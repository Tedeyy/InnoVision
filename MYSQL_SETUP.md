# MySQL Database Setup for InnoVision

This guide will help you connect your InnoVision PHP application to a local MySQL database.

## Prerequisites

1. **XAMPP** (or similar) with MySQL server running
2. **PHP** with PDO MySQL extension enabled
3. **MySQL** database server

## Setup Steps

### 1. Start MySQL Server
- Open XAMPP Control Panel
- Start MySQL service
- Ensure MySQL is running on port 3306

### 2. Create Database
1. Open phpMyAdmin (http://localhost/phpmyadmin)
2. Create a new database named `InnoVision`
3. Or run this SQL command:
   ```sql
   CREATE DATABASE InnoVision;
   ```

### 3. Import Database Schema
1. Open the `database_setup.sql` file in a text editor
2. Copy the entire contents
3. In phpMyAdmin, select the `InnoVision` database
4. Go to the "SQL" tab
5. Paste the SQL code and click "Go"

### 4. Configure Database Connection
The database connection is configured in `config/database.php`. Default settings:
- **Host**: localhost
- **Database**: InnoVision
- **Username**: root
- **Password**: (empty)

If your MySQL setup uses different credentials, update the `config/database.php` file:

```php
private $host = 'localhost';
private $db_name = 'InnoVision';
private $username = 'your_username';
private $password = 'your_password';
```

### 5. Test Connection
1. Open your browser
2. Navigate to `http://localhost/InnoVision/test_connection.php`
3. You should see a success message if everything is configured correctly

## Database Tables Created

The setup script creates the following tables:

### `users` table
- Stores user information (buyers, sellers, admins)
- Fields: id, user_fname, user_mname, user_lname, bdate, contact, email, rsbsanum, idnum, username, password, valid_id_path, user_type, status, created_at, updated_at

### `products` table
- Stores product information for sellers
- Fields: id, seller_id, product_name, description, price, category, image_path, stock_quantity, status, created_at, updated_at

### `orders` table
- Stores order information
- Fields: id, buyer_id, seller_id, product_id, quantity, total_amount, order_status, order_date, delivery_date

## Default Admin Account

A default admin account is created:
- **Username**: admin
- **Password**: password
- **Email**: admin@innovision.com

## Features Implemented

### Authentication System
- User login with MySQL authentication
- Password hashing for security
- Session management
- User type-based redirection (buyer/seller/admin)

### Registration System
- Seller registration with form validation
- Username and email uniqueness checks
- File upload for valid ID
- Data storage in MySQL database

### Security Features
- Password hashing using PHP's `password_hash()`
- SQL injection prevention with prepared statements
- Input validation and sanitization
- Session-based authentication

## Troubleshooting

### Common Issues

1. **Connection Error**
   - Check if MySQL server is running
   - Verify database credentials in `config/database.php`
   - Ensure the `InnoVision` database exists

2. **Table Not Found**
   - Run the `database_setup.sql` script
   - Check if all tables were created successfully

3. **Permission Denied**
   - Check MySQL user permissions
   - Ensure the user has CREATE, INSERT, UPDATE, DELETE privileges

4. **PDO Extension Not Found**
   - Enable PDO MySQL extension in PHP
   - In XAMPP, edit `php.ini` and uncomment: `extension=pdo_mysql`

### Testing Your Setup

1. **Test Database Connection**
   ```
   http://localhost/InnoVision/test_connection.php
   ```

2. **Test Registration**
   ```
   http://localhost/InnoVision/pages/authentication/seller/req.php
   ```

3. **Test Login**
   ```
   http://localhost/InnoVision/pages/authentication/login.php
   ```

## File Structure

```
InnoVision/
├── config/
│   ├── database.php          # Database connection configuration
│   └── UserManager.php       # User management class
├── pages/authentication/
│   ├── login.php            # Updated login with MySQL auth
│   ├── login.css            # Login form styles
│   └── seller/
│       ├── req.php          # Registration form
│       └── conreq.php       # Updated to save to MySQL
├── database_setup.sql       # Database schema
├── test_connection.php      # Connection test script
└── MYSQL_SETUP.md          # This setup guide
```

## Next Steps

1. Test the registration and login functionality
2. Customize the database schema if needed
3. Add more user management features
4. Implement product management for sellers
5. Add order management functionality

## Support

If you encounter any issues:
1. Check the error messages in `test_connection.php`
2. Verify MySQL server status
3. Check PHP error logs
4. Ensure all file paths are correct
