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

## Setup Instructions (Important)

You **cannot run directly from Downloads**. Do this:

1. Copy the folder:

```
rent-a-joy-marketplace-main
```

2. Paste it into:

```
C:\xampp\htdocs\
```

Final path should be:

```
C:\xampp\htdocs\rent-a-joy-marketplace-main
```

3. Start XAMPP → Start **Apache**

4. Open browser:

```
http://localhost/rent-a-joy-marketplace-main/
```

---

## Default Login Credentials

| Role   | Username | Password  |
| ------ | -------- | --------- |
| Owner  | owner1   | owner123  |
| Renter | renter1  | renter123 |
| Admin  | admin    | admin123  |

---

## How It Works

1. Login as Owner → Add items
2. Login as Renter → Browse items
3. Select dates → Check availability
4. Book item → Data stored in JSON

---

## Common Errors (Fix Fast)

* **Not Found**

  * Wrong folder path → Must be inside `htdocs`

* **This site can’t be reached**

  * Apache not running

* **include() error**

  * Missing `functions.php` or wrong folder structure

---

## Limitations

* JSON storage (not scalable)
* Basic security
* No payment gateway
* No real-time notifications

---

## Future Improvements

* Payment integration
* Database (MySQL)
* Better authentication
* Mobile app

---

## License

For educational use only.

---

