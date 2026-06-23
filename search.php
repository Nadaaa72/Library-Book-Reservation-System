<?php
// BOOK SEARCH PAGE (Main Dashboard)
// This is the primary page where authenticated users can search for and reserve books

// Start session to access user authentication data and display success/error messages
session_start();

// Include database connection to query books, categories, and reservations
require_once 'db_connection.php';


// Only authenticated users can access the search functionality
if (!isset($_SESSION['username'])) {
    
    header("Location: index.php");
    exit();
}

// RETRIEVE USER INFORMATION from session for personalized greeting
// These values were set during login by login.php
$username = $_SESSION['username'];
$first_name = $_SESSION['first_name'];
$last_name = $_SESSION['last_name'];


// These will store user's search criteria
$search_title = '';       // Book title search term
$search_author = '';      // Author name search term
$search_category = '';    // Selected category ID
$books = [];              // Array to store search results


// Display 5 books per page for optimal user experience
$books_per_page = 5;

//checks if page number exists in URL
// intval() converts the parameter to integer to prevent injection
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;

// calculates where to start fetching results from the database for the current page of your pagination.
$offset = ($current_page - 1) * $books_per_page;

// LOAD CATEGORIES for dropdown menu
// Query all categories from database, sorted alphabetically by department name
$categories_query = "SELECT CategoryID, CategoryDepartment FROM categories ORDER BY CategoryDepartment";

$categories_result = mysqli_query($conn, $categories_query);

//Check if user submitted a search or just viewing all books
// If any search parameters are present in URL, perform filtered search
if ($_SERVER["REQUEST_METHOD"] == "GET" && (isset($_GET['title']) || isset($_GET['author']) || isset($_GET['category']))) {
    
    // retrieve search parameters from URL query string
    $search_title = isset($_GET['title']) ? trim($_GET['title']) : '';
    $search_author = isset($_GET['author']) ? trim($_GET['author']) : '';
    $search_category = isset($_GET['category']) ? $_GET['category'] : '';
    
    
    $query = "SELECT b.ISBN, b.BookTitle, b.Author, b.Edition, b.Year, 
              b.Reserved, b.Category, c.CategoryDepartment 
              FROM books b 
              LEFT JOIN categories c ON b.Category = c.CategoryID 
              WHERE 1=1";
    
    // Initialize arrays to store parameters for prepared statement
    $params = [];    
    $types = "";     
    
    // CONDITIONALLY ADD SEARCH FILTERS based on what user entered
    
    // This ensures query is flexible and only filters what the user wants.
    if (!empty($search_title)) {
        $query .= " AND b.BookTitle LIKE ?"; //only gets books where the title matches the user's search
        $params[] = "%" . $search_title . "%";  //matches any title that contains the word
        $types .= "s";  // s = string type
    }
    
    // Add author filter if user entered an author name
    if (!empty($search_author)) {
        $query .= " AND b.Author LIKE ?";
        $params[] = "%" . $search_author . "%";  // Add wildcards for partial match
        $types .= "s";  // s = string type
    }
    
    // Add category filter if user selected a category from dropdown
    if (!empty($search_category)) {
        $query .= " AND b.Category = ?";
        $params[] = $search_category;
        $types .= "i";  // i = integer type
    }
    
    // COUNT TOTAL MATCHING BOOKS (for pagination calculation)
    // This query counts results without fetching actual data, which is more efficient
    $count_query = "SELECT COUNT(*) as total FROM books b WHERE 1=1";
    
    // Apply same search filters to the count query
    // mysqli_real_escape_string() prevents SQL injection in this dynamic query
    if (!empty($search_title)) {
        $count_query .= " AND b.BookTitle LIKE '" . mysqli_real_escape_string($conn, "%" . $search_title . "%") . "'";
    }
    if (!empty($search_author)) {
        $count_query .= " AND b.Author LIKE '" . mysqli_real_escape_string($conn, "%" . $search_author . "%") . "'";
    }
    if (!empty($search_category)) {
        // intval() ensures the category ID is an integer, preventing injection
        $count_query .= " AND b.Category = " . intval($search_category);
    }
    
    // Execute count query
    $count_result = mysqli_query($conn, $count_query);
    $total_books = mysqli_fetch_assoc($count_result)['total'];
    
    // runs a query to count rows, fetches the result as an associative array,
    // and then grabs the value of the total column, storing it in $total_books
    $total_pages = ceil($total_books / $books_per_page);
    
    
    // ORDER BY BookTitle ensures consistent alphabetical ordering
    // LIMIT restricts results to books_per_page (5)
    // OFFSET skips previous pages' results
    $query .= " ORDER BY b.BookTitle LIMIT ? OFFSET ?";
    
    // STEP 2: EXECUTE MAIN QUERY with prepared statement
    $stmt = mysqli_prepare($conn, $query);
    
    // Bind parameters based on whether search filters were applied
    if (!empty($params)) {
        // If filters exist, add LIMIT and OFFSET to existing parameters
        $types .= "ii";  // Two integers for LIMIT and OFFSET
        $params[] = $books_per_page;
        $params[] = $offset;
        // ... (spread operator) unpacks array into individual arguments
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    } else {
        // If no filters, only bind LIMIT and OFFSET
        mysqli_stmt_bind_param($stmt, "ii", $books_per_page, $offset);
    }
    
    // Execute the prepared statement
    mysqli_stmt_execute($stmt);
    
    // Get result set from executed query
    $result = mysqli_stmt_get_result($stmt);
    
    // Fetch all matching books into the $books array
    // Each row is added as an associative array
    while ($row = mysqli_fetch_assoc($result)) {
        $books[] = $row;
    }
    
    // Close the prepared statement to free resources
    mysqli_stmt_close($stmt);
} else {
    // NO SEARCH PERFORMED: Display all books with pagination
    // This executes when user first visits the page or clears search
    
    // Count total books in database
    $count_query = "SELECT COUNT(*) as total FROM books";
    $count_result = mysqli_query($conn, $count_query);
    $total_books = mysqli_fetch_assoc($count_result)['total'];
    
    // Calculate total pages needed for all books
    $total_pages = ceil($total_books / $books_per_page);
    
    // Build query to get all books (no WHERE clause, but still with pagination)
    $query = "SELECT b.ISBN, b.BookTitle, b.Author, b.Edition, b.Year, 
              b.Reserved, b.Category, c.CategoryDepartment 
              FROM books b 
              LEFT JOIN categories c ON b.Category = c.CategoryID 
              ORDER BY b.BookTitle
              LIMIT ? OFFSET ?";
    
    // Prepare and execute query with only pagination parameters
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ii", $books_per_page, $offset);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    // Fetch all books for current page
    while ($row = mysqli_fetch_assoc($result)) {
        $books[] = $row;
    }
    
    // Close statement
    mysqli_stmt_close($stmt);
}

// MESSAGE HANDLING: Check for reservation success/error messages from reserve_book.php
// Initialize variables for displaying feedback to user
$message = '';
$message_type = '';

// Check if there's a success message (book was reserved successfully)
if (isset($_SESSION['reservation_success'])) {
    $message = $_SESSION['reservation_success'];
    $message_type = 'success';  // Will display with green styling
    unset($_SESSION['reservation_success']);  // Remove from session (one-time display)
} 
// Check if there's an error message (reservation failed)
elseif (isset($_SESSION['reservation_error'])) {
    $message = $_SESSION['reservation_error'];
    $message_type = 'error';  // Will display with red styling
    unset($_SESSION['reservation_error']);  // Remove from session (one-time display)
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Books - Library System</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="dashboard">
    <nav class="navbar">
        <div class="nav-brand">
            <h1>Library Book Reservation System</h1>
        </div>
        <div class="nav-links">
            <a href="my_reservations.php">My Reservations</a>
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
        
        <div class="search-box">
            <h2>Search for Books</h2>
            <form action="search.php" method="GET" class="search-form">
                <input type="text" name="title" placeholder="Book Title" 
                       value="<?php echo htmlspecialchars($search_title); ?>">
                
                <input type="text" name="author" placeholder="Author Name" 
                       value="<?php echo htmlspecialchars($search_author); ?>">
                
                <select name="category">
                    <option value="">All Categories</option>
                    <?php 
                    mysqli_data_seek($categories_result, 0);
                    while ($cat = mysqli_fetch_assoc($categories_result)): 
                    ?>
                        <option value="<?php echo $cat['CategoryID']; ?>" 
                                <?php echo ($search_category == $cat['CategoryID']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['CategoryDepartment']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                
                <button type="submit">Search</button>
            </form>
        </div>
        
        <div class="books-table">
            <h2>Available Books (<?php echo $total_books; ?> total - Page <?php echo $current_page; ?> of <?php echo $total_pages; ?>)</h2>
            
            <?php if (count($books) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ISBN</th>
                            <th>Title</th>
                            <th>Author</th>
                            <th>Category</th>
                            <th>Edition</th>
                            <th>Year</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($books as $book): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($book['ISBN']); ?></td>
                                <td><?php echo htmlspecialchars($book['BookTitle']); ?></td>
                                <td><?php echo htmlspecialchars($book['Author']); ?></td>
                                <td><?php echo htmlspecialchars($book['CategoryDepartment']); ?></td>
                                <td><?php echo htmlspecialchars($book['Edition']); ?></td>
                                <td><?php echo htmlspecialchars($book['Year']); ?></td>
                                <td>
                                    <?php if ($book['Reserved'] == 'Y'): ?>
                                        <span style="color: #dc3545;">Reserved</span>
                                    <?php else: ?>
                                        <span style="color: #28a745;">Available</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($book['Reserved'] == 'Y'): ?>
                                        <button class="btn-reserved" disabled>Reserved</button>
                                    <?php else: ?>
                                        <form action="reserve_book.php" method="POST" style="display: inline;">
                                            <input type="hidden" name="isbn" value="<?php echo $book['ISBN']; ?>">
                                            <button type="submit" class="btn-reserve">Reserve</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php
                        // Build query string for pagination links
                        $query_params = [];
                        if (!empty($search_title)) $query_params['title'] = $search_title;
                        if (!empty($search_author)) $query_params['author'] = $search_author;
                        if (!empty($search_category)) $query_params['category'] = $search_category;

                        //converts an associative array into a URL-encoded query string
                        $query_string = http_build_query($query_params);

                        //& is used to separate multiple query parameters
                        $separator = !empty($query_string) ? '&' : '';
                        ?> 

                        <?php if ($current_page > 1): ?>
                            <a href="?<?php echo $query_string . $separator; ?>page=1" class="page-link">« First</a>
                            <a href="?<?php echo $query_string . $separator; ?>page=<?php echo $current_page - 1; ?>" class="page-link">‹ Previous</a>
                        <?php endif; ?>
                        
                        <!-- Display the current page number and total number of pages -->
                        <span class="page-info">Page <?php echo $current_page; ?> of <?php echo $total_pages; ?></span>
                        
                        <!--Check if the current page is less than the total pages, to show "Next" and "Last" links only when not on the last page-->
                        <?php if ($current_page < $total_pages): ?>

                            <!-- Link to the next page -->
                            <a href="?<?php echo $query_string . $separator; ?>page=<?php echo $current_page + 1; ?>" class="page-link">Next ›</a>

                            <!-- Link to the last page -->
                            <a href="?<?php echo $query_string . $separator; ?>page=<?php echo $total_pages; ?>" class="page-link">Last »</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <p>No books found matching your search criteria.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
<?php
mysqli_close($conn);
?>
