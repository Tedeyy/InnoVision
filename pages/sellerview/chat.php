<?php
session_start();
if (empty($_SESSION['role']) || $_SESSION['role'] !== 'seller') {
    header('Location: ../authentication/login.php');
    exit;
}

// Include database configuration
require_once '../../config/database.php';

// Get user information
$user_id = $_SESSION['user_id'];
$user_fname = $_SESSION['user_fname'] ?? '';
$user_lname = $_SESSION['user_lname'] ?? '';

// Check verification status
$verification_status = 'Pending';
$db = new Database();
$conn = $db->getConnection();

if ($conn) {
    try {
        // Check if user is in seller table (verified)
        $stmt = $conn->prepare("SELECT user_id FROM seller WHERE user_id = ?");
        $stmt->execute([$user_id]);
        if ($stmt->fetch()) {
            $verification_status = 'Verified';
        } else {
            // Check if user is in reviewseller table (pending)
            $stmt = $conn->prepare("SELECT user_id FROM reviewseller WHERE user_id = ?");
            $stmt->execute([$user_id]);
            if ($stmt->fetch()) {
                $verification_status = 'Pending';
            }
        }
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
    }
}

// Get recent conversations (placeholder - would need a proper chat system)
$conversations = [
    [
        'id' => 1,
        'buyer_name' => 'John Doe',
        'last_message' => 'Is this cow still available?',
        'timestamp' => '2 hours ago',
        'unread' => 2
    ],
    [
        'id' => 2,
        'buyer_name' => 'Jane Smith',
        'last_message' => 'What is the weight of the pig?',
        'timestamp' => '1 day ago',
        'unread' => 0
    ],
    [
        'id' => 3,
        'buyer_name' => 'Mike Johnson',
        'last_message' => 'Can I visit your farm?',
        'timestamp' => '3 days ago',
        'unread' => 1
    ]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat - Seller Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, Arial, sans-serif;
            background: #f7fafc;
            color: #2d3748;
            line-height: 1.6;
        }
        
        .navbar {
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            padding: 1rem 0;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .nav-brand {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2d3748;
            text-decoration: none;
        }
        
        .nav-links {
            display: flex;
            gap: 2rem;
            list-style: none;
        }
        
        .nav-links a {
            color: #4a5568;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }
        
        .nav-links a:hover,
        .nav-links a.active {
            color: #d69e2e;
        }
        
        .nav-user {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .user-info {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
        }
        
        .user-name {
            font-weight: 600;
            color: #2d3748;
        }
        
        .verification-status {
            font-size: 0.875rem;
            padding: 0.25rem 0.5rem;
            border-radius: 0.375rem;
            font-weight: 500;
        }
        
        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }
        
        .status-verified {
            background: #d1fae5;
            color: #065f46;
        }
        
        .logout-btn {
            background: #e53e3e;
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 0.375rem;
            text-decoration: none;
            font-weight: 500;
            transition: background 0.2s;
        }
        
        .logout-btn:hover {
            background: #c53030;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .page-header {
            margin-bottom: 2rem;
        }
        
        .page-title {
            font-size: 2rem;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 0.5rem;
        }
        
        .page-subtitle {
            color: #4a5568;
            font-size: 1.125rem;
        }
        
        .chat-container {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 1rem;
            height: 600px;
        }
        
        .conversations-sidebar {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            overflow: hidden;
        }
        
        .conversations-header {
            background: #f7fafc;
            padding: 1rem;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .conversations-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #2d3748;
        }
        
        .conversation-list {
            height: calc(100% - 80px);
            overflow-y: auto;
        }
        
        .conversation-item {
            padding: 1rem;
            border-bottom: 1px solid #e2e8f0;
            cursor: pointer;
            transition: background 0.2s;
            position: relative;
        }
        
        .conversation-item:hover {
            background: #f7fafc;
        }
        
        .conversation-item.active {
            background: #fef3c7;
        }
        
        .conversation-name {
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 0.25rem;
        }
        
        .conversation-preview {
            color: #6b7280;
            font-size: 0.875rem;
            margin-bottom: 0.25rem;
        }
        
        .conversation-time {
            color: #9ca3af;
            font-size: 0.75rem;
        }
        
        .unread-badge {
            position: absolute;
            top: 0.75rem;
            right: 0.75rem;
            background: #e53e3e;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .chat-main {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        
        .chat-header {
            background: #f7fafc;
            padding: 1rem;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .chat-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #d69e2e;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }
        
        .chat-user-info h3 {
            font-size: 1.125rem;
            font-weight: 600;
            color: #2d3748;
        }
        
        .chat-user-info p {
            color: #6b7280;
            font-size: 0.875rem;
        }
        
        .chat-messages {
            flex: 1;
            padding: 1rem;
            overflow-y: auto;
            background: #fafafa;
        }
        
        .message {
            margin-bottom: 1rem;
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
        }
        
        .message.sent {
            flex-direction: row-reverse;
        }
        
        .message-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #d69e2e;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 0.875rem;
            font-weight: 600;
            flex-shrink: 0;
        }
        
        .message.sent .message-avatar {
            background: #6b7280;
        }
        
        .message-content {
            max-width: 70%;
            background: #fff;
            padding: 0.75rem 1rem;
            border-radius: 1rem;
            border: 1px solid #e2e8f0;
        }
        
        .message.sent .message-content {
            background: #d69e2e;
            color: white;
            border-color: #d69e2e;
        }
        
        .message-text {
            margin-bottom: 0.25rem;
        }
        
        .message-time {
            font-size: 0.75rem;
            color: #9ca3af;
        }
        
        .message.sent .message-time {
            color: rgba(255, 255, 255, 0.7);
        }
        
        .chat-input {
            padding: 1rem;
            border-top: 1px solid #e2e8f0;
            background: #fff;
        }
        
        .input-group {
            display: flex;
            gap: 0.5rem;
        }
        
        .message-input {
            flex: 1;
            padding: 0.75rem;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            font-size: 1rem;
            resize: none;
        }
        
        .message-input:focus {
            outline: none;
            border-color: #d69e2e;
            box-shadow: 0 0 0 3px rgba(214, 158, 46, 0.1);
        }
        
        .send-btn {
            background: #d69e2e;
            color: white;
            border: none;
            border-radius: 0.5rem;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }
        
        .send-btn:hover {
            background: #b7791f;
        }
        
        .no-conversation {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #6b7280;
            font-style: italic;
        }
        
        @media (max-width: 768px) {
            .nav-container {
                flex-direction: column;
                gap: 1rem;
            }
            
            .nav-links {
                gap: 1rem;
            }
            
            .nav-user {
                flex-direction: column;
                align-items: center;
            }
            
            .container {
                padding: 1rem;
            }
            
            .chat-container {
                grid-template-columns: 1fr;
                height: auto;
            }
            
            .conversations-sidebar {
                height: 300px;
            }
            
            .chat-main {
                height: 400px;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <a href="dashboard.php" class="nav-brand">InnoVision</a>
            <ul class="nav-links">
                <li><a href="listings.php">Listings</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="chat.php" class="active">Chat</a></li>
            </ul>
            <div class="nav-user">
                <div class="user-info">
                    <div class="user-name"><?php echo htmlspecialchars($user_fname . ' ' . $user_lname); ?></div>
                    <div class="verification-status <?php echo $verification_status === 'Verified' ? 'status-verified' : 'status-pending'; ?>">
                        <?php echo $verification_status; ?>
                    </div>
                </div>
                <a href="../authentication/logout.php" class="logout-btn">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h1 class="page-title">Messages</h1>
            <p class="page-subtitle">Communicate with potential buyers about your listings</p>
        </div>

        <div class="chat-container">
            <div class="conversations-sidebar">
                <div class="conversations-header">
                    <h3 class="conversations-title">Conversations</h3>
                </div>
                <div class="conversation-list">
                    <?php foreach ($conversations as $conversation): ?>
                    <div class="conversation-item" onclick="selectConversation(<?php echo $conversation['id']; ?>)">
                        <div class="conversation-name"><?php echo htmlspecialchars($conversation['buyer_name']); ?></div>
                        <div class="conversation-preview"><?php echo htmlspecialchars($conversation['last_message']); ?></div>
                        <div class="conversation-time"><?php echo htmlspecialchars($conversation['timestamp']); ?></div>
                        <?php if ($conversation['unread'] > 0): ?>
                        <div class="unread-badge"><?php echo $conversation['unread']; ?></div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="chat-main">
                <div class="chat-header">
                    <div class="chat-avatar">JD</div>
                    <div class="chat-user-info">
                        <h3>John Doe</h3>
                        <p>Online</p>
                    </div>
                </div>
                
                <div class="chat-messages">
                    <div class="message">
                        <div class="message-avatar">JD</div>
                        <div class="message-content">
                            <div class="message-text">Hello! I'm interested in your cow listing. Is it still available?</div>
                            <div class="message-time">2:30 PM</div>
                        </div>
                    </div>
                    
                    <div class="message sent">
                        <div class="message-avatar"><?php echo strtoupper(substr($user_fname, 0, 1) . substr($user_lname, 0, 1)); ?></div>
                        <div class="message-content">
                            <div class="message-text">Yes, it's still available! The cow is 3 years old and weighs 450kg.</div>
                            <div class="message-time">2:32 PM</div>
                        </div>
                    </div>
                    
                    <div class="message">
                        <div class="message-avatar">JD</div>
                        <div class="message-content">
                            <div class="message-text">Great! Can I visit your farm to see the cow in person?</div>
                            <div class="message-time">2:35 PM</div>
                        </div>
                    </div>
                </div>
                
                <div class="chat-input">
                    <div class="input-group">
                        <textarea class="message-input" placeholder="Type your message..." rows="2"></textarea>
                        <button class="send-btn">Send</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function selectConversation(conversationId) {
            // Remove active class from all conversations
            document.querySelectorAll('.conversation-item').forEach(item => {
                item.classList.remove('active');
            });
            
            // Add active class to selected conversation
            event.target.closest('.conversation-item').classList.add('active');
            
            // Here you would typically load the conversation messages
            console.log('Selected conversation:', conversationId);
        }
        
        // Auto-resize textarea
        document.querySelector('.message-input').addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = this.scrollHeight + 'px';
        });
    </script>
</body>
</html>
