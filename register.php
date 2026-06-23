<?php
// USER REGISTRATION HANDLER
// This script processes new user registration form submissions

// store success/error messages after processing
session_start();

require_once 'db_connection.php';

// FORM SUBMISSION CHECK: Verify this page was accessed via POST method
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Get username and remove whitespace
    $username = trim($_POST['username']);
    
    // Get first name and remove whitespace
    $first_name = trim($_POST['first_name']);
    
    // Get last name and remove whitespace
    $last_name = trim($_POST['last_name']);
    
    // Get address line and remove whitespace
    $address_line = trim($_POST['address_line']);
    
    // Get address town and remove whitespace
    $address_town = trim($_POST['address_town']);
    
    // Get mobile number and remove whitespace
    $mobile = trim($_POST['mobile']);
    
    // Get password - no trimming to preserve exact password characters
    $password = $_POST['password'];
    
    // Get password confirmation - no trimming
    $confirm_password = $_POST['confirm_password'];
    
    // an array to collect all validation error messages
    $errors = [];
    
    // Validate username - must not be empty
    if (empty($username)) {
        $errors[] = "Username is required.";
    }
    
    // Validate first name - must not be empty
    if (empty($first_name)) {
        $errors[] = "First name is required.";
    }
    
    // Validate last name - must not be empty
    if (empty($last_name)) {
        $errors[] = "Last name is required.";
    }
    
    // Validate address line - must not be empty
    if (empty($address_line)) {
        $errors[] = "Address line is required.";
    }
    
    // Validate address town - must not be empty
    if (empty($address_town)) {
        $errors[] = "Address town is required.";
    }
    
    // Validate mobile number with multiple checks
    // First check: mobile field must not be empty
    if (empty($mobile)) {
        $errors[] = "Mobile number is required.";
    } 

    // Second check: mobile must contain only numeric digits (no letters or special characters)
    // ctype_digit() returns true only if all characters are digits 0-9
    elseif (!ctype_digit($mobile)) {
        $errors[] = "Mobile number must contain only digits.";
    } 

    // Third check: mobile must be exactly 10 digits long
    // strlen() returns the number of characters in the string
    elseif (strlen($mobile) != 10) {
        $errors[] = "Mobile number must be exactly 10 digits.";
    }
    
    // First check: password must not be empty
    if (empty($password)) {
        $errors[] = "Password is required.";
    } 
    // Second check: password must meet minimum length requirement of 6 characters
    elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long.";
    }
    
    // Validate password confirmation matches the original password
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }
    

    // Only perform database check if all previous validations passed
    if (empty($errors)) {

        // Prepare SQL statement to check if username already exists
        $stmt = mysqli_prepare($conn, "SELECT Username FROM users WHERE Username = ?");
        
        // Bind the username parameter
        mysqli_stmt_bind_param($stmt, "s", $username);
        
        // Execute the query
        mysqli_stmt_execute($stmt);
        
        // Store the result to check row count
        mysqli_stmt_store_result($stmt);
        
        // If any rows were returned, username already exists
        // mysqli_stmt_num_rows() returns the number of matching records
        if (mysqli_stmt_num_rows($stmt) > 0) {
            $errors[] = "Username already exists. Please choose a different username.";
        }
        
        // Close statement to free resources
        mysqli_stmt_close($stmt);
    }
    
    // STEP 4: HANDLE VALIDATION FAILURES
    // If any validation errors were collected, stop processing and show errors
    if (!empty($errors)) {

        // Combine all error messages into a single string separated by spaces
        // implode() joins array elements with the specified separator
        $_SESSION['register_error'] = implode(" ", $errors);
        
        // Redirect back to registration form to display errors
        header("Location: index.php");
        
        // Stop script execution
        exit();
    }
    
   
    // password_hash() creates a bcrypt hash of the password
    // PASSWORD_DEFAULT uses the strongest available algorithm (currently bcrypt)
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // insert new user into database
    $stmt = mysqli_prepare($conn, "INSERT INTO users (Username, Password, FirstName, Surname, AddressLine, AddressLineTown, Mobile) VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    // Bind all 7 parameters to the prepared statement
    mysqli_stmt_bind_param($stmt, "sssssss", $username, $hashed_password, $first_name, $last_name, $address_line, $address_town, $mobile);
    
  
    // Attempt to execute the prepared statement
    if (mysqli_stmt_execute($stmt)) {
        
        // Store success message in session to display on login page
        $_SESSION['register_success'] = "Registration successful! You can now login.";
        
        // Clean up: close the prepared statement
        mysqli_stmt_close($stmt);
        
        // Close database connection
        mysqli_close($conn);
        
        // Redirect to index page where user can now log in
        header("Location: index.php");
        exit();
    } else {
       
        // Store generic error message (don't expose database details)
        $_SESSION['register_error'] = "Registration failed. Please try again.";
        
        // Clean up resources
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        
        // Redirect back to registration form
        header("Location: index.php");
        exit();
    }
    
} else {
    
    // SECURITY MEASURE: If someone tries to access this page directly (not via form submission)
    // redirect them to the index page instead of showing an error
    header("Location: index.php");
    exit();
}
?>
