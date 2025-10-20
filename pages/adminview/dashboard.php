<?php
session_start();
if (empty($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../authentication/login.php');
    exit;
}

require_once '../../config/database.php';

$db = new Database();
$conn = $db->getConnection();

$admin_id = $_SESSION['user_id'] ?? null;

$flash_success = '';
$flash_error = '';

// Handle Approve/Deny Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    try {
        if ($action === 'approve_account') {
            $review_table = $_POST['review_table'] ?? '';
            $final_table = $_POST['final_table'] ?? '';
            $user_id = (int)($_POST['user_id'] ?? 0);

            if (!$conn) { throw new Exception('No DB connection'); }
            if (!$review_table || !$final_table || !$user_id) { throw new Exception('Invalid data'); }

            $conn->beginTransaction();

            // Fetch review row (bat uses bat_id)
            if ($review_table === 'reviewbat') {
                $stmt = $conn->prepare("SELECT * FROM reviewbat WHERE bat_id = ? FOR UPDATE");
            } else {
                $stmt = $conn->prepare("SELECT * FROM {$review_table} WHERE user_id = ? FOR UPDATE");
            }
            $stmt->execute([$user_id]);
            $row = $stmt->fetch();
            if (!$row) { throw new Exception('Record not found'); }

            // Build insert by whitelisting columns
            $newUserId = null;
            if ($final_table === 'seller') {
                // Map to seller table (no docs_path in final schema, includes admin_id)
                $insert = $conn->prepare("INSERT INTO seller (user_fname, user_mname, user_lname, bdate, contact, email, rsbsanum, idnum, username, password, admin_id) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
                $insert->execute([
                    $row['user_fname'], $row['user_mname'], $row['user_lname'], $row['bdate'], $row['contact'], $row['email'], $row['rsbsanum'], $row['idnum'], $row['username'], $row['password'], $admin_id
                ]);
                $newUserId = (int)$conn->lastInsertId();
            } else if ($final_table === 'buyer') {
                $insert = $conn->prepare("INSERT INTO buyer (user_fname, user_mname, user_lname, bdate, contact, email, supdoctype, supdocnum, username, password) VALUES (?,?,?,?,?,?,?,?,?,?)");
                $insert->execute([
                    $row['user_fname'], $row['user_mname'], $row['user_lname'], $row['bdate'], $row['contact'], $row['email'], $row['supdoctype'], $row['supdocnum'], $row['username'], $row['password']
                ]);
                $newUserId = (int)$conn->lastInsertId();
            } else if ($final_table === 'bat') {
                // Assuming final bat table: columns may include assigned_barangay, supdoctype
                $insert = $conn->prepare("INSERT INTO bat (user_fname, user_mname, user_lname, bdate, contact, email, assigned_barangay, supdoctype, username, password) VALUES (?,?,?,?,?,?,?,?,?,?)");
                $insert->execute([
                    $row['user_fname'], $row['user_mname'], $row['user_lname'], $row['bdate'], $row['contact'], $row['email'], $row['assigned_barangay'], $row['supdoctype'], $row['username'], $row['password']
                ]);
                $newUserId = (int)$conn->lastInsertId();
            } else if ($final_table === 'admin') {
                $insert = $conn->prepare("INSERT INTO admin (user_fname, user_mname, user_lname, bdate, contact, email, office, role, supdoctype, username, password) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
                $insert->execute([
                    $row['user_fname'], $row['user_mname'], $row['user_lname'], $row['bdate'], $row['contact'], $row['email'], $row['office'], $row['role'], $row['supdoctype'], $row['username'], $row['password']
                ]);
                $newUserId = (int)$conn->lastInsertId();
            } else {
                throw new Exception('Unsupported final table');
            }

            // Delete from review table (handle bat key)
            if ($review_table === 'reviewbat') {
                $del = $conn->prepare("DELETE FROM reviewbat WHERE bat_id = ?");
            } else {
                $del = $conn->prepare("DELETE FROM {$review_table} WHERE user_id = ?");
            }
            $del->execute([$user_id]);

            // Log to login_purpose
            if ($newUserId) {
                // Best-effort IP detection
                $ip = '';
                if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
                    $ip = $_SERVER['HTTP_CLIENT_IP'];
                } else if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                    // may contain a list, take first
                    $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
                } else if (!empty($_SERVER['REMOTE_ADDR'])) {
                    $ip = $_SERVER['REMOTE_ADDR'];
                }

                $purpose = 'User Approval';
                $log = $conn->prepare("INSERT INTO login_purpose (user_id, ip_address, purpose) VALUES (?,?,?)");
                $log->execute([$newUserId, $ip, $purpose]);
            }

            $conn->commit();
            $flash_success = 'Account approved and moved successfully.';
        }

        if ($action === 'deny_account') {
            $review_table = $_POST['review_table'] ?? '';
            $user_id = (int)($_POST['user_id'] ?? 0);
            if (!$conn) { throw new Exception('No DB connection'); }
            if (!$review_table || !$user_id) { throw new Exception('Invalid data'); }

            if ($review_table === 'reviewbat') {
                $stmt = $conn->prepare("DELETE FROM reviewbat WHERE bat_id = ?");
                $stmt->execute([$user_id]);
            } else {
                $stmt = $conn->prepare("DELETE FROM {$review_table} WHERE user_id = ?");
                $stmt->execute([$user_id]);
            }
            $flash_success = 'Account denied and removed from review.';
        }

        if ($action === 'approve_listing') {
            $listing_id = (int)($_POST['listing_id'] ?? 0);
            if (!$conn || !$listing_id) { throw new Exception('Invalid data'); }

            $conn->beginTransaction();
            $stmt = $conn->prepare("SELECT * FROM reviewlivestocklisting WHERE listing_id = ? FOR UPDATE");
            $stmt->execute([$listing_id]);
            $row = $stmt->fetch();
            if (!$row) { throw new Exception('Listing not found'); }

            // Insert into livestocklisting (marketprice optional, set NULL; admin_id set, bat_id NULL)
            $insert = $conn->prepare("INSERT INTO livestocklisting (seller_id, livestock_type, breed, age, weight, marketprice, price, admin_id) VALUES (?,?,?,?,?,?,?,?)");
            $insert->execute([
                $row['seller_id'], $row['livestock_type'], $row['breed'], $row['age'], $row['weight'], null, $row['price'], $admin_id
            ]);

            $del = $conn->prepare("DELETE FROM reviewlivestocklisting WHERE listing_id = ?");
            $del->execute([$listing_id]);
            $conn->commit();
            $flash_success = 'Listing approved and published.';
        }

        if ($action === 'deny_listing') {
            $listing_id = (int)($_POST['listing_id'] ?? 0);
            if (!$conn || !$listing_id) { throw new Exception('Invalid data'); }
            $stmt = $conn->prepare("DELETE FROM reviewlivestocklisting WHERE listing_id = ?");
            $stmt->execute([$listing_id]);
            $flash_success = 'Listing denied and removed.';
        }
    } catch (Throwable $e) {
        if ($conn && $conn->inTransaction()) { $conn->rollBack(); }
        $flash_error = 'Action failed: ' . $e->getMessage();
    }
}

// Fetch queues
$pending_accounts = [
    'reviewbuyer' => [],
    'reviewseller' => [],
    'reviewbat' => [],
    'reviewadmin' => []
];

$review_tables = array_keys($pending_accounts);
if ($conn) {
    foreach ($review_tables as $tbl) {
        try {
            // Handle reviewbat which uses bat_id instead of user_id
            if ($tbl === 'reviewbat') {
                $stmt = $conn->query("SELECT bat_id AS id, user_fname, user_mname, user_lname, email, username, created FROM reviewbat ORDER BY created DESC");
            } else {
                $stmt = $conn->query("SELECT user_id AS id, user_fname, user_mname, user_lname, email, username, created FROM {$tbl} ORDER BY created DESC");
            }
            $pending_accounts[$tbl] = $stmt->fetchAll();
        } catch (Throwable $e) {
            $pending_accounts[$tbl] = [];
        }
    }

    // Listings
    try {
        $stmt = $conn->query("SELECT listing_id, seller_id, livestock_type, breed, age, weight, price, created FROM reviewlivestocklisting ORDER BY created DESC");
        $pending_listings = $stmt->fetchAll();
    } catch (Throwable $e) {
        $pending_listings = [];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body{font-family:Inter,system-ui,-apple-system,Segoe UI,Roboto,Arial;background:#f7fafc;margin:0;padding:24px;color:#2d3748}
        .wrap{max-width:1200px;margin:0 auto}
        .top{display:flex;justify-content:space-between;align-items:center;margin-bottom:16px}
        h1{margin:0 0 12px}
        .meta{color:#4a5568;margin-bottom:16px}
        a.btn{display:inline-block;margin-top:12px;padding:10px 16px;border-radius:10px;background:#3182ce;color:#fff;text-decoration:none}
        .tabs{display:flex;gap:12px;margin-bottom:16px}
        .tab-btn{padding:8px 12px;border:1px solid #e2e8f0;border-radius:10px;background:#fff;cursor:pointer}
        .tab-btn.active{background:#3182ce;color:#fff;border-color:#3182ce}
        .card{background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:16px;box-shadow:0 6px 20px rgba(0,0,0,.06)}
        table{width:100%;border-collapse:collapse}
        th,td{padding:10px;border-bottom:1px solid #e2e8f0;text-align:left}
        th{background:#f7fafc;color:#4a5568}
        .actions{display:flex;gap:8px}
        .btn-secondary{background:#4a5568;color:#fff;text-decoration:none;padding:8px 12px;border-radius:8px}
        .btn-approve{background:#16a34a;color:#fff;text-decoration:none;padding:8px 12px;border-radius:8px;border:none;cursor:pointer}
        .btn-deny{background:#dc2626;color:#fff;text-decoration:none;padding:8px 12px;border-radius:8px;border:none;cursor:pointer}
        .btn-show{background:#d69e2e;color:#fff;text-decoration:none;padding:8px 12px;border-radius:8px;border:none;cursor:pointer}
        .alert{padding:12px;border-radius:10px;margin-bottom:12px}
        .alert-success{background:#d1fae5;color:#065f46;border:1px solid #a7f3d0}
        .alert-error{background:#fee2e2;color:#991b1b;border:1px solid #fca5a5}
        /* Modal */
        .modal{position:fixed;inset:0;display:none;align-items:center;justify-content:center;background:rgba(0,0,0,.4);padding:16px}
        .modal.open{display:flex}
        .modal-card{background:#fff;border-radius:12px;max-width:720px;width:100%;padding:16px;max-height:85vh;display:flex;flex-direction:column}
        .modal-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:12px}
        .close{background:#e2e8f0;border:none;border-radius:8px;padding:6px 10px;cursor:pointer}
        #modal-body{overflow:auto;padding-right:4px}
        .grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
        .list{display:block}
        .list .field{margin-bottom:8px}
        .field{background:#f7fafc;border:1px solid #e2e8f0;border-radius:8px;padding:8px}
        .field label{display:block;font-size:12px;color:#6b7280}
        .field div{font-weight:600;color:#2d3748}
    </style>
    </head>
<body>
    <div class="wrap">
        <div class="top">
            <div>
                <h1>Admin Dashboard</h1>
                <div class="meta">Welcome, <?php echo htmlspecialchars($_SESSION['name'] ?? $_SESSION['username']); ?> (<?php echo htmlspecialchars($_SESSION['role']); ?>)</div>
            </div>
            <div>
                <a class="btn" href="../authentication/logout.php">Logout</a>
            </div>
        </div>

        <?php if ($flash_success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($flash_success); ?></div>
        <?php endif; ?>
        <?php if ($flash_error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($flash_error); ?></div>
        <?php endif; ?>

        <div class="tabs">
            <button class="tab-btn active" data-tab="accounts">Pending Accounts</button>
            <button class="tab-btn" data-tab="listings">Listing Reviews</button>
        </div>

        <div id="tab-accounts" class="card">
            <h3>Accounts Awaiting Review</h3>
            <?php foreach ($pending_accounts as $tbl => $rows): ?>
                <h4 style="margin-top:16px;margin-bottom:8px; text-transform:capitalize;">
                    <?php echo htmlspecialchars($tbl); ?> (<?php echo count($rows); ?>)
                </h4>
                <?php if (empty($rows)): ?>
                    <div style="color:#6b7280;font-style:italic;margin-bottom:8px;">No records.</div>
                <?php else: ?>
                    <div style="overflow:auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Username</th>
                                <th>Created</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rows as $r): ?>
                            <tr>
                                <td>#<?php echo htmlspecialchars($r['id']); ?></td>
                                <td><?php echo htmlspecialchars($r['user_fname'] . ' ' . $r['user_lname']); ?></td>
                                <td><?php echo htmlspecialchars($r['email']); ?></td>
                                <td><?php echo htmlspecialchars($r['username']); ?></td>
                                <td><?php echo htmlspecialchars($r['created']); ?></td>
                                <td class="actions">
                                    <button class="btn-show" data-kind="account" data-table="<?php echo htmlspecialchars($tbl); ?>" data-id="<?php echo htmlspecialchars($r['id']); ?>">Show</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>

        <div id="tab-listings" class="card" style="display:none;">
            <h3>Livestock Listings Awaiting Review</h3>
            <?php if (empty($pending_listings)): ?>
                <div style="color:#6b7280;font-style:italic;">No listings pending review.</div>
            <?php else: ?>
            <div style="overflow:auto;">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Seller</th>
                        <th>Type</th>
                        <th>Breed</th>
                        <th>Age</th>
                        <th>Weight</th>
                        <th>Price</th>
                        <th>Created</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pending_listings as $l): ?>
                    <tr>
                        <td>#<?php echo htmlspecialchars($l['listing_id']); ?></td>
                        <td>#<?php echo htmlspecialchars($l['seller_id']); ?></td>
                        <td><?php echo htmlspecialchars($l['livestock_type']); ?></td>
                        <td><?php echo htmlspecialchars($l['breed']); ?></td>
                        <td><?php echo htmlspecialchars($l['age']); ?></td>
                        <td><?php echo htmlspecialchars($l['weight']); ?> kg</td>
                        <td>₱<?php echo number_format($l['price'], 2); ?></td>
                        <td><?php echo htmlspecialchars($l['created']); ?></td>
                        <td class="actions">
                            <button class="btn-show" data-kind="listing" data-id="<?php echo htmlspecialchars($l['listing_id']); ?>">Show</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal -->
    <div id="modal" class="modal" aria-hidden="true">
        <div class="modal-card">
            <div class="modal-header">
                <h3 id="modal-title">Details</h3>
                <button class="close" onclick="closeModal()">Close</button>
            </div>
            <div id="modal-body"></div>
            <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:12px;">
                <form id="deny-form" method="POST">
                    <input type="hidden" name="action" value="">
                    <input type="hidden" name="review_table" value="">
                    <input type="hidden" name="user_id" value="">
                    <input type="hidden" name="listing_id" value="">
                    <button type="submit" class="btn-deny">Deny</button>
                </form>
                <form id="approve-form" method="POST">
                    <input type="hidden" name="action" value="">
                    <input type="hidden" name="review_table" value="">
                    <input type="hidden" name="final_table" value="">
                    <input type="hidden" name="user_id" value="">
                    <input type="hidden" name="listing_id" value="">
                    <button type="submit" class="btn-approve">Approve</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Tabs
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                const tab = btn.getAttribute('data-tab');
                document.getElementById('tab-accounts').style.display = tab === 'accounts' ? 'block' : 'none';
                document.getElementById('tab-listings').style.display = tab === 'listings' ? 'block' : 'none';
            });
        });

        // Modal helpers
        const modal = document.getElementById('modal');
        const modalBody = document.getElementById('modal-body');
        const modalTitle = document.getElementById('modal-title');

        function openModal() { modal.classList.add('open'); modal.setAttribute('aria-hidden', 'false'); }
        function closeModal() { modal.classList.remove('open'); modal.setAttribute('aria-hidden', 'true'); }

        // Show buttons
        document.querySelectorAll('.btn-show').forEach(btn => {
            btn.addEventListener('click', async () => {
                const kind = btn.getAttribute('data-kind');
                if (kind === 'account') {
                    const table = btn.getAttribute('data-table');
                    const id = btn.getAttribute('data-id');
                    await showAccountDetail(table, id);
                } else if (kind === 'listing') {
                    const id = btn.getAttribute('data-id');
                    await showListingDetail(id);
                }
            });
        });

        async function fetchJSON(url) {
            const res = await fetch(url, { headers: { 'Accept': 'application/json' }});
            if (!res.ok) throw new Error('Network error');
            return await res.json();
        }

        async function showAccountDetail(table, id) {
            modalTitle.textContent = 'Account Details';
            try {
                const data = await fetchJSON(`view_account.php?table=${encodeURIComponent(table)}&id=${encodeURIComponent(id)}`);
                renderAccountModal(data, table);
            } catch (e) {
                modalBody.innerHTML = '<div style="color:#991b1b;">Failed to load details.</div>';
            }
            setupAccountForms(table, id);
            openModal();
        }

        async function showListingDetail(id) {
            modalTitle.textContent = 'Listing Details';
            try {
                const data = await fetchJSON(`view_listing.php?id=${encodeURIComponent(id)}`);
                renderListingModal(data);
            } catch (e) {
                modalBody.innerHTML = '<div style="color:#991b1b;">Failed to load details.</div>';
            }
            setupListingForms(id);
            openModal();
        }

        function renderAccountModal(data, table) {
            // Build vertical list of fields and hide sensitive keys
            const hiddenKeys = new Set(['password', 'docs_path']);
            const entries = Object.entries(data || {}).filter(([k]) => !hiddenKeys.has(k));
            const rows = entries.map(([k, v]) => {
                const label = k.replaceAll('_', ' ').replace(/\b\w/g, c => c.toUpperCase());
                return `<div class="field"><label>${escapeHtml(label)}</label><div>${escapeHtml(String(v ?? ''))}</div></div>`;
            }).join('');

            // Build docs preview using table-specific base folder (e.g., ../authentication/bat/ + docs_path)
            const resolveDocUrl = (relativePath, tableName) => {
                if (!relativePath) return '';
                if (relativePath.startsWith('http://') || relativePath.startsWith('https://') || relativePath.startsWith('/')) return relativePath;
                let base = '../authentication/';
                if (tableName === 'reviewseller') base += 'seller/';
                else if (tableName === 'reviewbuyer') base += 'buyer/';
                else if (tableName === 'reviewadmin') base += 'admin/';
                else if (tableName === 'reviewbat') base += 'bat/';
                return base + relativePath.replace(/^\/+/, '');
            };

            // Build docs preview if available
            let docsPreview = '';
            const docPath = data.docs_path || '';
            if (docPath) {
                const url = resolveDocUrl(docPath, table);
                const lower = docPath.toLowerCase();
                if (lower.endsWith('.pdf')) {
                    docsPreview = `<div class="field"><label>Document</label><div><a href="${escapeHtml(url)}" target="_blank" class="btn-secondary">Open PDF</a></div></div>`;
                } else {
                    docsPreview = `<div class="field"><label>Document</label><div><img src="${escapeHtml(url)}" alt="Document" style="max-width:100%;border-radius:8px;border:1px solid #e2e8f0"/></div></div>`;
                }
            }

            modalBody.innerHTML = `
                <div class="list">${rows}${docsPreview}</div>
            `;

            const approveForm = document.getElementById('approve-form');
            approveForm.querySelector('input[name="action"]').value = 'approve_account';
            approveForm.querySelector('input[name="review_table"]').value = table;
            // Determine primary id field
            const primaryId = table === 'reviewbat' ? data.bat_id : data.user_id;
            approveForm.querySelector('input[name="user_id"]').value = primaryId;
            // Map final table
            let finalTable = '';
            if (table === 'reviewseller') finalTable = 'seller';
            if (table === 'reviewbuyer') finalTable = 'buyer';
            if (table === 'reviewbat') finalTable = 'bat';
            if (table === 'reviewadmin') finalTable = 'admin';
            approveForm.querySelector('input[name="final_table"]').value = finalTable;

            const denyForm = document.getElementById('deny-form');
            denyForm.querySelector('input[name="action"]').value = 'deny_account';
            denyForm.querySelector('input[name="review_table"]').value = table;
            denyForm.querySelector('input[name="user_id"]').value = primaryId;
        }

        function renderListingModal(data) {
            modalBody.innerHTML = `
                <div class="grid">
                    <div class="field"><label>Listing ID</label><div>#${escapeHtml(String(data.listing_id || ''))}</div></div>
                    <div class="field"><label>Seller ID</label><div>#${escapeHtml(String(data.seller_id || ''))}</div></div>
                    <div class="field"><label>Type</label><div>${escapeHtml(data.livestock_type || '')}</div></div>
                    <div class="field"><label>Breed</label><div>${escapeHtml(data.breed || '')}</div></div>
                    <div class="field"><label>Age</label><div>${escapeHtml(String(data.age || ''))}</div></div>
                    <div class="field"><label>Weight</label><div>${escapeHtml(String(data.weight || ''))} kg</div></div>
                    <div class="field"><label>Price</label><div>₱${escapeHtml(Number(data.price || 0).toFixed(2))}</div></div>
                    <div class="field"><label>Created</label><div>${escapeHtml(data.created || '')}</div></div>
                </div>
            `;

            const approveForm = document.getElementById('approve-form');
            approveForm.querySelector('input[name="action"]').value = 'approve_listing';
            approveForm.querySelector('input[name="listing_id"]').value = data.listing_id;

            const denyForm = document.getElementById('deny-form');
            denyForm.querySelector('input[name="action"]').value = 'deny_listing';
            denyForm.querySelector('input[name="listing_id"]').value = data.listing_id;
        }

        function setupAccountForms(table, id) {
            // Populated in renderAccountModal
        }

        function setupListingForms(id) {
            // Populated in renderListingModal
        }

        function escapeHtml(str){
            if (str === null || str === undefined) return '';
            return String(str)
                .replaceAll('&','&amp;')
                .replaceAll('<','&lt;')
                .replaceAll('>','&gt;')
                .replaceAll('"','&quot;')
                .replaceAll("'",'&#039;');
        }
    </script>
</body>
</html>
