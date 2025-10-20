<?php
session_start();
if (empty($_SESSION['role']) || $_SESSION['role'] !== 'buyer') {
    header('Location: ../authentication/login.php');
    exit;
}

require_once '../../config/database.php';

$buyer_id = $_SESSION['user_id'];
$buyer_fname = $_SESSION['user_fname'] ?? '';
$buyer_lname = $_SESSION['user_lname'] ?? '';

$db = new Database();
$conn = $db->getConnection();

$interests = [];
if ($conn) {
    try {
        $stmt = $conn->prepare("SELECT li.listing_id, l.seller_id, l.livestock_type, l.breed, l.age, l.weight, l.price, l.created, l.docs_path
                                 FROM listinginterest li
                                 JOIN livestocklisting l ON l.listing_id = li.listing_id
                                 WHERE li.buyer_id = ?
                                 ORDER BY li.created DESC");
        $stmt->execute([$buyer_id]);
        $interests = $stmt->fetchAll();
    } catch (Throwable $e) {
        $interests = [];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Interested Listings</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *{box-sizing:border-box}
        body{font-family:Inter,system-ui,-apple-system,Segoe UI,Roboto,Arial;background:#f7fafc;margin:0;color:#2d3748}
        .navbar{background:#fff;border-bottom:1px solid #e2e8f0;position:sticky;top:0;z-index:20}
        .nav-wrap{max-width:1200px;margin:0 auto;padding:12px 16px;display:flex;align-items:center;gap:12px}
        .brand{font-weight:800;color:#2d3748;text-decoration:none;margin-right:12px}
        .search{flex:1}
        .search input{width:100%;padding:10px 12px;border:1px solid #e2e8f0;border-radius:10px}
        .links{display:flex;gap:12px;margin-left:12px}
        .links a{padding:8px 12px;border-radius:10px;text-decoration:none;color:#4a5568}
        .links a.active{background:#3182ce;color:#fff}
        .user{margin-left:auto;display:flex;align-items:center;gap:8px}
        .logout{background:#e53e3e;color:#fff;padding:8px 12px;border-radius:10px;text-decoration:none}

        .container{max-width:800px;margin:0 auto;padding:16px}
        .feed{display:flex;flex-direction:column;gap:16px}
        .card{background:#fff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden}
        .card-header{display:flex;justify-content:space-between;align-items:center;padding:12px 16px}
        .badge{font-size:.75rem;padding:2px 8px;border-radius:9999px;background:#d1fae5;color:#065f46}
        .media{width:100%;max-height:360px;object-fit:cover;background:#f7fafc}
        .card-body{padding:12px 16px}
        .meta{color:#4a5568;font-size:.9rem}
    </style>
    </head>
<body>
    <nav class="navbar">
        <div class="nav-wrap">
            <a class="brand" href="dashboard.php">InnoVision</a>
            <div class="search"><input type="text" placeholder="Search livestock..." disabled></div>
            <div class="links">
                <a href="dashboard.php">Feed</a>
                <a class="active" href="interested.php">Interested</a>
                <a href="chat.php">Chat</a>
            </div>
            <div class="user">
                <div><?php echo htmlspecialchars($buyer_fname . ' ' . $buyer_lname); ?></div>
                <a class="logout" href="../authentication/logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <h2 style="margin:8px 0 16px 0;">Your Interested Listings</h2>
        <div class="feed">
            <?php foreach ($interests as $l): ?>
            <?php
                $img = $l['docs_path'] ?? '';
                if ($img && !preg_match('/^https?:\/\//', $img)) {
                    if (str_starts_with($img, 'pages/')) { $img = '../' . substr($img, 6); } else { $img = '../' . ltrim($img, '/'); }
                }
            ?>
            <div class="card">
                <div class="card-header">
                    <div>
                        <div style="font-weight:700;">#<?php echo htmlspecialchars($l['listing_id']); ?> • <?php echo htmlspecialchars($l['livestock_type']); ?> — <?php echo htmlspecialchars($l['breed']); ?></div>
                        <div class="meta">Age: <?php echo htmlspecialchars($l['age']); ?> • Weight: <?php echo htmlspecialchars($l['weight']); ?>kg</div>
                    </div>
                    <span class="badge">Verified</span>
                </div>
                <?php if (!empty($img)): ?>
                    <img class="media" src="<?php echo htmlspecialchars($img); ?>" alt="Listing image">
                <?php endif; ?>
                <div class="card-body">
                    <div style="font-size:1.25rem;font-weight:800;">₱<?php echo number_format($l['price'], 2); ?></div>
                    <div class="meta">Posted on <?php echo date('M j, Y', strtotime($l['created'])); ?></div>
                </div>
            </div>
            <?php endforeach; ?>

            <?php if (empty($interests)): ?>
                <div class="card" style="padding:16px;text-align:center;color:#6b7280;">No interests yet. Mark listings as Interested from your feed.</div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

