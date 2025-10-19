<?php
/**
 * Debug Buyer Registration Script
 * This script will help identify issues with the buyer registration process
 */

echo "<h2>InnoVision Buyer Registration Debug</h2>";

// Test 1: Check if database connection works
echo "<h3>1. Database Connection Test</h3>";
require_once 'config/database.php';

$database = new Database();
$conn = $database->getConnection();

if ($conn) {
    echo "✅ Database connection successful!<br>";
} else {
    echo "❌ Database connection failed!<br>";
    exit;
}

// Test 2: Check if reviewbuyer table exists
echo "<h3>2. Table Structure Test</h3>";
try {
    $stmt = $conn->query("DESCRIBE reviewbuyer");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Table 'reviewbuyer' exists with columns:<br>";
    echo "<ul>";
    foreach ($columns as $column) {
        echo "<li>" . $column['Field'] . " (" . $column['Type'] . ")</li>";
    }
    echo "</ul>";
    
    // Check if docs_path column exists
    $hasDocsPath = false;
    foreach ($columns as $column) {
        if ($column['Field'] === 'docs_path') {
            $hasDocsPath = true;
            break;
        }
    }
    
    if ($hasDocsPath) {
        echo "✅ docs_path column exists<br>";
    } else {
        echo "❌ docs_path column missing! You need to add it:<br>";
        echo "<code>ALTER TABLE reviewbuyer ADD COLUMN docs_path VARCHAR(255);</code><br>";
    }
    
} catch (PDOException $e) {
    echo "❌ Error checking table: " . $e->getMessage() . "<br>";
    echo "The 'reviewbuyer' table might not exist. Please create it first.<br>";
}

// Test 3: Test BuyerRegistrationHandler
echo "<h3>3. BuyerRegistrationHandler Test</h3>";
require_once 'config/BuyerRegistrationHandler.php';

try {
    $handler = new BuyerRegistrationHandler();
    echo "✅ BuyerRegistrationHandler created successfully<br>";
    
    // Test with sample data
    $testData = [
        'user_fname' => 'Test',
        'user_mname' => 'Buyer',
        'user_lname' => 'Name',
        'bdate' => '1990-01-01',
        'contact' => '1234567890',
        'email' => 'buyer@example.com',
        'supdoctype' => 'Driver\'s License',
        'supdocnum' => 'DL123456',
        'username' => 'testbuyer' . time(),
        'password' => 'testpass123',
        'docs_path' => 'upload/test_buyer_docs.jpg'
    ];
    
    echo "Testing with sample data...<br>";
    $result = $handler->registerBuyer($testData);
    echo "Registration result: " . ($result ? "SUCCESS" : "FAILED") . "<br>";
    
} catch (Exception $e) {
    echo "❌ Error with BuyerRegistrationHandler: " . $e->getMessage() . "<br>";
}

// Test 4: Check current session data
echo "<h3>4. Session Data Test</h3>";
session_start();
echo "Current session data:<br>";
echo "<pre>" . print_r($_SESSION, true) . "</pre>";

echo "<hr>";
echo "<h3>Next Steps:</h3>";
echo "<ol>";
echo "<li>Make sure MySQL server is running</li>";
echo "<li>Ensure 'innovisionv1' database exists</li>";
echo "<li>Check if 'reviewbuyer' table exists</li>";
echo "<li>Add 'docs_path' column if missing</li>";
echo "<li>Test the buyer registration form</li>";
echo "</ol>";

echo "<h3>Buyer Registration URLs:</h3>";
echo "<ul>";
echo "<li><a href='pages/authentication/buyer/req.php'>Buyer Registration Form</a></li>";
echo "<li><a href='pages/authentication/login.php'>Login Page</a></li>";
echo "</ul>";
?>
