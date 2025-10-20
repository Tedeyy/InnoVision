<?php
session_start();
if (empty($_SESSION['role']) || $_SESSION['role'] !== 'buyer') {
    header('Location: ../authentication/login.php');
    exit;
}

$buyer_fname = $_SESSION['user_fname'] ?? '';
$buyer_lname = $_SESSION['user_lname'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buyer Chat</title>
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

        .container{max-width:1000px;margin:0 auto;padding:16px}
        .chat{display:grid;grid-template-columns:320px 1fr;gap:12px;height:70vh}
        .panel{background:#fff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;display:flex;flex-direction:column}
        .panel header{background:#f7fafc;border-bottom:1px solid #e2e8f0;padding:12px 16px;font-weight:700}
        .list{flex:1;overflow:auto}
        .item{padding:12px 16px;border-bottom:1px solid #e2e8f0;cursor:pointer}
        .item:hover{background:#f7fafc}
        .conversation{display:flex;flex-direction:column;height:100%}
        .messages{flex:1;overflow:auto;padding:16px}
        .msg{display:flex;gap:8px;margin-bottom:12px}
        .msg .bubble{padding:10px 12px;border-radius:12px;border:1px solid #e2e8f0;max-width:60%}
        .me{justify-content:flex-end}
        .me .bubble{background:#3182ce;color:#fff;border-color:#3182ce}
        .input{border-top:1px solid #e2e8f0;padding:12px;display:flex;gap:8px}
        .input textarea{flex:1;padding:10px;border:1px solid #e2e8f0;border-radius:10px;resize:none}
        .send{background:#d69e2e;color:#fff;border:none;border-radius:10px;padding:8px 12px}
    </style>
    </head>
<body>
    <nav class="navbar">
        <div class="nav-wrap">
            <a class="brand" href="dashboard.php">InnoVision</a>
            <div class="search"><input type="text" placeholder="Search sellers..." disabled></div>
            <div class="links">
                <a href="dashboard.php">Feed</a>
                <a href="interested.php">Interested</a>
                <a class="active" href="chat.php">Chat</a>
            </div>
            <div class="user">
                <div><?php echo htmlspecialchars($buyer_fname . ' ' . $buyer_lname); ?></div>
                <a class="logout" href="../authentication/logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="chat">
            <div class="panel">
                <header>Conversations</header>
                <div class="list">
                    <div class="item">Seller #100045 • Angus</div>
                    <div class="item">Seller #100102 • Boer Goat</div>
                    <div class="item">Seller #100220 • Pekin Duck</div>
                </div>
            </div>
            <div class="panel conversation">
                <header>Chat</header>
                <div class="messages">
                    <div class="msg"><div class="bubble">Hello, is this still available?</div></div>
                    <div class="msg me"><div class="bubble">Yes! Would you like to schedule a visit?</div></div>
                </div>
                <div class="input">
                    <textarea rows="2" placeholder="Type a message..." disabled></textarea>
                    <button class="send" disabled>Send</button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

