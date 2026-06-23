<?php
// LOGIN AND REGISTRATION PAGE (Landing Page)


// Start session to check authentication status and retrieve success/error messages
session_start();

// Include database connection 
include "db_connection.php";

// AUTO-REDIRECT: If user is already logged in, send them directly to the search page
if (isset($_SESSION['username'])) {
    header("Location: search.php");
    exit();
}

// MESSAGE HANDLING: Initialize variables to store messages from login/register handlers
// These variables will be used in the HTML to display feedback to users
$login_error = '';
$register_error = '';
$register_success = '';

// Check if there's a login error message in the session
if (isset($_SESSION['login_error'])) {
    
    $login_error = $_SESSION['login_error']; // Retrieve the error message
    
    unset($_SESSION['login_error']); // Remove it from session so it doesn't display again on page refresh
}

// Check if there's a registration error message in the session
if (isset($_SESSION['register_error'])) {
    
    $register_error = $_SESSION['register_error']; // Retrieve the error message
    
    unset($_SESSION['register_error']); // Remove it from session (one-time display)
}

// Check if there's a registration success message in the session
if (isset($_SESSION['register_success'])) {
    
    $register_success = $_SESSION['register_success']; // Retrieve the success message
    
    unset($_SESSION['register_success']); // Remove it from session (one-time display)
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Book Reservation System</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Library Book Reservation System</h1>
            <p>Search, Reserve, and Manage Your Book Reservations</p>
        </div>
        
        <div class="forms-container">
            <!-- Login Form -->
            <div class="form-section">
                <h2>Login</h2>
                <?php if ($login_error): ?>
                    <div class="error"><?php echo htmlspecialchars($login_error); ?></div>
                <?php endif; ?>
                
                <form action="login.php" method="POST">
                    <div class="form-group">
                        <label for="login-username">Username</label>
                        <input type="text" id="login-username" name="username" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="login-password">Password</label>
                        <input type="password" id="login-password" name="password" required>
                    </div>
                    
                    <button type="submit" class="btn">Login</button>
                </form>
            </div>
            
            <!-- Registration Form -->
            <div class="form-section">
                <h2>Register</h2>
                <?php if ($register_error): ?>
                    <div class="error"><?php echo htmlspecialchars($register_error); ?></div>
                <?php endif; ?>
                <?php if ($register_success): ?>
                    <div class="success"><?php echo htmlspecialchars($register_success); ?></div>
                <?php endif; ?>
                
                <form action="register.php" method="POST">
                    <div class="form-group">
                        <label for="reg-username">Username</label>
                        <input type="text" id="reg-username" name="username" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="reg-firstname">First Name</label>
                        <input type="text" id="reg-firstname" name="first_name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="reg-lastname">Last Name</label>
                        <input type="text" id="reg-lastname" name="last_name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="reg-address">Address Line</label>
                        <input type="text" id="reg-address" name="address_line" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="reg-town">Address Town</label>
                        <input type="text" id="reg-town" name="address_town" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="reg-mobile">Mobile (10 digits)</label>
                        <input type="text" id="reg-mobile" name="mobile" pattern="[0-9]{10}" 
                               title="Mobile number must be 10 digits" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="reg-password">Password (min 6 characters)</label>
                        <input type="password" id="reg-password" name="password" 
                               minlength="6" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="reg-confirm-password">Confirm Password</label>
                        <input type="password" id="reg-confirm-password" name="confirm_password" 
                               minlength="6" required>
                    </div>
                    
                    <button type="submit" class="btn">Register</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
