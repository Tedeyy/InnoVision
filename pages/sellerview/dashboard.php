<?php
session_start();
if (empty($_SESSION['role']) || $_SESSION['role'] !== 'seller') {
    header('Location: ../authentication/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body{font-family:Inter,system-ui,-apple-system,Segoe UI,Roboto,Arial;background:#f7fafc;margin:0;padding:24px;color:#2d3748}
        .wrap{max-width:960px;margin:0 auto}
        .card{background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:24px;box-shadow:0 6px 20px rgba(0,0,0,.06)}
        h1{margin:0 0 12px}
        .meta{color:#4a5568;margin-bottom:16px}
        a.btn{display:inline-block;margin-top:12px;padding:10px 16px;border-radius:10px;background:#d69e2e;color:#fff;text-decoration:none}
        .top{display:flex;justify-content:space-between;align-items:center;margin-bottom:16px}
    </style>
    </head>
<body>
    <div class="wrap">
        <div class="top">
            <div>
                <h1>Seller Dashboard</h1>
                <div class="meta">Welcome, <?php echo htmlspecialchars($_SESSION['name'] ?? $_SESSION['username']); ?> (<?php echo htmlspecialchars($_SESSION['role']); ?>)</div>
            </div>
            <div>
                <a class="btn" href="../authentication/logout.php">Logout</a>
            </div>
        </div>
        <div class="card">
            <p>Manage your listings and view recent activity here.</p>
        </div>
    </div>
</body>
</html>


