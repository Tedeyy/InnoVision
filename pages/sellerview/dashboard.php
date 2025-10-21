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

// Get user's listings
$listings = [];
if ($conn) {
    try {
        // Get listings from all three tables
        $all_listings = [];
        
        // Under Review listings
        $stmt = $conn->prepare("SELECT listing_id, livestock_type, breed, age, weight, price, 'Under Review' as status, created FROM reviewlivestocklisting WHERE seller_id = ? ORDER BY created DESC");
        $stmt->execute([$user_id]);
        $under_review = $stmt->fetchAll();
        
        // Verified listings
        $stmt = $conn->prepare("SELECT listing_id, livestock_type, breed, age, weight, price, 'Verified' as status, created FROM livestocklisting WHERE seller_id = ? ORDER BY created DESC");
        $stmt->execute([$user_id]);
        $verified = $stmt->fetchAll();
        
        // Sold listings
        $stmt = $conn->prepare("SELECT listing_id, livestock_type, breed, age, weight, price, 'Sold' as status, created FROM soldlivestocklisting WHERE seller_id = ? ORDER BY created DESC");
        $stmt->execute([$user_id]);
        $sold = $stmt->fetchAll();
        
        $listings = array_merge($under_review, $verified, $sold);
        
        // Sort by creation date
        usort($listings, function($a, $b) {
            return strtotime($b['created']) - strtotime($a['created']);
        });
        
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
    }
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
        
        .nav-links a:hover {
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
        
        .dashboard-header {
            margin-bottom: 2rem;
        }
        
        .dashboard-title {
            font-size: 2rem;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 0.5rem;
        }
        
        .dashboard-subtitle {
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
        
        .create-listing-section {
            text-align: center;
            padding: 2rem;
        }
        
        .create-listing-btn {
            background: #d69e2e;
            color: white;
            padding: 0.75rem 2rem;
            border: none;
            border-radius: 0.5rem;
            font-size: 1.125rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: background 0.2s;
        }
        
        .create-listing-btn:hover {
            background: #b7791f;
        }
        
        .listings-section h3 {
            font-size: 1.5rem;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 1rem;
        }
        
        .listings-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        
        .listings-table th,
        .listings-table td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .listings-table th {
            background: #f7fafc;
            font-weight: 600;
            color: #4a5568;
        }
        
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 500;
        }
        
        .status-under-review {
            background: #fef3c7;
            color: #92400e;
        }
        
        .status-verified {
            background: #d1fae5;
            color: #065f46;
        }
        
        .status-sold {
            background: #e5e7eb;
            color: #374151;
        }
        
        .no-listings {
            text-align: center;
            color: #6b7280;
            padding: 2rem;
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
            
            .listings-table {
                font-size: 0.875rem;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <a href="dashboard.php" class="nav-brand">InnoVision</a>
            <ul class="nav-links">
                <li><a href="../pricewatch.php">PriceWatch</a></li>
                <li><a href="listings.php">Listings</a></li>
                <li><a href="profile.php">Profile</a></li>
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
        <div class="dashboard-header">
            <h1 class="dashboard-title">Seller Dashboard</h1>
            <p class="dashboard-subtitle">Manage your livestock listings and track your sales</p>
        </div>

        <div class="card create-listing-section">
            <h3>Create New Listing</h3>
            <p>Add a new livestock listing to your inventory</p>
            <a href="create_listing.php" class="create-listing-btn">Create Listing</a>
        </div>

        <div class="card listings-section">
            <h3>Your Listings</h3>
            <?php if (empty($listings)): ?>
                <div class="no-listings">
                    <p>You haven't created any listings yet. <a href="create_listing.php">Create your first listing</a> to get started!</p>
                </div>
            <?php else: ?>
                <table class="listings-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Type</th>
                            <th>Breed</th>
                            <th>Age</th>
                            <th>Weight</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($listings as $listing): ?>
                        <tr>
                            <td>#<?php echo htmlspecialchars($listing['listing_id']); ?></td>
                            <td><?php echo htmlspecialchars($listing['livestock_type']); ?></td>
                            <td><?php echo htmlspecialchars($listing['breed']); ?></td>
                            <td><?php echo htmlspecialchars($listing['age']); ?> years</td>
                            <td><?php echo htmlspecialchars($listing['weight']); ?> kg</td>
                            <td>₱<?php echo number_format($listing['price'], 2); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $listing['status'])); ?>">
                                    <?php echo htmlspecialchars($listing['status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('M j, Y', strtotime($listing['created'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>