# 📚 BookLeaf Publishing — Author Support & Communication Portal

An enterprise-grade, dual-role author support and operational ticket management system engineered with **Laravel**, **MongoDB**, and **Alpine.js**. The platform isolates author interactions to their own catalogue and royalty statements while granting operational administrators comprehensive oversight of queue triaging, automated classification, and dispatch workflows.

---

## 📑 Table of Contents
- [Architecture & System Overview](#-architecture--system-overview)
- [Key Features](#-key-features)
- [Tech Stack](#-tech-stack)
- [Database Schema (MongoDB)](#-database-schema-mongodb)
- [Role-Based Access Control (RBAC)](#-role-based-access-control-rbac)
- [Local Installation & Setup](#-local-installation--setup)
- [REST API Specification](#-rest-api-specification)
- [Standard Error Handling](#-standard-error-handling)
- [Test Credentials](#-test-credentials)

---

## 🏛️ Architecture & System Overview

BookLeaf relies on a decoupled reactive frontend supported by a session-authenticated REST API:

```text
┌───────────────────────────────────────────────────────────┐
│                    Single-Page Portal                     │
│         (Blade Views + Alpine.js State Store)             │
└─────────────┬───────────────────────────────┬─────────────┘
              │                               │
       Author Context                   Admin Context
     (Isolated Scoping)             (Global Queue Triage)
              │                               │
              ▼                               ▼
    GET /api/author/*               GET /api/admin/*
    POST /api/author/tickets        POST /api/admin/tickets/*
              │                               │
              └───────────────┬───────────────┘
                              │
                    ┌─────────▼─────────┐
                    │  Laravel REST API │
                    │ (RoleMiddleware)  │
                    └─────────┬─────────┘
                              │
                    ┌─────────▼─────────┐
                    │ MongoDB Database  │
                    │ (users, tickets,  │
                    │  authors, books)  │
                    └───────────────────┘
```

---

## 🌟 Key Features

### 1. Dual-Role Architecture
- **Author Portal**: Authors access isolated dashboards displaying published titles, copies sold across Amazon/Flipkart/BookLeaf, quarterly earned vs. pending royalties, and historical support threads.
- **Operations Desk (Admin)**: Full operational control across author master directories, company-wide distribution analytics, and live ticket triage.

### 2. Intelligent Ticket Triage & Queue
- **AI Classification**: Automated issue detection categorizing tickets into `Royalty & Payments`, `ISBN & Metadata Issues`, `Printing & Quality`, `Distribution & Availability`, and `General Inquiry`.
- **Urgency Scoring & Smart Sorting**: Dynamic queue sorting prioritizing unresolved tickets by weight (`Critical > High > Medium > Low`), followed by FIFO creation timestamp.
- **Self-Assignment ("Claim Ticket")**: Admins can claim unassigned tickets directly from the workspace modal, updating live state and displaying owner badges across the queue.
- **Policy Calibration**: Workspace includes rapid draft regeneration calibrated against BookLeaf policies (80/20 royalty split, 45-day payment cycle, and ₹1,000 disbursement threshold).

---

## 🛠️ Tech Stack

- **Backend Framework**: Laravel 11/12 (PHP 8.2+)
- **Database**: MongoDB (via `mongodb/laravel-mongodb`)
- **Frontend Layer**: Alpine.js, Tailwind CSS (Utility Engine), Phosphor Icons
- **Security**: Laravel Session Authentication, Custom Role Middleware, Strict Form Requests Validation, and CSRF protection.

---

## 🗄️ Database Schema (MongoDB)

### 1. `users` Collection
```json
{
  "_id": "ObjectId('...')",
  "name": "Priya Sharma",
  "email": "priya.sharma@email.com",
  "password": "$2y$12$...",
  "role": "author",
  "author_id": "AUTH001",
  "city": "Mumbai",
  "joined_date": "2023-01-15"
}
```

### 2. `tickets` Collection
```json
{
  "_id": "ObjectId('...')",
  "ticket_id": "TCK-0002",
  "author_id": "AUTH001",
  "author_name": "Priya Sharma",
  "book_id": "BK-901",
  "book_title": "Whispers of the Ganges",
  "subject": "Q2 royalty payout delay for 3 months",
  "description": "My accumulated royalty crossed threshold but payout is still delayed.",
  "category": "Royalty & Payments",
  "priority": "Critical",
  "status": "In Progress",
  "internal_notes": "Finance escalated for verification.",
  "admin_response": "Dear Priya Sharma, your query has been forwarded...",
  "assigned_to": "671cc0b892a01...",
  "assigned_name": "BookLeaf Operations Lead",
  "created_at": "2026-10-20T10:30:00Z",
  "replied_at": null
}
```

### 3. `books` Collection
```json
{
  "_id": "ObjectId('...')",
  "book_id": "BK-901",
  "author_id": "AUTH001",
  "title": "Whispers of the Ganges",
  "isbn": "978-81-945678-0-1",
  "genre": "Historical Fiction",
  "mrp": 399,
  "author_royalty_per_copy": 64,
  "total_copies_sold": 1420,
  "total_royalty_earned": 90880,
  "royalty_paid": 60000,
  "royalty_pending": 30880,
  "available_on": ["Amazon", "Flipkart", "Store"],
  "status": "Published & Live"
}
```

---

## 🛡️ Role-Based Access Control (RBAC)

API access is enforced at the route middleware layer:
- **`web`**: Bootstraps session state, reading encrypted session cookies.
- **`admin` (`\App\Http\Middleware\AdminMiddleware`)**: Verifies `Auth::user()->role === 'admin'`. Rejects authors or guests with `401 Unauthorized` or `403 Forbidden`.
- **`author` (`\App\Http\Middleware\EnsureAuthor`)**: Verifies `Auth::user()->role === 'author'`. Restricts query payloads to `Auth::user()->author_id`.

---

## 🚀 Local Installation & Setup

### 1. Prerequisites
- PHP >= 8.2 with `php-mongodb` extension enabled
- Composer
- MongoDB Server running locally on `localhost:27017`

### 2. Installation Steps
```bash
# Clone the repository
git clone https://github.com/your-username/bookleaf-portal.git
cd bookleaf-portal

# Install PHP dependencies
composer install

# Configure environment variables
cp .env.example .env
php artisan key:generate
```

### 3. Database Configuration (`.env`)
```env
DB_CONNECTION=mongodb
DB_HOST=127.0.0.1
DB_PORT=27017
DB_DATABASE=bookleaf_portal
```

### 4. Seed Data & Start Server
```bash
# Clear any existing configuration cache
php artisan config:clear
php artisan route:clear

# Populate demo authors, books, admin account, and ticket records
php artisan db:seed

# Launch local server
php artisan serve
```
Open your browser and navigate to: `http://127.0.0.1:8000`.

---

## 📖 REST API Specification

**Base URL**: `http://localhost:8000/api`  
**Common Headers**: `Accept: application/json`, `X-CSRF-TOKEN: <token>`

### 1. Authentication Endpoints

#### `POST /auth/login`
Authenticate user credentials and initiate session.
- **Body**:
  ```json
  {
    "email": "admin@bookleafpub.com",
    "password": "admin123"
  }
  ```
- **Responses**:
  - `200 OK`: Returns authenticated user profile and initializes session cookie.
  - `401 Unauthorized`: Invalid credentials.

#### `GET /auth/me`
Check active session status and identity profile.
- **Responses**:
  - `200 OK`: Returns active user object.
  - `401 Unauthorized`: No active session found.

#### `POST /auth/logout`
Flush session tokens and log user out.
- **Responses**:
  - `200 OK`: Session terminated.

---

### 2. Author Portal (Protected: `role:author`)

#### `GET /author/my-books`
Retrieve titles and aggregate royalty accounting belonging to the logged-in author.
- **Responses**:
  - `200 OK`:
    ```json
    {
      "author": { "author_id": "AUTH001", "name": "Priya Sharma", "city": "Mumbai" },
      "summary": { "total_copies_sold": 1420, "total_royalty_earned": 90880, "royalty_pending": 30880 },
      "books": [ ]
    }
    ```

#### `GET /author/tickets`
List all historical support queries submitted by the logged-in author.
- **Responses**:
  - `200 OK`: Array of ticket documents scoped to `author_id`.

#### `POST /author/tickets`
Submit a new support ticket.
- **Body**:
  ```json
  {
    "book_id": "BK-901",
    "subject": "Missing Q2 Royalty Payment",
    "description": "My threshold was reached last month but payment has not cleared."
  }
  ```
- **Responses**:
  - `201 Created`: Returns created ticket document with auto-assigned `ticket_id`, initial AI category, and priority assessment.
  - `422 Unprocessable Entity`: Validation failed.

---

### 3. Operations Desk (Protected: `role:admin`)

#### `GET /admin/authors`
Fetch complete author database directory.
- **Responses**:
  - `200 OK`: Array of author records with contact details and associated catalogue counts.

#### `GET /admin/books`
Fetch global publishing catalog with platform-wide sales figures.
- **Responses**:
  - `200 OK`: Array of all book documents.

#### `GET /admin/tickets`
Fetch master ticket queue.
- **Responses**:
  - `200 OK`: Array of ticket documents containing author queries, AI categories, and assigned agents.

#### `POST /admin/tickets/{ticket_id}/assign`
Self-assign ticket to the currently logged-in admin.
- **Path Parameter**: `ticket_id` (e.g. `TCK-0002`)
- **Responses**:
  - `200 OK`:
    ```json
    {
      "message": "Ticket assigned successfully.",
      "ticket": {
        "ticket_id": "TCK-0002",
        "assigned_to": "671cc0...",
        "assigned_name": "BookLeaf Operations Lead"
      }
    }
    ```
  - `404 Not Found`: Ticket ID does not exist.

#### `POST /admin/tickets/{ticket_id}/reply`
Save internal notes, update status, and dispatch official email response to the author.
- **Body**:
  ```json
  {
    "response": "Dear Author, we have reviewed your concern...",
    "status": "Resolved",
    "internal_notes": "Verified via accounts NEFT records.",
    "category": "Royalty & Payments",
    "priority": "Critical"
  }
  ```
- **Responses**:
  - `200 OK`: Ticket updated successfully.
  - `422 Unprocessable Entity`: Missing response body or invalid status.

---

## 🔒 Standard Error Handling

All endpoints enforce uniform, predictable JSON error payloads:

### Validation Error (`422 Unprocessable Entity`)
```json
{
  "success": false,
  "message": "Validation failed.",
  "errors": {
    "subject": ["The subject field is required."],
    "description": ["The description must be at least 10 characters."]
  }
}
```

### Authentication Failure (`401 Unauthorized`)
```json
{
  "success": false,
  "message": "Unauthenticated: Please log in to proceed."
}
```

### Authorization Failure (`403 Forbidden`)
```json
{
  "success": false,
  "message": "Forbidden: You do not have permissions to access this endpoint."
}
```

### Resource Not Found (`404 Not Found`)
```json
{
  "success": false,
  "message": "Requested resource not found."
}
```

---

## 🔑 Test Credentials

The database seeder provisions demo users ready for testing:

| Role | Name | Email | Password | Scope |
| :--- | :--- | :--- | :--- | :--- |
| **Ops Admin** | BookLeaf Ops Admin | `admin@bookleafpub.com` | `admin123` | Full Dashboard & Ticket Queue |
| **Author** | Priya Sharma (`AUTH001`) | `priya.sharma@email.com` | `password123` | Isolated Titles & Royalties |
| **Author** | Rohit Kapoor (`AUTH002`) | `rohit.kapoor@email.com` | `password123` | Isolated Titles & Royalties |
| **Author** | Sunita Deshmukh (`AUTH005`) | `sunita.deshmukh@email.com` | `password123` | Pre-production Pipeline |

# BookLeaf Author Support & Communication Portal

A full-stack, AI-augmented author support and operations workflow portal engineered for **BookLeaf Publishing**.

---

## 1. Quick Local Setup

### Prerequisites
- PHP 8.2+
- Composer
- MongoDB (Local instance or MongoDB Atlas Connection URI)
- Groq Cloud API Key (or OpenAI API Key)

### Installation Steps
```bash
# 1. Clone repository
git clone <private-repo-url>
cd bookleaf-portal

# 2. Install dependencies
composer install

# 3. Environment configuration
cp .env.example .env