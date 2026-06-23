<?php
// LOGOUT HANDLER
// This script terminates the user's session and redirects them to the login page

// Start the session to access existing session data
// This is necessary because we need to access the session before we can destroy it
session_start();

// STEP 1: Remove all session variables
// session_unset() clears all session variables (username, first_name, last_name, etc.)
// but keeps the session itself active
session_unset();

// STEP 2: Destroy the session completely
// session_destroy() deletes the session file from the server
// This ensures no session data remains that could be exploited
session_destroy();

// STEP 3: Redirect user to the login page
// After logout, users should return to the index page where they can log in again
header("Location: index.php");

// Stop script execution to ensure the redirect happens immediately
// No code after this line will execute
exit();
?>
