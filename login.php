<?php
// This script processes login form submissions and authenticates users

session_start(); // Start session to store user authentication data after successful login

// Include database connection file to access the MySQL database
require_once 'db_connection.php';

// This ensures users can't access this page directly via URL
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $username = trim($_POST['username']); // Get username from form and remove leading/trailing whitespace
    
    $password = $_POST['password']; // Get password from form 
    
    $errors = [];  // Initialize empty array to collect any validation errors
    
    // Validate username field is not empty
    if (empty($username)) {
        $errors[] = "Username is required.";
    }
    
    // Validate password field is not empty
    if (empty($password)) {
        $errors[] = "Password is required.";
    }
    
    // If any validation errors exist, redirect back to login page
    if (!empty($errors)) {
        
        $_SESSION['login_error'] = implode(" ", $errors); // Combine all error messages into a single string separated by spaces. implode() joins all elements of an array into a single string 
        
        header("Location: index.php"); // Redirect back to index page to display errors
        
        exit();
    }
    
    // STEP 3: DATABASE QUERY TO FIND USER

    // Prepare SQL statement to retrieve user data by username
    $stmt = mysqli_prepare($conn, "SELECT Username, Password, FirstName, Surname FROM users WHERE Username = ?");
    
    // "s" indicates the parameter is a string type
    // inserts username to the sql query 
    mysqli_stmt_bind_param($stmt, "s", $username);
    
    // Execute the prepared statement to query the database
    mysqli_stmt_execute($stmt);
    
    // Retrieve the result set from the query
    $result = mysqli_stmt_get_result($stmt);
    

    // STEP 4: CHECK IF USER EXISTS AND VERIFY PASSWORD

    // returns the user record as an associative array (1 row at a time)
    if ($row = mysqli_fetch_assoc($result)) {
        
        // password_verify() compares the plain text password with the hashed password from database
        // This is secure because the hash cannot be reversed to get the original password
        if (password_verify($password, $row['Password'])) {
            
            // Store username in session for authentication checks
            $_SESSION['username'] = $row['Username'];
            
            // Store first name for personalized greeting
            $_SESSION['first_name'] = $row['FirstName'];
            
            // Store last name for personalized greeting
            $_SESSION['last_name'] = $row['Surname'];
            
            // Clean up: close the prepared statement to free resources
            mysqli_stmt_close($stmt);
            
            // Close database connection since we're done with it
            mysqli_close($conn);
            
            // Redirect authenticated user to the main search page
            header("Location: search.php");
            exit();
        } else {
            
            $_SESSION['login_error'] = "Invalid username or password.";
            
            // Clean up resources
            mysqli_stmt_close($stmt);
            mysqli_close($conn);
            
            // Redirect back to login page
            header("Location: index.php");
            exit();
        }
    } else {
        
        $_SESSION['login_error'] = "Invalid username or password.";
        
        // Clean up resources
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        
        // Redirect back to login page
        header("Location: index.php");
        exit();
    }
    
} else {
    
    header("Location: index.php");
    exit();
}
?>
