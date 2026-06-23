# Library Book Reservation System


## Database Structure

### Users Table
- `Username` (Primary Key) - Unique username
- `Password` - Hashed password
- `FirstName` - User's first name
- `Surname` - User's surname/last name
- `AddressLine` - Address line 1
- `AddressLineTown` - Address line 2/town
- `City` - City
- `Telephone` - Telephone number
- `Mobile` - Mobile number (10 digits, required)

### Categories Table
- `CategoryID` (Primary Key) - Category ID number
- `CategoryDepartment` - Category/department name

### Books Table
- `ISBN` (Primary Key) - Book ISBN number
- `BookTitle` - Title of the book
- `Author` - Author name
- `Edition` - Edition number
- `Year` - Publication year
- `Category` - Foreign key to CategoryID
- `Reserved` - 'Y' for reserved, 'N' for available

### Reservations Table
- `ISBN` (Foreign Key) - Book ISBN
- `Username` (Foreign Key) - Username who reserved
- `ReservedDate` - Date of reservation

## Features

### User Authentication
- **Registration**: New users can register with required fields
  - Username (unique)
  - Password (minimum 6 characters with confirmation)
  - First Name & Surname
  - Mobile Number (exactly 10 numeric digits)
- **Login**: Existing users login with username and password
- **Session Management**: Secure session handling throughout
- **Logout**: Clean session termination

### Book Search
- Search by book title (supports partial matching)
- Search by author name (supports partial matching)
- Filter by category using dropdown menu (populated from database)
- Combined search using multiple criteria
- View all available books when no search criteria entered

### Book Reservation
- Reserve available books (Reserved = 'N')
- Prevents reserving already reserved books (Reserved = 'Y')
- Automatically captures current date as reservation date
- Transaction-based operations for data integrity
- Updates book status to 'Y' when reserved

### My Reservations
- View all books reserved by logged-in user
- Display reservation details with dates
- Remove reservations
- Updates book status back to 'N' when reservation removed
- Real-time availability updates

## Requirements

- XAMPP (or similar LAMP/WAMP stack)
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web browser (Chrome, Firefox, Safari, Edge)
- **Existing database 'ca' with the structure described above**

## Installation & Setup

### Step 1: Verify Database Configuration

The database connection is configured in `db_connection.php`:
```php
$host = "localhost";
$user = "root";
$pass = "";
$db = "ca";  // my database name
```

Make sure MySQL credentials match these settings.

### Step 2: Start XAMPP Services
1. Open XAMPP Control Panel
2. Start **Apache** server
3. Start **MySQL** server

### Step 3: Verify Database Tables

Open phpMyAdmin (`http://localhost/phpmyadmin`) and verify that the **ca** database contains:
- users table with columns: Username, Password, FirstName, Surname, Mobile, etc.
- categories table with columns: CategoryID, CategoryDepartment
- books table with columns: ISBN, BookTitle, Author, Edition, Year, Category, Reserved
- reservations table with columns: ISBN, Username, ReservedDate

### Step 4: Access the Application

Navigate to: `http://localhost/WebD/assignment/index.php`

The login and registration page should appear.

## File Structure

```
assignment/
│
├── index.php                     # Login and registration page
├── login.php                     # Login handler (updated for ca database)
├── register.php                  # Registration handler (updated for ca database)
├── logout.php                    # Logout handler
├── search.php                    # Book search page (updated for ca database)
├── reserve_book.php              # Reservation handler (updated for ca database)
├── my_reservations.php           # User reservations page (updated for ca database)
├── db_connection.php             # Database connection (configured for 'ca')
├── styles.css                    # CSS styling
├── database_schema_reference.sql # Reference documentation for the database
├── UPDATED_README.md             # This file
└── README.md                     # Original README (for reference)
```

## Key Changes Made

All PHP files have been updated to match your database schema:

1. **Column names use CamelCase** instead of snake_case
   - `FirstName` instead of `first_name`
   - `BookTitle` instead of `title`
   - `CategoryDepartment` instead of `category_name`

2. **Reserved status uses 'Y'/'N'** instead of 1/0
   - Checks for `Reserved = 'Y'` for reserved books
   - Sets to `'N'` for available books

3. **Foreign keys use actual values** instead of IDs
   - Reservations use `ISBN` and `Username` directly
   - No separate reservation_id or user_id

4. **Session management** uses Username instead of user_id
   - Session stores: `username`, `first_name`, `last_name`

## Usage Guide

### Register a New Account

1. Go to the index page
2. Fill in the registration form on the right:
   - Choose a unique username
   - Enter your first name and surname
   - Enter a 10-digit mobile number (numeric only)
   - Create a password (minimum 6 characters)
   - Confirm your password
3. Click **Register**
4. Upon success, you can login

### Login

1. Enter your username and password in the left form
2. Click **Login**
3. You'll be redirected to the search page

### Search for Books

1. Use any combination of search criteria:
   - **Book Title**: Enter full or partial title
   - **Author**: Enter full or partial author name
   - **Category**: Select from dropdown (populated from your categories table)
2. Click **Search**
3. Results will display matching books
4. Leave all fields empty to see all books

### Reserve a Book

1. Find an available book (Status: Available, Reserved = 'N')
2. Click the **Reserve** button
3. The book will be marked as reserved (Reserved = 'Y')
4. Reservation date is automatically recorded
5. Success message will confirm the reservation

### Manage Your Reservations

1. Click **My Reservations** in the navigation
2. View all your reserved books with dates
3. To remove a reservation:
   - Click **Remove** button
   - Confirm the action
   - Book becomes available again (Reserved = 'N')

## Security Features

### Server-Side Validation
- All form inputs validated on server
- Mobile number: exactly 10 numeric digits
- Password: minimum 6 characters
- Password confirmation must match
- Username uniqueness check against database

### Data Security
- Password hashing using `password_hash()` (bcrypt)
- Input sanitization with `trim()` and `mysqli_real_escape_string()`
- Prepared statements to prevent SQL injection
- `htmlspecialchars()` to prevent XSS attacks
- Transaction-based operations for data integrity

### Session Security
- Session-based authentication
- Login required for all protected pages
- Automatic redirect for unauthorized access
- Proper session cleanup on logout

## Testing Checklist

### Registration Testing
- [ ] Register with valid data - should succeed
- [ ] Try duplicate username - should fail with error
- [ ] Try invalid mobile (not 10 digits) - should fail
- [ ] Try invalid mobile (contains letters) - should fail
- [ ] Try password mismatch - should fail
- [ ] Try password less than 6 chars - should fail

### Login Testing
- [ ] Login with correct credentials - should succeed
- [ ] Login with wrong password - should fail
- [ ] Login with non-existent username - should fail

### Search Testing
- [ ] Search by partial title - should find matching books
- [ ] Search by author name - should find books by that author
- [ ] Filter by category - should show only books in that category
- [ ] Combine filters - should apply all criteria
- [ ] Leave all empty - should show all books

### Reservation Testing
- [ ] Reserve an available book (Reserved='N') - should succeed
- [ ] Try to reserve a reserved book (Reserved='Y') - should be disabled
- [ ] View your reservations - should display the book
- [ ] Remove a reservation - book should become available again
- [ ] Verify database updates (Reserved column changes)

## Troubleshooting

### "Connection failed" Error
- Verify MySQL is running in XAMPP
- Check credentials in `db_connection.php`
- Ensure database 'ca' exists

### Books Not Showing
- Verify books table has data
- Check that books table has all required columns
- Verify column names match: ISBN, BookTitle, Author, etc.

### Categories Dropdown Empty
- Check categories table has data
- Verify columns: CategoryID, CategoryDepartment

### Reservation Not Working
- Verify reservations table exists
- Check columns: ISBN, Username, ReservedDate
- Verify Reserved column in books table is CHAR(1)

### Login/Registration Issues
- Check users table structure
- Verify columns: Username, Password, FirstName, Surname, Mobile
- Check if Username is the primary key

## Database Queries Reference

The application uses these main queries:

**Search Books:**
```sql
SELECT b.ISBN, b.BookTitle, b.Author, b.Edition, b.Year, 
       b.Reserved, b.Category, c.CategoryDepartment 
FROM books b 
LEFT JOIN categories c ON b.Category = c.CategoryID 
WHERE b.BookTitle LIKE '%search%' 
ORDER BY b.BookTitle
```

**Reserve Book:**
```sql
INSERT INTO reservations (Username, ISBN, ReservedDate) VALUES (?, ?, ?);
UPDATE books SET Reserved = 'Y' WHERE ISBN = ?;
```

**Remove Reservation:**
```sql
DELETE FROM reservations WHERE ISBN = ? AND Username = ?;
UPDATE books SET Reserved = 'N' WHERE ISBN = ?;
```

