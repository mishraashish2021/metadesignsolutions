# Support Ticket API

A small production-minded REST API built with **Laravel and PHP** for managing support tickets.

This project was developed as part of a take-home technical exercise. The exercise allowed the candidate to choose a small feature using a preferred technology stack. I chose to build a **Support Ticket API** using Laravel/PHP to demonstrate API design, validation, maintainable code structure, business-rule handling, edge-case handling, and automated testing.

---

## Tech Stack

* **PHP:** 8.2+
* **Framework:** Laravel 12
* **Database:** SQLite
* **ORM:** Laravel Eloquent
* **Testing:** Laravel Feature Tests / PHPUnit
* **API:** REST

---

## Features

The API provides the following functionality:

* Create a support ticket
* List support tickets
* View a single ticket
* Update ticket details
* Delete a ticket
* Update ticket status through controlled status transitions
* Filter tickets by status
* Filter tickets by priority
* Search tickets by title or description
* Sort ticket results
* Paginate ticket results
* Validate incoming API requests
* Consistent API responses using Laravel API Resources
* Eloquent relationships
* Eager loading to avoid N+1 queries
* Database indexes for commonly queried fields
* Automated feature tests
* Proper handling of non-existing resources

---

# Project Structure

The main application structure is:

```text
app/
├── Enums/
│   ├── TicketPriority.php
│   └── TicketStatus.php
│
├── Http/
│   ├── Controllers/
│   │   └── Api/
│   │       └── TicketController.php
│   │
│   ├── Requests/
│   │   ├── StoreTicketRequest.php
│   │   ├── UpdateTicketRequest.php
│   │   └── UpdateTicketStatusRequest.php
│   │
│   └── Resources/
│       └── TicketResource.php
│
└── Models/
    ├── Ticket.php
    └── User.php

database/
├── factories/
│   └── TicketFactory.php
│
└── migrations/
    └── create_tickets_table.php

routes/
└── api.php

tests/
└── Feature/
    └── TicketTest.php
```

---

# Requirements

Before running the application, make sure the following are installed:

* PHP 8.2 or higher
* Composer
* Git

No separate MySQL installation is required because the project uses SQLite.

---

# Installation

## 1. Clone the repository

```bash
git clone https://github.com/mishraashish2021/metadesignsolutions.git
```

Move into the project directory:

```bash
cd metadesignsolutions
```

---

## 2. Install PHP dependencies

```bash
composer install
```

---

## 3. Create the environment file

Copy the example environment file:

```bash
cp .env.example .env
```

---

## 4. Generate application key

```bash
php artisan key:generate
```

---

## 5. Configure SQLite

Create the SQLite database file:

```bash
touch database/database.sqlite
```

Make sure the `.env` file contains:

```env
DB_CONNECTION=sqlite
```

Other database connection values are not required for SQLite.

---

## 6. Run database migrations

```bash
php artisan migrate
```

This will create the required database tables.

---

## 7. Start the Laravel development server

```bash
php artisan serve
```

The application will be available at:

```text
http://127.0.0.1:8000
```

---

# API Endpoints

| Method | Endpoint                       | Description           |
| ------ | ------------------------------ | --------------------- |
| POST   | `/api/tickets`                 | Create a ticket       |
| GET    | `/api/tickets`                 | List tickets          |
| GET    | `/api/tickets/{ticket}`        | View a single ticket  |
| PUT    | `/api/tickets/{ticket}`        | Update ticket details |
| PATCH  | `/api/tickets/{ticket}/status` | Update ticket status  |
| DELETE | `/api/tickets/{ticket}`        | Delete a ticket       |

---

# API Usage

## 1. Create Ticket

### Request

```http
POST /api/tickets
Content-Type: application/json
```

### Body

```json
{
    "user_id": 1,
    "title": "Payment failed",
    "description": "Customer payment was deducted but the order was not created.",
    "priority": "high",
    "due_date": "2026-09-20"
}
```

### Response

```json
{
    "data": {
        "id": 1,
        "title": "Payment failed",
        "description": "Customer payment was deducted but the order was not created.",
        "priority": "high",
        "status": "open",
        "due_date": "2026-09-20"
    }
}
```

The API returns HTTP status:

```text
201 Created
```

---

# 2. List Tickets

### Request

```http
GET /api/tickets
```

The endpoint supports pagination, filtering, searching, and sorting.

Example:

```http
GET /api/tickets?per_page=10&page=1
```

---

# 3. Filter Tickets

## Filter by status

```http
GET /api/tickets?status=open
```

## Filter by priority

```http
GET /api/tickets?priority=high
```

## Combine filters

```http
GET /api/tickets?status=open&priority=high
```

---

# 4. Search Tickets

Tickets can be searched by title or description.

Example:

```http
GET /api/tickets?search=payment
```

The search performs a case-insensitive partial match using the database query.

---

# 5. Sorting

Supported sorting fields include:

* `created_at`
* `updated_at`
* `priority`
* `status`
* `due_date`

Example:

```http
GET /api/tickets?sort_by=created_at&sort_direction=desc
```

Supported sort directions:

```text
asc
desc
```

Invalid sort fields and directions fall back to safe defaults instead of being directly passed into the database query.

---

# 6. Pagination

Pagination is supported through the `per_page` and `page` parameters.

Example:

```http
GET /api/tickets?per_page=10&page=1
```

The API limits `per_page` to a maximum of **100 records** to avoid unnecessarily large responses.

---

# 7. View a Single Ticket

### Request

```http
GET /api/tickets/1
```

If the ticket exists, the API returns the ticket details.

If the ticket does not exist, Laravel route model binding returns:

```text
404 Not Found
```

---

# 8. Update Ticket

### Request

```http
PUT /api/tickets/1
Content-Type: application/json
```

### Body

```json
{
    "title": "Updated payment issue",
    "priority": "urgent"
}
```

The update endpoint validates the provided fields before updating the ticket.

---

# 9. Update Ticket Status

Ticket status changes are handled through a dedicated endpoint.

### Request

```http
PATCH /api/tickets/1/status
Content-Type: application/json
```

### Body

```json
{
    "status": "in_progress"
}
```

---

## Status Workflow

The supported ticket lifecycle is:

```text
open
  ↓
in_progress
  ↓
resolved
  ↓
closed
```

Only valid transitions are allowed.

For example:

```text
open → in_progress       Allowed
in_progress → resolved   Allowed
resolved → closed        Allowed
```

An invalid transition such as:

```text
open → closed
```

is rejected with HTTP:

```text
422 Unprocessable Entity
```

This prevents arbitrary status changes and keeps the business workflow explicit.

---

# 10. Delete Ticket

### Request

```http
DELETE /api/tickets/1
```

A successful deletion returns:

```text
204 No Content
```

Attempting to delete a ticket that does not exist returns:

```text
404 Not Found
```

---

# Validation

The API uses Laravel Form Requests for request validation.

## Ticket creation validation

The following fields are validated:

### user_id

* Required
* Must be an integer
* Must reference an existing user

### title

* Required
* Must be a string
* Maximum 255 characters

### description

* Required
* Must be a string
* Maximum 5000 characters

### priority

Allowed values:

```text
low
medium
high
urgent
```

### due_date

* Optional
* Must be a valid date
* Cannot be earlier than the current date

---

# Ticket Status and Priority

PHP Enums are used to represent ticket status and priority.

## Ticket Status

```text
open
in_progress
resolved
closed
```

## Ticket Priority

```text
low
medium
high
urgent
```

Using enums helps prevent arbitrary string values from being introduced into the application.

---

# Database Design

The main `tickets` table contains:

| Column        | Type        | Description              |
| ------------- | ----------- | ------------------------ |
| `id`          | Big Integer | Ticket identifier        |
| `user_id`     | Foreign Key | User who owns the ticket |
| `title`       | String      | Ticket title             |
| `description` | Text        | Ticket description       |
| `priority`    | String      | Ticket priority          |
| `status`      | String      | Current ticket status    |
| `due_date`    | Date        | Optional due date        |
| `created_at`  | Timestamp   | Creation time            |
| `updated_at`  | Timestamp   | Last update time         |

The `user_id` column has a foreign-key relationship with the `users` table.

The ticket table also contains indexes for commonly used query fields.

---

# Technical Decisions

## 1. Laravel Form Requests

Validation is separated from the controller using Form Request classes.

This keeps the controller focused on application logic and makes validation rules easier to maintain and test.

---

## 2. PHP Enums

PHP backed enums are used for ticket status and priority.

This provides a clearly defined set of supported values and reduces the possibility of invalid states.

---

## 3. API Resources

`TicketResource` is used to control the API response structure.

Instead of returning the Eloquent model directly, the resource explicitly defines which fields are exposed through the API.

---

## 4. Eager Loading

The ticket's user relationship is eager loaded using:

```php
with('user')
```

This prevents unnecessary database queries when returning user information along with tickets and helps avoid the N+1 query problem.

---

## 5. Pagination

Ticket listing uses Laravel pagination rather than returning all records.

A maximum page size of 100 is applied to prevent excessively large API responses.

---

## 6. Safe Sorting

Sorting fields are restricted to an explicit allowlist.

This prevents arbitrary request values from being used directly as database column names.

---

## 7. Database Indexes

Indexes have been added to fields commonly used for filtering and sorting.

The goal is to improve query performance while avoiding unnecessary indexes that would increase write overhead.

---

## 8. Route Model Binding

Laravel route model binding is used for individual ticket operations.

For example:

```http
GET /api/tickets/{ticket}
```

Laravel automatically resolves the ticket model and returns a 404 response when the requested ticket does not exist.

---

## 9. Dedicated Status Endpoint

Status changes use a dedicated endpoint instead of allowing unrestricted status modification through the normal update endpoint.

This makes the ticket workflow explicit and provides a clear place to enforce status transition rules.

---

# Assumptions

The assessment intentionally leaves some product requirements open-ended. The following assumptions were therefore made:

### 1. Authentication

Authentication and authorization are considered outside the scope of this small feature.

For the purpose of the assessment, `user_id` is supplied while creating a ticket.

In a production application with authentication, the user would normally be derived from the authenticated request rather than accepting an arbitrary `user_id`.

---

### 2. Ticket Ownership

Each ticket belongs to a user through the `user_id` foreign-key relationship.

---

### 3. Status Workflow

The following workflow was selected:

```text
open → in_progress → resolved → closed
```

Backward or skipped transitions are rejected.

---

### 4. Database

SQLite was selected because the exercise is intended to be a small, self-contained feature with an expected effort of approximately 2–3 hours.

The application can be configured to use MySQL or PostgreSQL if required.

---

### 5. Search

The search implementation uses a database `LIKE` query against the title and description.

This is sufficient for the scale of the exercise.

For a production system with a large number of tickets, a full-text search solution or dedicated search engine could be considered.

---

### 6. API Scope

The exercise focuses on backend functionality, so no frontend UI was implemented.

---

# Testing

Automated Laravel feature tests are included in:

```text
tests/Feature/TicketTest.php
```

The tests cover important functionality including:

* Creating a ticket
* Required field validation
* Invalid priority validation
* Listing tickets
* Filtering tickets by priority
* Viewing a single ticket
* Updating a ticket
* Deleting a ticket
* Handling non-existing tickets
* Valid status transitions
* Invalid status transitions

---

# Run Tests

Run the complete test suite:

```bash
php artisan test
```

For a more detailed output:

```bash
php artisan test --verbose
```

---

# Manual Validation

The API was also manually validated during development.

Useful commands:

```bash
php artisan route:list --path=api
```

Check migration status:

```bash
php artisan migrate:status
```

Run the automated tests:

```bash
php artisan test
```

---

# Error Handling

The API uses appropriate HTTP response codes for common scenarios.

Examples:

| Status | Meaning                                       |
| ------ | --------------------------------------------- |
| `200`  | Successful request                            |
| `201`  | Resource successfully created                 |
| `204`  | Resource successfully deleted                 |
| `404`  | Requested ticket does not exist               |
| `422`  | Validation error or invalid status transition |
| `500`  | Unexpected server-side error                  |

Laravel's built-in validation and exception handling are used wherever appropriate.

---

# Limitations

The implementation intentionally avoids unnecessary complexity because the exercise is expected to be completed within approximately 2–3 hours.

Current limitations include:

* Authentication is not implemented.
* Authorization policies are not implemented.
* No frontend application is included.
* Search uses a database `LIKE` query.
* No ticket notification system is implemented.
* No background queues are required for the current feature.
* SQLite is used for local simplicity.
* Status transition history/audit logs are not currently stored.

---

# Future Improvements

With additional development time, the following improvements could be considered:

## Authentication and Authorization

* Laravel Sanctum authentication
* Role-based access control
* Laravel Policies for ticket ownership and permissions

## Search

For a large dataset, replace the basic `LIKE` search with:

* Database full-text search
* Laravel Scout
* Elasticsearch or another dedicated search engine

## Audit History

Maintain a history of:

* Status changes
* Priority changes
* Ticket updates
* User actions

This would be useful for support and compliance purposes.

## Notifications

Add notifications when:

* A ticket is created
* A ticket is assigned
* Ticket priority changes
* Ticket status changes
* A ticket approaches its due date

These notifications could be processed asynchronously using Laravel queues.

## API Documentation

Add OpenAPI/Swagger documentation for easier API consumption and testing.

## Rate Limiting

Add API rate limiting to protect endpoints from excessive requests.

## Docker

Provide a Docker-based development environment for consistent setup across machines.

---

# Security Considerations

The following security-related practices are followed within the scope of the exercise:

* Request validation is handled through Form Requests.
* Database relationships use foreign keys.
* Sorting parameters use an allowlist.
* Pagination has a maximum limit.
* `.env` should not be committed to the repository.
* Sensitive configuration values are kept outside source control.

For a production deployment, authentication, authorization, rate limiting, logging, monitoring, and additional security controls would also be required.

---

# AI-Assisted Development

AI coding tools were permitted and explicitly encouraged as part of the assessment.

AI assistance was used during development for tasks such as:

* Exploring implementation approaches
* Generating initial code structure
* Reviewing Laravel patterns
* Identifying edge cases
* Improving test coverage
* Reviewing API design

All generated code was reviewed, adapted, tested, and validated as part of the development process.

The final implementation decisions and responsibility for the code remain with the developer.

---

# Conclusion

This project intentionally focuses on a small but complete feature rather than building an unnecessarily large application.

The implementation demonstrates:

* REST API design
* Laravel conventions
* Request validation
* Eloquent relationships
* PHP Enums
* API Resources
* Query filtering and pagination
* Business-rule validation
* Error handling
* Database indexing
* Automated feature testing
* Documentation and technical decision-making

The implementation can be extended further depending on the requirements of a production support-ticket system.
