# ExpenseTracker - Daily Expense Tracking Application

## Tech Stack
- **Backend:** PHP 8.x, MySQL
- **Frontend:** Bootstrap 5.3, jQuery 3.7, Chart.js
- **Features:** AJAX CRUD, Email OTP Validation, Sessions, Cookies

## Setup Instructions

### 1. Database Setup
1. Import `database.sql` into MySQL:
   ```
   mysql -u root -p < database.sql
   ```

### 2. Configuration
1. Edit `config/database.php` with your MySQL credentials
2. For email OTP to work, configure PHP `mail()` or use SMTP (e.g., PHPMailer)

### 3. Run
1. Place files in your web server root (e.g., `htdocs` for XAMPP)
2. Navigate to `http://localhost/expense-tracker/`

## Features
- ✅ User Registration with Email OTP Verification
- ✅ Login/Logout with Sessions & Remember Me (Cookies)
- ✅ Password Reset via OTP
- ✅ Dashboard with Stats & Charts
- ✅ Add/Edit/Delete Expenses (AJAX - no page reload)
- ✅ Filter by Date Range & Category
- ✅ Reports with Monthly Charts & Category Breakdown
- ✅ Profile Settings & Password Change
- ✅ Responsive Bootstrap UI
- ✅ Client-side Email Validation

## Folder Structure
```
expense-tracker/
├── ajax/                 # AJAX endpoints
│   ├── add_expense.php
│   ├── update_expense.php
│   └── delete_expense.php
├── assets/
│   ├── css/style.css     # Custom styles
│   └── js/app.js         # jQuery/AJAX logic
├── config/
│   └── database.php      # DB configuration
├── includes/
│   ├── header.php        # Navbar & head
│   ├── footer.php        # Footer & scripts
│   └── session.php       # Session & cookie helpers
├── database.sql          # SQL schema
├── index.php             # Dashboard
├── login.php             # Login page
├── register.php          # Registration page
├── verify-otp.php        # OTP verification
├── forgot-password.php   # Forgot password
├── reset-password.php    # Reset password (OTP + new password)
├── expenses.php          # Expenses list with filters
├── reports.php           # Yearly reports & charts
├── profile.php           # Profile & password settings
└── logout.php            # Logout handler
```
