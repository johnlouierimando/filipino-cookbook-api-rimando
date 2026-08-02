# Filipino Cookbook API

A RESTful API for Filipino cuisine built with **PHP**, the **Slim Framework**, and **MySQL**. This API provides structured data about traditional Filipino foods, their ingredients, categories, and regional origins — secured with Bearer token authentication.

---

## Table of Contents

1. [API Description](#api-description)
2. [Features](#features)
3. [Technologies Used](#technologies-used)
4. [Project Structure](#project-structure)
5. [Installation Instructions](#installation-instructions)
6. [Database Setup](#database-setup)
7. [Base URL](#base-url)
8. [Authentication](#authentication)
9. [API Endpoints](#api-endpoints)
10. [HTTP Status Codes](#http-status-codes)
11. [Testing Evidence](#testing-evidence)
12. [Developer Information](#developer-information)
13. [Optional API Enhancements](#optional-api-enhancements)

---

## API Description

The **Filipino Cookbook API** is a token-secured REST API that exposes a curated collection of traditional Filipino food data. It is designed for developers, students, and researchers who want programmatic access to Filipino culinary information.

- **Purpose:** Provide structured, queryable data about Filipino foods, their categories, regional origins, and ingredients.
- **Type of information:** Food names, cooking instructions, categories (e.g., Main Dish, Soup, Dessert), regional origins (e.g., Bicol, Ilocos, Bacolod), and ingredient lists.
- **Intended users:** Students, web developers, and researchers building applications that feature Filipino cuisine.
- **Main functions:** Retrieve all foods, retrieve a specific food by ID, search foods by name, retrieve categories, retrieve ingredients, add new food records, and delete food records.
- **Technologies used:** PHP, Slim Framework 4, MySQL, Composer, Apache (XAMPP).

---

## Features

- Retrieve all Filipino foods with full ingredient lists
- Retrieve details of a specific food by ID
- Search for foods by name (partial match)
- Retrieve all food categories
- Retrieve all ingredients
- Add a new Filipino food record (POST)
- Delete a food record (DELETE)
- Bearer token authentication on all `/api` endpoints
- **Per-IP Rate Limiting** — 10 requests per 30 seconds per client IP (returns `429 Too Many Requests` when exceeded)
- JSON responses with appropriate HTTP status codes
- Prepared SQL statements (protection against SQL injection)
- Relational MySQL database (many-to-many food-ingredient relationship)

---

## Technologies Used

| Tool / Technology       | Purpose                              |
|-------------------------|--------------------------------------|
| PHP (>= 7.2)            | Server-side scripting language       |
| Slim Framework 4        | Micro-framework for routing          |
| MySQL                   | Relational database management       |
| Composer                | PHP dependency manager               |
| JSON                    | API response format                  |
| Apache                  | Web server (via XAMPP)               |
| XAMPP                   | Local development environment        |
| Thunder Client / Postman| API endpoint testing                 |
| Git                     | Version control                      |
| GitHub                  | Remote repository hosting            |

---

## Project Structure

```
filipino-cookbook-api/
├── database/
│   └── filipino_foods_relational.sql   # SQL schema and seed data
├── public/
│   ├── .htaccess                        # Apache URL rewriting rules
│   └── index.php                        # Application entry point and all routes
├── vendor/                              # Composer dependencies (not committed)
├── .gitignore                           # Git ignore rules
├── composer.json                        # Composer configuration
├── composer.lock                        # Locked dependency versions
├── config.example.php                   # Example configuration (safe to commit)
├── API_DOCUMENTATION.md                 # Full API reference documentation
└── README.md                            # This file
```

---

## Installation Instructions

### Prerequisites

- [XAMPP](https://www.apachefriends.org/) (Apache + MySQL + PHP >= 7.2)
- [Composer](https://getcomposer.org/)
- [Git](https://git-scm.com/)
- Thunder Client (VS Code extension) or [Postman](https://www.postman.com/)

### Step 1 - Clone the Repository

```bash
git clone https://github.com/username/filipino-cookbook-api-surname.git
cd filipino-cookbook-api-surname
```

### Step 2 - Place in XAMPP Directory

Copy or move the project folder into your XAMPP `htdocs` directory:

```
C:\xampp\htdocs\filipino-cookbook-api\
```

### Step 3 - Install Dependencies

```bash
composer install
```

### Step 4 - Create and Import the Database

1. Open **phpMyAdmin** at `http://localhost/phpmyadmin`
2. Click **Import**
3. Select the file: `database/filipino_foods_relational.sql`
4. Click **Go**

Alternatively, use the MySQL command line:

```bash
mysql -u root -p < database/filipino_foods_relational.sql
```

### Step 5 - Configure the Database Connection

Open `public/index.php` and update the configuration constants at the top of the file:

```php
define('API_TOKEN', 'your-secret-token');

define('DB_HOST', 'localhost');
define('DB_NAME', 'filipino_cookbook_api');
define('DB_USER', 'root');
define('DB_PASS', '');
```

> **Note:** For production use, see `config.example.php` and store credentials in a separate configuration file excluded from version control.

### Step 6 - Start Apache and MySQL

Open the **XAMPP Control Panel** and start both **Apache** and **MySQL**.

### Step 7 - Test the API

Open Thunder Client, Postman, or your browser and navigate to:

```
http://localhost/filipino-cookbook-api/public/
```

You should receive:

```json
{
  "message": "Welcome to the Secured Filipino Cookbook API",
  "note": "Use a valid Bearer token to access /api endpoints."
}
```

---

## Database Setup

- **Database name:** `filipino_cookbook_api`
- **SQL file:** `database/filipino_foods_relational.sql`

### Tables

| Table             | Description                                                |
|-------------------|------------------------------------------------------------|
| `categories`      | Food categories (e.g., Main Dish, Soup, Dessert)           |
| `origins`         | Regional origins (e.g., Philippines, Bicol Region)         |
| `foods`           | Food records with name, category, origin, and instructions |
| `ingredients`     | Individual ingredient names                                |
| `food_ingredients`| Junction table linking foods to ingredients (many-to-many) |

### Table Relationships

```
categories ──────┐
                 v
origins ─────► foods ◄──── food_ingredients ◄──── ingredients
```

- `categories → foods` (one category has many foods)
- `origins → foods` (one origin has many foods)
- `foods <-> ingredients` via `food_ingredients` (many-to-many)

---

## Base URL

```
http://localhost/filipino-cookbook-api/public
```

All API endpoints are prefixed with `/api`:

```
http://localhost/filipino-cookbook-api/public/api
```

---

## Authentication

This API uses **Bearer Token** authentication. All `/api` endpoints require a valid token in the `Authorization` header.

### Required Header

```
Authorization: Bearer dmmmsu-cookbook-token-2026
```

### Unauthorized Response (HTTP 401)

```json
{
  "status": "error",
  "message": "Unauthorized access. Valid API token is required."
}
```

---

## API Endpoints

For full endpoint documentation with request/response examples, see [API_DOCUMENTATION.md](API_DOCUMENTATION.md).

### Quick Reference

| Method | Endpoint                      | Description                  | Auth Required |
|--------|-------------------------------|------------------------------|---------------|
| GET    | `/`                           | Welcome message              | No            |
| GET    | `/api/foods`                  | Get all foods with ingredients | Yes         |
| GET    | `/api/foods/{id}`             | Get one food by ID           | Yes           |
| GET    | `/api/foods/search/{name}`    | Search foods by name         | Yes           |
| GET    | `/api/categories`             | Get all categories           | Yes           |
| GET    | `/api/ingredients`            | Get all ingredients          | Yes           |
| POST   | `/api/foods`                  | Add a new food record        | Yes           |
| DELETE | `/api/foods/{id}`             | Delete a food record         | Yes           |

---

## HTTP Status Codes

| Status Code | Meaning                            |
|-------------|------------------------------------|
| 200         | Request completed successfully     |
| 201         | Resource created successfully      |
| 400         | Invalid request or missing parameter |
| 401         | Missing or invalid authentication  |
| 403         | Access is not allowed              |
| 404         | Requested resource was not found   |
| 429         | Too many requests                  |
| 500         | Internal server error              |

---

## Testing Evidence

All endpoints were tested using **Thunder Client** in Visual Studio Code with Bearer token authentication.

---

### 1. GET `/` Public Welcome Endpoint

![GET / — Welcome message returned with HTTP 200 OK](screenshots/GET_PUBLIC-WELCOME.png)

*Public welcome route returns a JSON message confirming the API is running. No token required.*

---

### 2. GET `/api/foods` Retrieve All Foods

![GET /api/foods — All foods returned with ingredients and HTTP 200 OK](screenshots/GET_FOODS.png)

*Returns the full list of Filipino foods with category, origin, instructions, and ingredients. Bearer token required.*

---

### 3. GET `/api/foods/{id}` Retrieve Food by ID

![GET /api/foods/{id} — Single food record returned with HTTP 200 OK](screenshots/GET_FOOD-ID.png)

*Returns the full details and ingredient list for a specific food identified by its ID.*

---

### 4. GET `/api/foods/search/{name}` Search Foods by Name

![GET /api/foods/search/adobo — Search results returned with HTTP 200 OK](screenshots/GET_SEARCH-ADOBO.png)

*Partial-match search returns all foods whose name contains the search keyword.*

---

### 5. GET `/api/categories` Retrieve All Categories

![GET /api/categories — All categories returned with HTTP 200 OK](screenshots/GET_CATEGORIES.png)

*Returns all food categories stored in the database.*

---

### 6. GET `/api/ingredients` Retrieve All Ingredients

![GET /api/ingredients — All ingredients returned with HTTP 200 OK](screenshots/GET_INGREDIENTS.png)

*Returns the complete alphabetically sorted list of all ingredients.*

---

### 7. POST `/api/foods` Add New Food

![POST /api/foods — New food record created with HTTP 201 Created](screenshots/POST_NEW-FOOD.png)

*Creates a new food record. Requires a JSON body with food_name, category_id, origin_id, and instructions.*

---

### 8. DELETE `/api/foods/{id}` Delete Food by ID

![DELETE /api/foods/{id} — Food deleted successfully with HTTP 200 OK](screenshots/DELETE_FOOD-BY-ID.png)

*Permanently removes a food record and its associated ingredient links from the database.*

---

## Developer Information

| Field            | Details                                                             |
|------------------|---------------------------------------------------------------------|
| Student Name     | John Louie E. Rimando                                               |
| Course & Section | BS Information Technology 4A                                        |
| GitHub Username  | johnlouierimando                                                    |
| Repository Link  | https://github.com/johnlouierimando/filipino-cookbook-api-rimando   |
| Date Completed   | [Date]                                                              |

---

## Optional API Enhancements

This API includes the following enhancement beyond the base requirements:

### Search Endpoint

**Endpoint:** `GET /api/foods/search/{name}`

- **Description:** Performs a partial-match search on food names using SQL `LIKE`.
- **Purpose:** Allows users to find foods without knowing the exact full name.
- **Files Modified:** `public/index.php`
- **Endpoint Added:** `GET /api/foods/search/{name}`
- **Security:** Uses prepared statements to prevent SQL injection.
- **Testing:** Send a GET request with a food name keyword (e.g., `/api/foods/search/adobo`).

---

### 2. Security Feature — Per-IP Rate Limiting

**Description:** All `/api/*` routes are now protected by a sliding-window rate limiter — each client IP is limited to **10 requests per 30 seconds**. Requests over the limit receive `429 Too Many Requests` instead of being processed.

**Purpose:** Reduces the risk of brute-force token guessing and protects the database from being overwhelmed by a runaway client or script.

**Files modified:** `public/index.php` (added `isRateLimited()` function and `$rateLimitMiddleware`, attached to all `/api` routes)

**Implementation notes:** The limiter stores a short list of recent request timestamps per IP in `storage/rate_limit/`, using file locking (`flock`) so concurrent requests don't corrupt the count. This keeps state across requests without needing a database table or an external cache service — appropriate for a single-server student project.

**Rate limit response (HTTP 429):**
```json
{
  "status": "error",
  "message": "Too many requests. Please wait a moment and try again."
}
```

**Screenshot 429 Too Many Requests:**

![Rate limiter triggered — HTTP 429 Too Many Requests response in Thunder Client](screenshots/RATE_LIMITER.png)

**Testing instructions:**
1. Send 10 requests to any `/api/*` route (e.g., `/api/categories`) within 30 seconds with a valid token — all should return `200 OK`.
2. Send an 11th request within the same 30-second window — expect `429 Too Many Requests`.
3. Wait 30 seconds and try again — requests should succeed again.

---

*Filipino Cookbook API - Built using PHP + Slim Framework*
