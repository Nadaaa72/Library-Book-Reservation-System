<?php
// This page displays all books the user has reserved and allows them to remove reservations

// Start session to access user authentication data and handle messages
session_start();

// Include database connection to query and modify reservations
require_once 'db_connection.php';

// SECURITY CHECK: Verify user is logged in

if (!isset($_SESSION['username'])) {

    // Redirect unauthorized users to login page
    header("Location: index.php");
    exit();
}

// RETRIEVE USER INFORMATION from session
// These values identify the current user and are used for personalized greeting
$username = $_SESSION['username'];
$first_name = $_SESSION['first_name'];
$last_name = $_SESSION['last_name'];


// This block processes POST requests when user clicks "Remove" button
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['remove_reservation'])) {
    
    // Get ISBN from form and sanitize it
    // mysqli_real_escape_string() prevents SQL injection
    $isbn = mysqli_real_escape_string($conn, $_POST['isbn']);
    
    
    // Ensures both deletion and update succeed together or fail together
    mysqli_begin_transaction($conn);
    
    
    try {
        
        // Prepare SQL statement to remove the specific reservation
        // WHERE clause ensures user can only remove their own reservations
        $delete_stmt = mysqli_prepare($conn, "DELETE FROM reservations WHERE ISBN = ? AND Username = ?");
        
        // Bind both ISBN and username parameters (both strings)
        mysqli_stmt_bind_param($delete_stmt, "ss", $isbn, $username);
        
        // Execute deletion and check for errors
        if (!mysqli_stmt_execute($delete_stmt)) {

            // If deletion fails, throw exception to trigger rollback
            throw new Exception("Failed to remove reservation.");
        }
        
        // Close the delete statement
        mysqli_stmt_close($delete_stmt);
        
        // STEP 2: UPDATE BOOK STATUS to available in books table
        $update_stmt = mysqli_prepare($conn, "UPDATE books SET Reserved = 'N' WHERE ISBN = ?");
        
        // Bind ISBN parameter
        mysqli_stmt_bind_param($update_stmt, "s", $isbn);
        
        // Execute update and check for errors
        if (!mysqli_stmt_execute($update_stmt)) {

            // If update fails, throw exception to trigger rollback
            throw new Exception("Failed to update book status.");
        }
        
        // Close the update statement
        mysqli_stmt_close($update_stmt);
        
        // COMMIT TRANSACTION: Make all changes permanent
        // Both deletion and update succeeded, so save changes to database
        mysqli_commit($conn);
        
        // Store success message in session to display after redirect
        $_SESSION['remove_success'] = "Reservation removed successfully!";
       
        //Executes if any error occurred during removal
    } catch (Exception $e) {
        
        // Undo all changes made during this transaction
        // This ensures database consistency if something failed
        mysqli_rollback($conn);
        
        // Store error message in session to display after redirect
        // getMessage() retrieves the error text from the Exception object
        $_SESSION['remove_error'] = $e->getMessage();
    }
    
    // Redirect back to my_reservations page 
    // This prevents the removal from being resubmitted if user refreshes
    header("Location: my_reservations.php");
    exit();
}

// fetch user's reservation from database
$query = "SELECT r.ISBN, r.ReservedDate, b.BookTitle, 
          b.Author, b.Edition, b.Year, c.CategoryDepartment 
          FROM reservations r 
          INNER JOIN books b ON r.ISBN = b.ISBN 
          LEFT JOIN categories c ON b.Category = c.CategoryID 
          WHERE r.Username = ? 
          ORDER BY r.ReservedDate DESC";



// Prepare the SQL statement
$stmt = mysqli_prepare($conn, $query);

// Bind username parameter to filter by current user
mysqli_stmt_bind_param($stmt, "s", $username);

// Execute the query
mysqli_stmt_execute($stmt);

// Get result set
$result = mysqli_stmt_get_result($stmt);

// Initialize array to store all reservation records
$reservations = [];

// Fetch each reservation as an associative array and add to $reservations
while ($row = mysqli_fetch_assoc($result)) {
    $reservations[] = $row;
}

// Close the prepared statement
mysqli_stmt_close($stmt);


// Initialize variables for displaying feedback to user
$message = '';
$message_type = '';

// Check if there's a success message (reservation was removed)
if (isset($_SESSION['remove_success'])) {
    $message = $_SESSION['remove_success'];
    $message_type = 'success';  // Will display with green styling
    unset($_SESSION['remove_success']);  // Remove from session (one-time display)
} 
// Check if there's an error message (removal failed)
elseif (isset($_SESSION['remove_error'])) {
    $message = $_SESSION['remove_error'];
    $message_type = 'error';  // Will display with red styling
    unset($_SESSION['remove_error']);  // Remove from session (one-time display)
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Reservations - Library System</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="dashboard">
    <nav class="navbar">
        <div class="nav-brand">
            <h1>Library Book Reservation System</h1>
        </div>
        <div class="nav-links">
            <a href="search.php">Search Books</a>
            <a href="logout.php">Logout</a>
        </div>
    </nav>
    
    <div class="greeting-section">
        <p>Welcome, <strong><?php echo htmlspecialchars($first_name . ' ' . $last_name); ?></strong>!</p>
    </div>
    
    <div class="main-content">
        <?php if ($message): ?>
            <div class="<?php echo $message_type; ?>"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <div class="books-table">
            <h2>My Reserved Books (<?php echo count($reservations); ?> reservations)</h2>
            
            <?php if (count($reservations) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ISBN</th>
                            <th>Title</th>
                            <th>Author</th>
                            <th>Category</th>
                            <th>Edition</th>
                            <th>Year</th>
                            <th>Reserved On</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reservations as $reservation): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($reservation['ISBN']); ?></td>
                                <td><?php echo htmlspecialchars($reservation['BookTitle']); ?></td>
                                <td><?php echo htmlspecialchars($reservation['Author']); ?></td>
                                <td><?php echo htmlspecialchars($reservation['CategoryDepartment']); ?></td>
                                <td><?php echo htmlspecialchars($reservation['Edition']); ?></td>
                                <td><?php echo htmlspecialchars($reservation['Year']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($reservation['ReservedDate'])); ?></td>
                                <td>
                                    <form action="my_reservations.php" method="POST" style="display: inline;">
                                        <input type="hidden" name="isbn" value="<?php echo $reservation['ISBN']; ?>">
                                        <button type="submit" name="remove_reservation" class="btn-remove" 
                                                onclick="return confirm('Are you sure you want to remove this reservation?');">
                                            Remove
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>You have no active reservations.</p>
                <p><a href="search.php" style="color: #667eea;">Search for books to reserve</a></p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
<?php
mysqli_close($conn);
?>
