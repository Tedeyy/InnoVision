<?php
/**
 * Test Database Connection Script
 * Run this file to test your MySQL database connection
 */

require_once 'config/database.php';

echo "<h2>InnoVision Database Connection Test</h2>";

// Test database connection
$database = new Database();
$connection = $database->getConnection();

if ($connection) {
    echo "<p style='color: green;'>✅ Database connection successful!</p>";
    
    // Test if tables exist
    try {
        $stmt = $connection->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo "<h3>Database Tables:</h3>";
        if (empty($tables)) {
            echo "<p style='color: orange;'>⚠️ No tables found. Please run the database_setup.sql script first.</p>";
        } else {
            echo "<ul>";
            foreach ($tables as $table) {
                echo "<li>$table</li>";
            }
            echo "</ul>";
        }
        
        // Test RegistrationHandler
        echo "<h3>Testing RegistrationHandler:</h3>";
        require_once 'config/RegistrationHandler.php';
        $registrationHandler = new RegistrationHandler();
        echo "<p style='color: green;'>✅ RegistrationHandler class loaded successfully!</p>";
        
    } catch (PDOException $e) {
        echo "<p style='color: red;'>❌ Error checking tables: " . $e->getMessage() . "</p>";
    }
    
} else {
    echo "<p style='color: red;'>❌ Database connection failed!</p>";
    echo "<p>Please check:</p>";
    echo "<ul>";
    echo "<li>MySQL server is running</li>";
    echo "<li>Database 'InnoVision' exists</li>";
    echo "<li>Username and password are correct</li>";
    echo "<li>PHP PDO MySQL extension is enabled</li>";
    echo "</ul>";
}

echo "<hr>";
echo "<h3>Next Steps:</h3>";
echo "<ol>";
echo "<li>Make sure MySQL server is running</li>";
echo "<li>Create the 'InnoVision' database in MySQL</li>";
echo "<li>Run the database_setup.sql script to create tables</li>";
echo "<li>Test the login and registration functionality</li>";
echo "</ol>";
?>
