<?php
session_start();
require_once '../../config/database.php';

function tableExists(PDO $conn, string $table): bool {
    try {
        $stmt = $conn->prepare("SHOW TABLES LIKE :t");
        $stmt->execute([':t' => $table]);
        return (bool)$stmt->fetchColumn();
    } catch (Throwable $e) {
        return false;
    }
}

function columnExists(PDO $conn, string $table, string $column): bool {
    try {
        $stmt = $conn->prepare("SHOW COLUMNS FROM `{$table}` LIKE :c");
        $stmt->execute([':c' => $column]);
        return (bool)$stmt->fetch();
    } catch (Throwable $e) {
        return false;
    }
}

function resolveExistingColumn(PDO $conn, string $table, array $candidates): ?string {
    foreach ($candidates as $c) {
        if (columnExists($conn, $table, $c)) { return $c; }
    }
    return null;
}

function findUser(PDO $conn, string $table, string $userCol, string $passCol, string $username): ?array {
    $sql = "SELECT * FROM `{$table}` WHERE `{$userCol}` = :u LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':u' => $username]);
    $row = $stmt->fetch();
    if ($row && isset($row[$passCol])) {
        $row['__table'] = $table;
        $row['__user_col'] = $userCol;
        $row['__pass_col'] = $passCol;
        return $row;
    }
    return null;
}

// Role definitions: tries multiple tables for seller/buyer to support both `seller` and `reviewseller` schemas
$roles = [
    'admin'  => [ ['table' => 'admin',  'userCandidates' => ['username','user','email','admin_username'],   'passCandidates' => ['password','pass','pwd','hash'], 'nameCandidates' => ['name','username','user']] ],
    'bat'    => [ ['table' => 'bat',    'userCandidates' => ['username','user','email'],                     'passCandidates' => ['password','pass','pwd','hash'], 'nameCandidates' => ['name','username','user']] ],
    'seller' => [
        ['table' => 'seller',      'userCandidates' => ['username','user','email'], 'passCandidates' => ['password','pass','pwd','user_password'], 'nameCandidates' => ['user_fname','firstname','first_name','username','user']],
        ['table' => 'reviewseller','userCandidates' => ['username'],                 'passCandidates' => ['password'],                                'nameCandidates' => ['user_fname','username']],
    ],
    'buyer'  => [
        ['table' => 'buyer',       'userCandidates' => ['username','user','email'], 'passCandidates' => ['password','pass','pwd','user_password'], 'nameCandidates' => ['user_fname','firstname','first_name','username','user']],
        ['table' => 'reviewbuyer', 'userCandidates' => ['username'],                 'passCandidates' => ['password'],                                'nameCandidates' => ['user_fname','username']],
    ],
];

$error = '';
if (isset($_POST['login'])) {
    $username = trim($_POST['username'] ?? '');
    $password = (string)($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $error = 'Please fill in all fields.';
    } else {
        $db = new Database();
        $conn = $db->getConnection();
        if (!$conn) {
            $error = 'Database connection failed.';
        } else {
            $foundUser = null;
            $foundRole = null;
            $wrongPassword = false;

            foreach ($roles as $role => $tables) {
                foreach ($tables as $cfg) {
                    if (!tableExists($conn, $cfg['table'])) { continue; }
                    $userCol = resolveExistingColumn($conn, $cfg['table'], $cfg['userCandidates']);
                    $passCol = resolveExistingColumn($conn, $cfg['table'], $cfg['passCandidates']);
                    if (!$userCol || !$passCol) { continue; }
                    $row = findUser($conn, $cfg['table'], $userCol, $passCol, $username);
                    if ($row) {
                        $hashed = (string)$row[$passCol];
                        if (password_verify($password, $hashed)) {
                            $foundUser = $row;
                            $foundRole = $role;
                            // Determine display name
                            $displayName = $row[$userCol] ?? $username;
                            foreach ($cfg['nameCandidates'] as $cand) {
                                if (isset($row[$cand]) && $row[$cand] !== '') { $displayName = $row[$cand]; break; }
                            }
                            $_SESSION['user_id'] = $row['user_id'] ?? ($row['id'] ?? ($row['uid'] ?? null));
                            $_SESSION['username'] = $row[$userCol] ?? $username;
                            $_SESSION['role'] = $role;
                            $_SESSION['name'] = $displayName;

                            // Redirect
                            if ($role === 'admin') { header('Location: ../adminview/dashboard.php'); exit; }
                            if ($role === 'bat')   { header('Location: ../batview/dashboard.php'); exit; }
                            if ($role === 'seller'){ header('Location: ../sellerview/dashboard.php'); exit; }
                            if ($role === 'buyer') { header('Location: ../buyerview/dasboard.php'); exit; }
                        } else {
                            // Username matched in this table, but password invalid
                            $wrongPassword = true;
                        }
                    }
                }
            }

            if (!$foundUser) {
                $error = $wrongPassword ? 'Invalid password.' : 'Account not found.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login to InnoVision</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>
    <div class="login-container">
        <form action="login.php" method="post">
            <h2>Login to InnoVision</h2>
            
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" name="login" value="login">Login</button>
            
            <?php if (!empty($error)): ?>
            <div class="register-link" style="color:#e53e3e; font-weight:600;">
                <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>
            <div class="register-link">
                <p>Don't have an account? <a href="userregister.php">Register here</a></p>
            </div>
        </form>
    </div>
</body>
</html>

<?php
// (legacy handler removed; logic handled above)
?>