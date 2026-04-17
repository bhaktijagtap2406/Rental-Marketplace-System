# Smart Rental Marketplace

A lightweight, web-based rental platform built using PHP, HTML, CSS, JavaScript, and JSON.
This system allows users to list, browse, and rent items efficiently without using a database.

---

## Features

* User Authentication (Session-Based)

  * Owner, Renter, and Admin roles
* Item Listing

  * Add items with name, category, price, location, and image
* Browse Items

  * Card-based UI for easy viewing
* Booking System

  * Select start and end dates
  * Prevents overlapping bookings
* Dynamic Pricing

  * Automatic total calculation
  * Discount for long-duration rentals
* Booking Management
* Real-Time Availability Check
* JSON-Based Storage (No Database)

---

## Tech Stack

| Layer    | Technology            |
| -------- | --------------------- |
| Frontend | HTML, CSS, JavaScript |
| Backend  | PHP                   |
| Storage  | JSON Files            |
| Server   | XAMPP (Apache)        |

---

## Project Location (Your System)

Current folder:

```
C:\Users\Bhakti\Downloads\rent-a-joy-marketplace-main\rent-a-joy-marketplace-main
```

---
Good — this is what a proper README should say for **any user who downloads your project**.

---

## How to Run the Project

### Option 1: Using PHP Built-in Server (Recommended – Simple)

1. Download or extract the project folder

2. Open Command Prompt / Terminal

3. Navigate to the project folder:

```bash
cd path\to\rent-a-joy-marketplace-main
```

Example:

```bash
cd C:\Users\YourName\Downloads\rent-a-joy-marketplace-main
```

4. Start PHP server:

```bash
php -S localhost:8000
```

(If PHP is not globally installed, use full path like:)

```bash
C:\xampp\php\php.exe -S localhost:8000
```

5. Open browser:

```text
http://localhost:8000
```

---

### Option 2: Using XAMPP (Apache)

1. Copy project folder to:

```text
C:\xampp\htdocs\
```

2. Start Apache from XAMPP

3. Open browser:

```text
http://localhost/rent-a-joy-marketplace-main/
```

---

## Required Folder Structure

```text
rent-a-joy-marketplace-main/
│
├── index.php
├── add_item.php
├── login.php
├── logout.php
├── book.php
├── check_availability.php
├── functions.php
├── style.css
├── script.js
│
├── uploads/
└── data/
    ├── users.json
    ├── items.json
    └── bookings.json
```

---

## Default Login Credentials

| Role   | Username | Password  |
| ------ | -------- | --------- |
| Owner  | owner1   | owner123  |
| Renter | renter1  | renter123 |
| Admin  | admin    | admin123  |

---

## Important Setup Notes

* Make sure these folders exist:

  * `uploads/`
  * `data/`

* Make sure JSON files exist:

```json
[]
```

for:

* items.json
* bookings.json

---

## Common Errors & Fixes

* **Not Found**

  * Server started in wrong folder → navigate correctly

* **include() error**

  * Missing `functions.php` → check file location

* **This site can’t be reached**

  * Server not running

* **Image upload not working**

  * `uploads/` folder missing

---

## Features

* Role-based login (Owner, Renter, Admin)
* Item listing with image upload
* Booking system with date validation
* Real-time availability check
* Dynamic pricing calculation
* JSON-based storage (no database)

---

## Limitations

* Not scalable (uses JSON instead of database)
* Basic authentication (no encryption)
* No payment system
* No real-time notifications

---

## Future Scope

* Add MySQL database
* Add payment gateway
* Improve security
* Add real-time features

---
