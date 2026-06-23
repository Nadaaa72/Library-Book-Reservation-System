<?php
// Start the session to access user authentication data and store messages
session_start();

// Include the database connection file to establish connection to MySQL database
require_once 'db_connection.php';


// If no username exists in the session, the user is not authenticated
if (!isset($_SESSION['username'])) {
    
    header("Location: index.php");
    
    exit();
}

// FORM VALIDATION: Check if this page was accessed via POST method with ISBN data
// This prevents direct URL access and ensures proper form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['isbn'])) {
    
    // Retrieve the logged-in username from session - this identifies who is making the reservation
    $username = $_SESSION['username'];
    
    // Sanitize the ISBN input to prevent special characters from breaking SQL queries
    // mysqli_real_escape_string escapes characters like quotes that could be used in SQL injection
    $isbn = mysqli_real_escape_string($conn, $_POST['isbn']);
    
    // groups multiple sql queries together so they are all executed at once
    // This prevents partial updates that could leave the database in an inconsistent state
    mysqli_begin_transaction($conn);
    
    // TRY-CATCH BLOCK: Wrap all database operations to handle errors gracefully
    try {
        
        // Prepare a SQL statement to check if the book exists and its reservation status
        $check_stmt = mysqli_prepare($conn, "SELECT Reserved FROM books WHERE ISBN = ?");
        
        // Bind the ISBN parameter to the prepared statement
        mysqli_stmt_bind_param($check_stmt, "s", $isbn);
        
        // Execute the prepared statement to query the database
        mysqli_stmt_execute($check_stmt);
        
        // Retrieve the result set from the executed query
        $result = mysqli_stmt_get_result($check_stmt);
        
        // Fetch the result as an associative array (column names as keys)
        // This gives us access to the Reserved field: $book['Reserved']
        $book = mysqli_fetch_assoc($result);
        
        // Close the prepared statement to free up resources
        mysqli_stmt_close($check_stmt);
        
        // VALIDATION 1: Check if the book exists in the database
        // If mysqli_fetch_assoc returned nothing, the ISBN doesn't exist
        if (!$book) {

            // Throw an exception to trigger the catch block and rollback the transaction
            throw new Exception("Book not found.");
        }
        
        // VALIDATION 2: Check if the book is already reserved by someone else
        if ($book['Reserved'] == 'Y') {

            // Prevent double-booking by rejecting the reservation attempt
            throw new Exception("This book is already reserved by another user.");
        }
        
       
        // Get the current date in MySQL-compatible format (YYYY-MM-DD)
        $reservation_date = date('Y-m-d');
        
        // Prepare SQL statement to insert a new reservation record
        // This creates a record linking the user, book, and reservation date
        $insert_stmt = mysqli_prepare($conn, "INSERT INTO reservations (Username, ISBN, ReservedDate) VALUES (?, ?, ?)");
        
        // Bind all three parameters: username, ISBN, and date (all strings - "sss")
        mysqli_stmt_bind_param($insert_stmt, "sss", $username, $isbn, $reservation_date);
        
        // Execute the insert operation and check if it succeeded
        if (!mysqli_stmt_execute($insert_stmt)) {
            
            throw new Exception("Failed to create reservation.");
        }
        
        // Close the insert statement to free resources
        mysqli_stmt_close($insert_stmt);
        
        
        // Prepare SQL statement to mark the book as reserved in the books table
        // This prevents other users from reserving the same book
        $update_stmt = mysqli_prepare($conn, "UPDATE books SET Reserved = 'Y' WHERE ISBN = ?");
        
        // Bind the ISBN parameter to identify which book to update
        mysqli_stmt_bind_param($update_stmt, "s", $isbn);
        
        // Execute the update operation and verify success
        if (!mysqli_stmt_execute($update_stmt)) {

            // If update fails, throw exception to trigger rollback
            throw new Exception("Failed to update book status.");
        }
        
        // Close the update statement
        mysqli_stmt_close($update_stmt);
        
        
        // If we reach this point, all operations succeeded
        // mysqli_commit makes all changes permanent in the database
        mysqli_commit($conn);
        
        // Store a success message in the session to display on the next page
        $_SESSION['reservation_success'] = "Book reserved successfully!";
        
    } catch (Exception $e) {
        // EXCEPTION HANDLER: This block executes if any error occurred above
        
        // Undo all database changes made during this transaction
        // This ensures the database stays consistent even if something failed
        mysqli_rollback($conn);
        
        // Store the error message in session to display to the user
        // getMessage() retrieves the error text from the Exception object
        $_SESSION['reservation_error'] = $e->getMessage();
    }
    
    // Close the database connection to free up server resources
    mysqli_close($conn);
    
    // Redirect the user back to the search page 
    // This prevents the reservation from being submitted again if the user refreshes
    header("Location: search.php");
    
    // Stop script execution after redirect
    exit();
    
} else {
    
    // SECURITY MEASURE: If someone tries to access this page directly without submitting the form
    // redirect them to the search page instead of showing an error
    header("Location: search.php");
    exit();
}
?>
