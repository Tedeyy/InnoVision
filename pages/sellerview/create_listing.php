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

// Handle form submission
$success_message = '';
$error_message = '';

if ($_POST && isset($_POST['action']) && $_POST['action'] === 'create_listing') {
    try {
        $livestock_type = $_POST['livestock_type'] ?? '';
        $breed = $_POST['breed'] ?? '';
        $age = (int)($_POST['age'] ?? 0);
        $weight = (float)($_POST['weight'] ?? 0);
        $price = (float)($_POST['price'] ?? 0);
        
        // Handle file upload
        $docs_path = '';
        if (isset($_FILES['docs']) && $_FILES['docs']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../../pages/authentication/seller/upload/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_extension = pathinfo($_FILES['docs']['name'], PATHINFO_EXTENSION);
            $filename = $user_fname . '_' . $user_lname . '_docs_' . time() . '.' . $file_extension;
            $docs_path = $upload_dir . $filename;
            
            if (move_uploaded_file($_FILES['docs']['tmp_name'], $docs_path)) {
                $docs_path = 'pages/authentication/seller/upload/' . $filename;
            } else {
                throw new Exception('Failed to upload file');
            }
        }
        
        // Insert into reviewlivestocklisting table
        $stmt = $conn->prepare("INSERT INTO reviewlivestocklisting (seller_id, livestock_type, breed, age, weight, price, docs_path) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $livestock_type, $breed, $age, $weight, $price, $docs_path]);
        
        $success_message = 'Listing created successfully! It will be reviewed by administrators before being published.';
        
    } catch (Exception $e) {
        $error_message = 'Error creating listing: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Listing - Seller Dashboard</title>
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
            max-width: 800px;
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
            padding: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
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
        
        .form-input,
        .form-select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            font-size: 1rem;
            transition: border-color 0.2s;
        }
        
        .form-input:focus,
        .form-select:focus {
            outline: none;
            border-color: #d69e2e;
            box-shadow: 0 0 0 3px rgba(214, 158, 46, 0.1);
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        
        .file-upload {
            border: 2px dashed #e2e8f0;
            border-radius: 0.5rem;
            padding: 2rem;
            text-align: center;
            transition: border-color 0.2s;
            cursor: pointer;
        }
        
        .file-upload:hover {
            border-color: #d69e2e;
        }
        
        .file-upload input[type="file"] {
            display: none;
        }
        
        .file-upload-text {
            color: #6b7280;
            margin-bottom: 0.5rem;
        }
        
        .file-upload-hint {
            font-size: 0.875rem;
            color: #9ca3af;
        }
        
        .btn {
            background: #d69e2e;
            color: white;
            padding: 0.75rem 2rem;
            border: none;
            border-radius: 0.5rem;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
            text-decoration: none;
            display: inline-block;
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
        
        .form-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: 2rem;
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
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .form-actions {
                flex-direction: column;
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
            <h1 class="page-title">Create New Listing</h1>
            <p class="page-subtitle">Add a new livestock listing to your inventory</p>
        </div>

        <?php if ($success_message): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <form method="POST" action="" enctype="multipart/form-data">
                <input type="hidden" name="action" value="create_listing">
                
                <div class="form-group">
                    <label class="form-label" for="livestock_type">Livestock Type *</label>
                    <select id="livestock_type" name="livestock_type" class="form-select" required>
                        <option value="">Select livestock type</option>
                        <option value="Cattle">Cattle</option>
                        <option value="Pig">Pig</option>
                        <option value="Goat">Goat</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="breed">Breed *</label>
                    <select id="breed" name="breed" class="form-select" required>
                        <option value="">Select breed</option>
                    </select>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="age">Age (years) *</label>
                        <input type="number" id="age" name="age" class="form-input" 
                               min="0" max="50" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="weight">Weight (kg) *</label>
                        <input type="number" id="weight" name="weight" class="form-input" 
                               min="0" step="0.1" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="price">Price (₱) *</label>
                    <input type="number" id="price" name="price" class="form-input" 
                           min="0" step="0.01" placeholder="Enter price in Philippine Peso" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Documentation (Optional)</label>
                    <div class="file-upload" onclick="document.getElementById('docs').click()">
                        <input type="file" id="docs" name="docs" accept="image/*,.pdf">
                        <div class="file-upload-text">Click to upload photos or documents</div>
                        <div class="file-upload-hint">Supports JPG, PNG, PDF files (Max 10MB)</div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn">Create Listing</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Breed options for different livestock types
        const breedOptions = {
            'Cattle': [
                'Holstein Friesian',
                'Jersey',
                'Angus',
                'Hereford',
                'Brahman',
                'Simmental',
                'Charolais',
                'Limousin',
                'Shorthorn',
                'Santa Gertrudis',
                'Other'
            ],
            'Pig': [
                'Berkshire',
                'Duroc',
                'Hampshire',
                'Yorkshire',
                'Landrace',
                'Pietrain',
                'Tamworth',
                'Large White',
                'Chester White',
                'Poland China',
                'Other'
            ],
            'Goat': [
                'Boer',
                'Nubian',
                'Saanen',
                'Alpine',
                'Toggenburg',
                'Oberhasli',
                'LaMancha',
                'Nigerian Dwarf',
                'Angora',
                'Cashmere',
                'Other'
            ]
        };

        // Update breed options when livestock type changes
        document.getElementById('livestock_type').addEventListener('change', function() {
            const selectedType = this.value;
            const breedSelect = document.getElementById('breed');
            
            // Clear existing options
            breedSelect.innerHTML = '<option value="">Select breed</option>';
            
            // Add new options based on selected type
            if (selectedType && breedOptions[selectedType]) {
                breedOptions[selectedType].forEach(breed => {
                    const option = document.createElement('option');
                    option.value = breed;
                    option.textContent = breed;
                    breedSelect.appendChild(option);
                });
            }
        });

        // File upload preview
        document.getElementById('docs').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const uploadDiv = document.querySelector('.file-upload');
                uploadDiv.innerHTML = `
                    <div class="file-upload-text">Selected: ${file.name}</div>
                    <div class="file-upload-hint">Click to change file</div>
                `;
            }
        });
    </script>
</body>
</html>
