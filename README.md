# Support Ticket API

## Overview

A Laravel-based REST API for managing support tickets.

## Tech Stack

- PHP 8.2+
- Laravel 12
- SQLite
- Eloquent ORM
- PHPUnit/Pest

## Requirements

- PHP
- Composer

## Installation

composer install

cp .env.example .env

php artisan key:generate

touch database/database.sqlite

php artisan migrate

php artisan serve

## API Endpoints

POST   /api/tickets
GET    /api/tickets
GET    /api/tickets/{ticket}
PUT    /api/tickets/{ticket}
PATCH  /api/tickets/{ticket}/status
DELETE /api/tickets/{ticket}

## Filtering

GET /api/tickets?status=open

GET /api/tickets?priority=high

GET /api/tickets?search=payment

## Pagination

GET /api/tickets?per_page=10&page=1

## Testing

php artisan test

## Design Decisions

- SQLite was selected for easy setup and portability.
- PHP enums are used for ticket status and priority.
- Form Requests handle validation.
- API Resources control response structure.
- Eager loading prevents N+1 queries.
- Pagination prevents large unbounded responses.

## Assumptions

1. Authentication/authorization is outside the core assessment scope.

2. user_id is supplied during ticket creation. In a production
   authenticated application, it would be derived from the
   authenticated user.

3. Ticket status transitions follow:
   open → in_progress → resolved → closed.

4. SQLite is used to keep the assessment self-contained.
   The application can be configured for MySQL/PostgreSQL.

5. Search currently uses database LIKE matching. For very large
   datasets, full-text search or a dedicated search engine can
   be considered.