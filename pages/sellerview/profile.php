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

// Check verification status and get user details
$verification_status = 'Pending';
$user_details = [];
$db = new Database();
$conn = $db->getConnection();

if ($conn) {
    try {
        // Check if user is in seller table (verified)
        $stmt = $conn->prepare("SELECT * FROM seller WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $user_details = $stmt->fetch();
        if ($user_details) {
            $verification_status = 'Verified';
        } else {
            // Check if user is in reviewseller table (pending)
            $stmt = $conn->prepare("SELECT * FROM reviewseller WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $user_details = $stmt->fetch();
            if ($user_details) {
                $verification_status = 'Pending';
            }
        }
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
    }
}

// Handle profile update
$update_message = '';
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    try {
        $new_fname = $_POST['user_fname'] ?? '';
        $new_lname = $_POST['user_lname'] ?? '';
        $new_contact = $_POST['contact'] ?? '';
        $new_email = $_POST['email'] ?? '';
        
        if ($verification_status === 'Verified') {
            $stmt = $conn->prepare("UPDATE seller SET user_fname = ?, user_lname = ?, contact = ?, email = ? WHERE user_id = ?");
            $stmt->execute([$new_fname, $new_lname, $new_contact, $new_email, $user_id]);
        } else {
            $stmt = $conn->prepare("UPDATE reviewseller SET user_fname = ?, user_lname = ?, contact = ?, email = ? WHERE user_id = ?");
            $stmt->execute([$new_fname, $new_lname, $new_contact, $new_email, $user_id]);
        }
        
        // Update session variables
        $_SESSION['user_fname'] = $new_fname;
        $_SESSION['user_lname'] = $new_lname;
        
        $update_message = 'Profile updated successfully!';
        
        // Refresh user details
        if ($verification_status === 'Verified') {
            $stmt = $conn->prepare("SELECT * FROM seller WHERE user_id = ?");
        } else {
            $stmt = $conn->prepare("SELECT * FROM reviewseller WHERE user_id = ?");
        }
        $stmt->execute([$user_id]);
        $user_details = $stmt->fetch();
        
    } catch (PDOException $e) {
        $update_message = 'Error updating profile: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Seller Dashboard</title>
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
        
        .card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            margin-bottom: 1.5rem;
        }
        
        .profile-grid {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 2rem;
        }
        
        .profile-sidebar {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        
        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: #d69e2e;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            font-weight: 700;
            color: white;
            margin: 0 auto 1rem;
        }
        
        .profile-info {
            text-align: center;
        }
        
        .profile-name {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 0.5rem;
        }
        
        .profile-role {
            color: #4a5568;
            margin-bottom: 1rem;
        }
        
        .verification-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 0.5rem;
        }
        
        .form-input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            font-size: 1rem;
            transition: border-color 0.2s;
        }
        
        .form-input:focus {
            outline: none;
            border-color: #d69e2e;
            box-shadow: 0 0 0 3px rgba(214, 158, 46, 0.1);
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        
        .btn {
            background: #d69e2e;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 0.5rem;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }
        
        .btn:hover {
            background: #b7791f;
        }
        
        .btn-secondary {
            background: #6b7280;
        }
        
        .btn-secondary:hover {
            background: #4b5563;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }
        
        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        
        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .info-card {
            background: #f7fafc;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            padding: 1rem;
            text-align: center;
        }
        
        .info-label {
            color: #4a5568;
            font-size: 0.875rem;
            margin-bottom: 0.5rem;
        }
        
        .info-value {
            font-weight: 600;
            color: #2d3748;
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
            
            .profile-grid {
                grid-template-columns: 1fr;
            }
            
            .form-row {
                grid-template-columns: 1fr;
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
                <li><a href="profile.php" class="active">Profile</a></li>
                <li><a href="chat.php">Chat</a></li>
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
            <h1 class="page-title">Profile</h1>
            <p class="page-subtitle">Manage your account information and settings</p>
        </div>

        <?php if ($update_message): ?>
            <div class="alert <?php echo strpos($update_message, 'Error') !== false ? 'alert-error' : 'alert-success'; ?>">
                <?php echo htmlspecialchars($update_message); ?>
            </div>
        <?php endif; ?>

        <div class="profile-grid">
            <div class="profile-sidebar">
                <div class="card">
                    <div class="profile-avatar">
                        <?php echo strtoupper(substr($user_fname, 0, 1) . substr($user_lname, 0, 1)); ?>
                    </div>
                    <div class="profile-info">
                        <div class="profile-name"><?php echo htmlspecialchars($user_fname . ' ' . $user_lname); ?></div>
                        <div class="profile-role">Seller</div>
                        <div class="verification-badge <?php echo $verification_status === 'Verified' ? 'status-verified' : 'status-pending'; ?>">
                            <?php echo $verification_status; ?>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <h3>Account Information</h3>
                    <div class="info-grid">
                        <div class="info-card">
                            <div class="info-label">User ID</div>
                            <div class="info-value">#<?php echo htmlspecialchars($user_id); ?></div>
                        </div>
                        <div class="info-card">
                            <div class="info-label">Username</div>
                            <div class="info-value"><?php echo htmlspecialchars($user_details['username'] ?? ''); ?></div>
                        </div>
                        <div class="info-card">
                            <div class="info-label">RSBSA Number</div>
                            <div class="info-value"><?php echo htmlspecialchars($user_details['rsbsanum'] ?? ''); ?></div>
                        </div>
                        <div class="info-card">
                            <div class="info-label">ID Number</div>
                            <div class="info-value"><?php echo htmlspecialchars($user_details['idnum'] ?? ''); ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <h3>Edit Profile</h3>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="update_profile">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="user_fname">First Name</label>
                            <input type="text" id="user_fname" name="user_fname" class="form-input" 
                                   value="<?php echo htmlspecialchars($user_details['user_fname'] ?? $user_fname); ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="user_lname">Last Name</label>
                            <input type="text" id="user_lname" name="user_lname" class="form-input" 
                                   value="<?php echo htmlspecialchars($user_details['user_lname'] ?? $user_lname); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="contact">Contact Number</label>
                        <input type="tel" id="contact" name="contact" class="form-input" 
                               value="<?php echo htmlspecialchars($user_details['contact'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="email">Email Address</label>
                        <input type="email" id="email" name="email" class="form-input" 
                               value="<?php echo htmlspecialchars($user_details['email'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="bdate">Birth Date</label>
                        <input type="date" id="bdate" name="bdate" class="form-input" 
                               value="<?php echo htmlspecialchars($user_details['bdate'] ?? ''); ?>" readonly>
                    </div>
                    
                    <div style="display: flex; gap: 1rem;">
                        <button type="submit" class="btn">Update Profile</button>
                        <a href="dashboard.php" class="btn btn-secondary" style="text-decoration: none; display: inline-block;">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
