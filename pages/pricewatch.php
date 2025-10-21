<?php
// /c:/xampp/htdocs/InnoVision/InnoVision/pages/pricewatch.php
// GitHub Copilot

session_start();

// === DB config - edit to match your environment ===
$dbHost = 'localhost';
$dbUser = 'root';
$dbPass = '';
$dbName = 'innovisionv1'; // change if your DB name differs

// connect
$mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
if ($mysqli->connect_errno) {
    http_response_code(500);
    echo "Database connect error: " . $mysqli->connect_error;
    exit;
}

// === fixed breed lists (used instead of relying solely on DB distinct values) ===
$fixedBreeds = [
    'cow' => [
        'Holstein Friesian','Jersey','Angus','Hereford','Brahman',
        'Simmental','Charolais','Limousin','Shorthorn','Santa Gertrudis','Other'
    ],
    'pig' => [
        'Berkshire','Duroc','Hampshire','Yorkshire','Landrace',
        'Pietrain','Tamworth','Large White','Chester White','Poland China','Other'
    ],
    'goat' => [
        'Boer','Nubian','Saanen','Alpine','Toggenburg',
        'Oberhasli','LaMancha','Nigerian Dwarf','Angora','Cashmere','Other'
    ]
];

// AJAX: return fixed breed list for an animal (JSON)
if (isset($_GET['fetch']) && $_GET['fetch'] === 'breeds' && isset($_GET['animal'])) {
    $animal = $_GET['animal'];
    header('Content-Type: application/json; charset=utf-8');
    if (!isset($fixedBreeds[$animal])) {
        echo json_encode([]);
        exit;
    }
    echo json_encode($fixedBreeds[$animal]);
    exit;
}

// handle selection form (save to session)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['animal_type']) && isset($_POST['breed'])) {
    $animal_type = $_POST['animal_type'];
    $breed = $_POST['breed'];
    // basic validation
    if (in_array($animal_type, ['cow', 'goat', 'pig']) && trim($breed) !== '') {
        $_SESSION['pw_animal'] = $animal_type;
        $_SESSION['pw_breed'] = $breed;
    }
    // redirect to avoid repost
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// clear selection
if (isset($_GET['clear']) && $_GET['clear'] == '1') {
    unset($_SESSION['pw_animal'], $_SESSION['pw_breed']);
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// helper to get table/col by animal
function animal_map($animal) {
    $map = [
        'cow' => ['table' => 'cowprice', 'col' => 'cow_breed'],
        'goat' => ['table' => 'goatprice', 'col' => 'goat_breed'],
        'pig' => ['table' => 'pigprice', 'col' => 'pig_breed'],
    ];
    return $map[$animal] ?? null;
}

$selected_animal = $_SESSION['pw_animal'] ?? null;
$selected_breed = $_SESSION['pw_breed'] ?? null;

// fetch list of all breeds/prices for selected animal
$allRows = [];
$selectedPrice = null;
if ($selected_animal) {
    $map = animal_map($selected_animal);
    if ($map) {
        $table = $map['table'];
        $col = $map['col'];
        $q = "SELECT `$col` AS breed, `marketprice` FROM `$table` ORDER BY `$col` ASC";
        if ($res = $mysqli->query($q)) {
            while ($r = $res->fetch_assoc()) {
                $allRows[] = $r;
                if ($selected_breed !== null && $r['breed'] === $selected_breed) {
                    $selectedPrice = $r['marketprice'];
                }
            }
            $res->free();
        }
    }
}

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Pricewatch Dashboard</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <style>
        body { font-family: Arial, sans-serif; margin:20px; }
        .topbar { display:flex; gap:10px; align-items:center; margin-bottom:20px; }
        .big { font-size:32px; font-weight:700; }
        .price-big { font-size:48px; font-weight:900; color:#1a73e8; }
        table { border-collapse:collapse; width:100%; max-width:900px; }
        th, td { border:1px solid #ddd; padding:8px; text-align:left; }
        th { background:#f4f4f4; }
        .btn { padding:8px 12px; cursor:pointer; border-radius:4px; border:1px solid #ccc; background:#fff; }
        .btn-primary { background:#1a73e8; color:#fff; border-color:#1761c6; }
        .btn-danger { background:#e53935; color:#fff; border-color:#c62828; }
        /* modal */
        .modal-backdrop { position:fixed; inset:0; background:rgba(0,0,0,0.5); display:flex; align-items:center; justify-content:center; z-index:9999; }
        .modal { background:#fff; padding:20px; border-radius:6px; width:320px; box-shadow:0 6px 30px rgba(0,0,0,0.2); }
        .modal h3 { margin-top:0; }
        .form-row { margin-bottom:10px; }
        select, button { width:100%; padding:8px; box-sizing:border-box; }
        .small { font-size:13px; color:#666; margin-top:8px; text-align:center; }
    </style>
</head>
<body>

<div class="topbar">
    <div class="big">Pricewatch Dashboard</div>
    <div style="flex:1"></div>
    <button class="btn" onclick="openSelection()">Change selection</button>
    <button class="btn" onclick="location.href='?clear=1'">Clear selection</button>
    <button class="btn btn-primary" onclick="window.print()">Print</button>
    <button class="btn" onclick="refreshPrices()">Refresh</button>
</div>

<?php if (!$selected_animal): ?>
    <p>No animal selected. Please choose an animal and breed.</p>
<?php else: ?>
    <div style="margin-bottom:18px;">
        <div>Selected animal: <strong><?php echo htmlspecialchars(ucfirst($selected_animal)); ?></strong></div>
        <div>Selected breed: <strong><?php echo htmlspecialchars($selected_breed); ?></strong></div>
        <div style="margin-top:8px;">Price per kg for selected breed:</div>
        <div class="price-big" id="selected-price"><?php echo $selectedPrice !== null ? number_format((float)$selectedPrice, 2) : 'N/A'; ?></div>
    </div>

    <h4>All <?php echo htmlspecialchars(ucfirst($selected_animal)); ?> breeds and prices (per kg)</h4>
    <table id="prices-table">
        <thead><tr><th>Breed</th><th>Price / kg</th></tr></thead>
        <tbody>
        <?php foreach ($allRows as $r): ?>
            <tr data-breed="<?php echo htmlspecialchars($r['breed']); ?>">
                <td><?php echo htmlspecialchars($r['breed']); ?></td>
                <td><?php echo number_format((float)$r['marketprice'], 2); ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<!-- Modal selection -->
<div id="selectionModal" class="modal-backdrop" style="display:<?php echo $selected_animal ? 'none' : 'flex'; ?>;">
    <div class="modal" role="dialog" aria-modal="true">
        <h3>Select livestock & breed</h3>
        <form id="selectionForm" method="post" action="">
            <div class="form-row">
                <select name="animal_type" id="animal_type" required>
                    <option value="">-- Select animal --</option>
                    <option value="cow">Cow</option>
                    <option value="goat">Goat</option>
                    <option value="pig">Pig</option>
                </select>
            </div>
            <div class="form-row">
                <select name="breed" id="breed" required>
                    <option value="">-- Select breed --</option>
                </select>
            </div>
            <div>
                <button type="submit" class="btn btn-primary">Enter dashboard</button>
            </div>
        </form>
        <div class="small">You can change selection later using "Change selection".</div>
    </div>
</div>

<script>
function openSelection() {
    document.getElementById('selectionModal').style.display = 'flex';
}

// fetch breeds when animal type changes
document.getElementById('animal_type').addEventListener('change', function(){
    var animal = this.value;
    var breedSelect = document.getElementById('breed');
    breedSelect.innerHTML = '<option>Loading...</option>';
    if (!animal) {
        breedSelect.innerHTML = '<option value="">-- Select breed --</option>';
        return;
    }
    fetch('?fetch=breeds&animal=' + encodeURIComponent(animal))
        .then(r => r.json())
        .then(data => {
            breedSelect.innerHTML = '';
            if (!data || data.length === 0) {
                breedSelect.innerHTML = '<option value="">(no breeds found)</option>';
                return;
            }
            breedSelect.innerHTML = '<option value="">-- Select breed --</option>';
            data.forEach(function(b){
                var opt = document.createElement('option');
                opt.value = b;
                opt.textContent = b;
                breedSelect.appendChild(opt);
            });
        })
        .catch(function(){
            breedSelect.innerHTML = '<option value="">(error)</option>';
        });
});

// if modal visible and user already had selection, prefill selects
<?php if ($selected_animal): ?>
    (function(){
        var an = "<?php echo htmlspecialchars($selected_animal, ENT_QUOTES); ?>";
        var br = "<?php echo htmlspecialchars($selected_breed ?? '', ENT_QUOTES); ?>";
        document.getElementById('animal_type').value = an;
        // trigger change to load breeds then set breed selection
        var ev = new Event('change');
        document.getElementById('animal_type').dispatchEvent(ev);
        // poll until breeds loaded then set
        var tries = 0;
        var intv = setInterval(function(){
            var sel = document.getElementById('breed');
            if (sel.options.length > 1 || tries > 20) {
                clearInterval(intv);
                for (var i=0;i<sel.options.length;i++){
                    if (sel.options[i].value === br){
                        sel.selectedIndex = i;
                        break;
                    }
                }
            }
            tries++;
        }, 150);
    })();
<?php endif; ?>

// Refresh prices (re-fetch table fragment and selected price)
function refreshPrices(){
    // simple reload to ensure fresh DB data
    location.reload();
}

// optional: click on table row to set as selected breed (updates big price visually)
document.getElementById('prices-table')?.addEventListener('click', function(e){
    var tr = e.target.closest('tr');
    if (!tr) return;
    var breed = tr.getAttribute('data-breed');
    if (!breed) return;
    // set hidden form and submit to change selection
    document.getElementById('animal_type').value = "<?php echo $selected_animal ?? ''; ?>";
    document.getElementById('breed').innerHTML = '<option value="'+breed+'">'+breed+'</option>';
    document.getElementById('selectionForm').submit();
});
</script>
</body>
</html>