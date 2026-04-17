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

  * View items in a card-based UI
* Booking System

  * Select start and end dates
  * Prevents overlapping bookings
* Dynamic Pricing

  * Automatic total calculation
  * Discount for long-duration rentals
* Booking Management

  * Stores booking history
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

## Project Structure

```text
rental_pro/
│
├── index.php
├── login.php
├── logout.php
├── add_item.php
├── dashboard.php
├── history.php
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

## Setup Instructions

1. Install XAMPP
2. Move the project folder to:
   C:\xampp\htdocs\rental_pro
3. Start Apache from XAMPP
4. Open browser and go to:
   [http://localhost/rental_pro/index.php](http://localhost/rental_pro/index.php)

---

## How It Works

1. Login as Owner to add items
2. Login as Renter to browse items
3. Select dates to check availability
4. Book item and data is stored in JSON files

---

## Limitations

* Uses JSON instead of a database, so not scalable for large data
* Basic authentication without encryption
* No payment gateway integration
* No real-time notifications

---

## Future Improvements

* Payment gateway integration
* Cloud database integration (MySQL or Firebase)
* Improved security with encryption
* Real-time chat system
* Mobile application version

---

## License

This project is for educational purposes only.
