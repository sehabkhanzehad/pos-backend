# Mini SaaS / POS Backend System (API-First)

A scalable, secure, and multi-tenant Point-of-Sale (POS) backend system built with Laravel 12. This API-first application provides inventory management, order processing, and reporting features for multiple independent businesses (tenants), ensuring strict data isolation.

## Table of Contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation & Setup](#installation--setup)
- [Architecture Overview](#architecture-overview)
- [Multi-Tenancy Strategy](#multi-tenancy-strategy)
- [Database Schema](#database-schema)
- [Authentication Flow](#authentication-flow)
- [Key Design Decisions and Trade-offs](#key-design-decisions-and-trade-offs)
- [Performance Considerations](#performance-considerations)
- [Testing](#testing)
- [API Endpoints](#api-endpoints)
- [API Usage](#usage)
- [Postman Collection](#postman-collection)

## Features

- **Multi-Tenant Architecture**: Complete data isolation between tenants using global scopes and middleware.
- **Authentication & Authorization**: Laravel Sanctum with role-based access control (Owner/Staff roles).
- **Inventory Management**: Product CRUD with stock tracking, low-stock alerts, and SKU uniqueness per tenant.
- **Order Management**: Multi-item orders with transactional stock deduction, status tracking (Pending/Paid/Cancelled), and inventory restoration on cancellation.
- **Reporting**: Optimized reports for daily sales, top-selling products, and low-stock items.
- **Security**: Form request validation, mass assignment protection, rate limiting, and secure error handling.
- **Performance**: Eager loading, optimized queries, caching, and database indexing.
- **Background Jobs**: Top products report uses a queued job (`TopProductsReportJob`) for heavy queries, with results cached for 1 hour to improve performance.
- **Testing**: PHPUnit feature tests, currently available for Order workflows.

## Requirements

- PHP 8.2+
- Composer
- MySQL 8.0+

## Installation & Setup

1. **Clone the Repository**:

   ```bash
   git clone https://github.com/sehabkhanzehad/pos-backend.git
   cd pos-backend
   ```

2. **Install Dependencies**:

   ```bash
   composer install
   ```

3. **Environment Configuration**:
   - Copy `.env.example` to `.env`:

    ```bash
    cp .env.example .env
    ```

   - Update database credentials and other settings in `.env`:

    ```ini
     APP_NAME="POSBackend"
     APP_ENV=local
     APP_DEBUG=false

     DB_CONNECTION=mysql
     DB_HOST=127.0.0.1
     DB_PORT=3306
     DB_DATABASE=pos_backend
     DB_USERNAME=your_username
     DB_PASSWORD=your_password
     ```

4. **Generate Application Key**:

   ```bash
   php artisan key:generate
   ```

5. **Run Migrations**:

   ```bash
   php artisan migrate
   ```

6. **Seed Database (Optional)**:

   ```bash
   php artisan db:seed
   ```

7. **Start the Server**:

   ```bash
   php artisan serve
   ```

8. **Run Queue Worker** (for background jobs):

   ```bash
   php artisan queue:work
   ```

The API will be available at `http://localhost:8000/api`.

## Architecture Overview

This application follows an API-first, layered architecture:

- **Controllers**: Handle HTTP requests, delegate to services/policies, and return JSON responses.
- **Services**: Contain business logic (e.g., `OrderService` for order processing).
- **Models**: Eloquent models with relationships, scopes, and traits.
- **Policies**: Enforce authorization (RBAC) without hard-coding in controllers.
- **Middleware**: `ResolveTenant` for multi-tenancy, Sanctum for auth.
- **Traits**: Reusable code like `ApiResponse` for consistent responses.
- **Resources**: Transform data into JSON API format.

The system is modular, with separation of concerns, making it maintainable and scalable.

## Multi-Tenancy Strategy

Multi-tenancy is implemented using a "shared database, separate schemas" approach with Laravel's global scopes:

- **Tenant Resolution**: Via `X-Tenant-ID` header in requests, resolved by `ResolveTenant` middleware.
- **Data Isolation**: All models use `BelongsToTenant` trait, applying global scopes to filter queries by `tenant_id`.
- **Authorization**: Policies check tenant ownership/access.
- **Database**: Single database with `tenant_id` foreign keys; indexes on `tenant_id` for performance.
- **Security**: No tenant can access another's data; enforced at query, policy, and middleware levels.

This ensures complete isolation without external packages, keeping the system lightweight.

## Database Schema

Key tables and relationships:

- **users**: Stores user accounts with roles (Owner/Staff).
- **tenants**: Represents businesses, linked to owners.
- **products**: Inventory items with stock, SKU (unique per tenant), price.
- **customers**: Client details for orders.
- **orders**: Order headers with status, total.
- **order_items**: Order lines linking products and quantities.
- **roles/permissions**: Custom RBAC tables.

Relationships: Tenants own products/customers/orders; Users belong to tenants; Orders have items.

Indexes: On `tenant_id`, `status`, `created_at`, `sku` for query optimization.

## Authentication Flow

1. User signs up via `/api/auth/sign-up`, creating a default tenant.
2. Login via `/api/auth/sign-in` to get Bearer token.
3. Include token in `Authorization` header for protected routes.
4. Use `X-Tenant-ID` for tenant-specific actions.
5. Logout invalidates the token.

## Key Design Decisions and Trade-offs

- **Custom RBAC vs. Packages**: Used custom traits/policies instead of Spatie Laravel Permission for simplicity and control, avoiding extra dependencies. Trade-off: Less features but faster and tailored.
- **Global Scopes for Multi-Tenancy**: Ensures automatic isolation without manual checks. Trade-off: Slightly more complex queries, but secure and performant.
- **Service Layer**: Business logic in services (e.g., `OrderService`) keeps controllers thin. Trade-off: More files, but better testability.
- **Enums for Statuses**: Used PHP enums for order statuses. Trade-off: Type-safe but requires migration for changes.
- **Database Transactions**: Used for order creation/cancellation to ensure consistency. Trade-off: Slight performance hit, but critical for inventory accuracy.
- **Code Documentation**: Used PHPDoc comments throughout the codebase for better readability and to keep documentation relevant. Inline comments added where necessary for clarity.
- **Error Handling**: Global error handling implemented in the bootstrap app file, with try-catch blocks used appropriately in critical sections to ensure robust error management.

## Performance Considerations

- **Eager Loading**: Used `with()` in controllers (e.g., orders with items/customers) to avoid N+1 queries.
- **Optimized Queries**: Raw SQL in reports (e.g., joins for top products) for speed.
- **Database Indexes**: Added on `tenant_id`, `status`, `created_at`, `sku` (unique per tenant) to speed queries.
- **Rate Limiting**: 60 requests/min for auth, 30 for reports to prevent abuse.
- **Background Jobs**: Top products report uses a queued job (`TopProductReportJob`) for heavy queries, with results cached for 1 hour to improve performance.
- **Trade-offs**: Prioritized read performance over write complexity; transactions add overhead but ensure data integrity.

## Testing

Run all tests with:

```bash
php artisan test
```

### Order Feature Tests

We include focused feature tests for the **Order** workflow to validate: creation (stock decrement), viewing (resource structure), marking as paid, cancellation (stock restoration), and insufficient stock handling.

- File: `tests/Feature/OrderTest.php`
- Run only order tests:

```bash
php artisan test tests/Feature/OrderTest.php
# or filter by method
php artisan test --filter user_can_create_an_order
```

The test suite uses `RefreshDatabase` and sets the current tenant context using the `X-Tenant-ID` header. Tests assert database side-effects (orders, order_items, stock) and response structures.

For CI: ensure your workflow runs `php artisan migrate --env=testing` and `php artisan test`.

---

## Caching (Products)

To improve performance for read-heavy product endpoints, the application caches product listings and show responses using Redis with cache tags.

Key points:

- **Driver**: Use `CACHE_DRIVER=redis` in `.env` for tag support.
- **What is cached**:
  - `GET /api/products` (paginated) — cached per tenant + page + includes for 15 minutes.
  - `GET /api/products/{id}` — cached per tenant + product for 1 hour.

See `app/Support/ProductCache.php` for the implementation details and `app/Http/Controllers/Api/Tenant/ProductController.php` for how caching and invalidation are applied.

## API Endpoints

- [Authentication](#authentication)
- [Tenants](#tenants)
- [Staff](#staff)
- [Role & Permissions](#role--permissions)
- [Products](#products)
- [Customers](#customers)
- [Orders](#orders)
- [Reports](#reports)
- [Settings](#settings)

### Authentication

#### POST /api/auth/sign-up

- **Description**: Register a new user and create a default tenant.
- **Headers**: `Content-Type: application/json`
- **Request Body**:

  ```json
  {
    "name": "string",
    "email": "string",
    "password": "string",
    "password_confirmation": "string"
  }
  ```

- **Response**: `201 Created` with user data and tenant info.

#### POST /api/auth/sign-in

- **Description**: Authenticate user and return access token.
- **Headers**: `Content-Type: application/json`
- **Request Body**:

  ```json
  {
    "email": "string",
    "password": "string"
  }
  ```

- **Response**: `200 OK` with `{accessToken, tokenType: "Bearer"}`.

#### GET /api/auth/user

- **Description**: Get authenticated user's profile.
- **Headers**: `Authorization: Bearer <token>`
- **Request Body**: None
- **Response**: `200 OK` with user data.

#### POST /api/auth/sign-out

- **Description**: Logout and invalidate token.
- **Headers**: `Authorization: Bearer <token>`
- **Request Body**: None
- **Response**: `200 OK` with success message.

### Tenants

#### GET /api/tenants

- **Description**: List all tenants owned by the authenticated user.
- **Headers**: `Authorization: Bearer <token>`
- **Request Body**: None
- **Response**: `200 OK` with paginated tenant list.

#### POST /api/tenants

- **Description**: Create a new tenant.
- **Headers**: `Authorization: Bearer <token>`, `Content-Type: application/json`
- **Request Body**:

  ```json
  {
    "name": "string"
  }
  ```

- **Response**: `201 Created` with tenant data.

#### GET /api/tenants/{tenant}

- **Description**: Get details of a specific tenant.
- **Headers**: `Authorization: Bearer <token>`
- **Request Body**: None
- **Response**: `200 OK` with tenant data.

#### PUT /api/tenants/{tenant}

- **Description**: Update tenant details.
- **Headers**: `Authorization: Bearer <token>`, `Content-Type: application/json`
- **Request Body**:

  ```json
  {
    "name": "string"
  }
  ```

- **Response**: `200 OK` with updated tenant data.

#### DELETE /api/tenants/{tenant}

- **Description**: Delete a tenant (if allowed).
- **Headers**: `Authorization: Bearer <token>`
- **Request Body**: None
- **Response**: `200 OK` with success message.

### Staff

#### GET /api/staffs

- **Description**: List staff members for the tenant (Owner only).
- **Headers**: `Authorization: Bearer <token>`, `X-Tenant-ID: <id>`
- **Request Body**: None
- **Response**: `200 OK` with paginated staff list.

#### POST /api/staffs

- **Description**: Add a new staff member.
- **Headers**: `Authorization: Bearer <token>`, `X-Tenant-ID: <id>`, `Content-Type: application/json`
- **Request Body**:

  ```json
  {
    "name": "string",
    "email": "string",
    "password": "string",
    "role": "string (e.g., Staff)"
  }
  ```

- **Response**: `201 Created` with staff data.

#### GET /api/staffs/{user}

- **Description**: Get details of a specific staff member.
- **Headers**: `Authorization: Bearer <token>`, `X-Tenant-ID: <id>`
- **Request Body**: None
- **Response**: `200 OK` with staff data.

#### PUT /api/staffs/{user}

- **Description**: Update staff member details.
- **Headers**: `Authorization: Bearer <token>`, `X-Tenant-ID: <id>`, `Content-Type: application/json`
- **Request Body**:

  ```json
  {
    "name": "string",
    "email": "string",
    "role": "string"
  }
  ```

- **Response**: `200 OK` with updated staff data.

#### DELETE /api/staffs/{user}

- **Description**: Remove a staff member.
- **Headers**: `Authorization: Bearer <token>`, `X-Tenant-ID: <id>`
- **Request Body**: None
- **Response**: `200 OK` with success message.

### Role & Permissions

#### GET /api/roles

- **Description**: List all roles for the tenant.
- **Headers**: `Authorization: Bearer <token>`, `X-Tenant-ID: <id>`
- **Request Body**: None
- **Response**: `200 OK` with roles list.

#### POST /api/roles

- **Description**: Create a new role.
- **Headers**: `Authorization: Bearer <token>`, `X-Tenant-ID: <id>`, `Content-Type: application/json`
- **Request Body**:

  ```json
  {
    "name": "string",
    "permissions": ["array of permission ids"]
  }
  ```

- **Response**: `201 Created` with role data.

#### GET /api/roles/{role}

- **Description**: Get details of a specific role.
- **Headers**: `Authorization: Bearer <token>`, `X-Tenant-ID: <id>`
- **Request Body**: None
- **Response**: `200 OK` with role data.

#### PUT /api/roles/{role}

- **Description**: Update role details.
- **Headers**: `Authorization: Bearer <token>`, `X-Tenant-ID: <id>`, `Content-Type: application/json`
- **Request Body**:

  ```json
  {
    "name": "string",
    "permissions": ["array of permission ids"]
  }
  ```

- **Response**: `200 OK` with updated role data.

#### DELETE /api/roles/{role}

- **Description**: Delete a role.
- **Headers**: `Authorization: Bearer <token>`, `X-Tenant-ID: <id>`
- **Request Body**: None
- **Response**: `200 OK` with success message.

#### GET /api/roles-for-attach

- **Description**: Get roles available for attachment.
- **Headers**: `Authorization: Bearer <token>`, `X-Tenant-ID: <id>`
- **Request Body**: None
- **Response**: `200 OK` with roles list.

#### GET /api/permissions-for-attach

- **Description**: Get permissions available for attachment.
- **Headers**: `Authorization: Bearer <token>`, `X-Tenant-ID: <id>`
- **Request Body**: None
- **Response**: `200 OK` with permissions list.

### Customers

#### GET /api/customers

- **Description**: List customers for the current tenant (paginated).
- **Headers**: `Authorization: Bearer <token>`, `X-Tenant-ID: <id>`
- **Request Body**: None
- **Response**: `200 OK` with paginated customer list.

#### POST /api/customers

- **Description**: Create a new customer.
- **Headers**: `Authorization: Bearer <token>`, `X-Tenant-ID: <id>`, `Content-Type: application/json`
- **Request Body**:

  ```json
  {
    "name": "string",
    "phone": "string (optional)",
    "email": "string (optional)",
    "address": "string (optional)"
  }
  ```

- **Response**: `201 Created` with customer data.

#### GET /api/customers/{customer}

- **Description**: Get details of a specific customer.
- **Headers**: `Authorization: Bearer <token>`, `X-Tenant-ID: <id>`
- **Request Body**: None
- **Response**: `200 OK` with customer data.

#### PUT /api/customers/{customer}

- **Description**: Update customer details.
- **Headers**: `Authorization: Bearer <token>`, `X-Tenant-ID: <id>`, `Content-Type: application/json`
- **Request Body**:

  ```json
  {
    "name": "string",
    "phone": "string (optional)",
    "email": "string (optional)",
    "address": "string (optional)"
  }
  ```

- **Response**: `200 OK` with updated customer data.

#### DELETE /api/customers/{customer}

- **Description**: Delete a customer.
- **Headers**: `Authorization: Bearer <token>`, `X-Tenant-ID: <id>`
- **Request Body**: None
- **Response**: `200 OK` with success message.

### Products

#### GET /api/products

- **Description**: List products for the current tenant (paginated).
- **Headers**: `Authorization: Bearer <token>`, `X-Tenant-ID: <id>`
- **Request Body**: None
- **Response**: `200 OK` with paginated product list.

#### POST /api/products

- **Description**: Create a new product.
- **Headers**: `Authorization: Bearer <token>`, `X-Tenant-ID: <id>`, `Content-Type: application/json`
- **Request Body**:

  ```json
  {
    "name": "string",
    "sku": "string (unique per tenant)",
    "price": "number",
    "stock_qty": "integer",
    "low_stock_threshold": "integer"
  }
  ```

- **Response**: `201 Created` with product data.

#### GET /api/products/{product}

- **Description**: Get details of a specific product.
- **Headers**: `Authorization: Bearer <token>`, `X-Tenant-ID: <id>`
- **Request Body**: None
- **Response**: `200 OK` with product data.

#### PUT /api/products/{product}

- **Description**: Update product details.
- **Headers**: `Authorization: Bearer <token>`, `X-Tenant-ID: <id>`, `Content-Type: application/json`
- **Request Body**:

  ```json
  {
    "name": "string",
    "sku": "string (unique per tenant)",
    "price": "number",
    "stock_qty": "integer",
    "low_stock_threshold": "integer"
  }
  ```

- **Response**: `200 OK` with updated product data.

#### DELETE /api/products/{product}

- **Description**: Delete a product.
- **Headers**: `Authorization: Bearer <token>`, `X-Tenant-ID: <id>`
- **Request Body**: None
- **Response**: `200 OK` with success message.

### Orders

#### GET /api/orders

- **Description**: List orders for the current tenant (paginated).
- **Headers**: `Authorization: Bearer <token>`, `X-Tenant-ID: <id>`
- **Request Body**: None
- **Response**: `200 OK` with paginated order list.

#### POST /api/orders

- **Description**: Create a new order with items.
- **Headers**: `Authorization: Bearer <token>`, `X-Tenant-ID: <id>`, `Content-Type: application/json`
- **Request Body**:

  ```json
  {
    "customer_id": "integer",
    "items": [
      {
        "product_id": "integer",
        "qty": "integer"
      }
    ]
  }
  ```

- **Response**: `201 Created` with order data.

#### GET /api/orders/{order}

- **Description**: Get details of a specific order with items.
- **Headers**: `Authorization: Bearer <token>`, `X-Tenant-ID: <id>`
- **Request Body**: None
- **Response**: `200 OK` with order and items data.

#### POST /api/orders/{order}/paid

- **Description**: Mark order as paid.
- **Headers**: `Authorization: Bearer <token>`, `X-Tenant-ID: <id>`
- **Request Body**: None
- **Response**: `200 OK` with success message.

#### POST /api/orders/{order}/cancel

- **Description**: Cancel order and restore stock.
- **Headers**: `Authorization: Bearer <token>`, `X-Tenant-ID: <id>`
- **Request Body**: None
- **Response**: `200 OK` with success message.

### Reports

#### GET /api/reports/daily-sales

- **Description**: Get daily sales report.
- **Headers**: `Authorization: Bearer <token>`, `X-Tenant-ID: <id>`
- **Query Params**: `date=YYYY-MM-DD`
- **Request Body**: None
- **Response**: `200 OK` with `{total_orders, total_revenue}`.

#### GET /api/reports/top-products

- **Description**: Get top-selling products report.
- **Headers**: `Authorization: Bearer <token>`, `X-Tenant-ID: <id>`
- **Query Params**: `start_date=YYYY-MM-DD&end_date=YYYY-MM-DD`
- **Request Body**: None
- **Response**: `200 OK` with array of top products.

#### GET /api/reports/low-stock

- **Description**: Get low-stock products.
- **Headers**: `Authorization: Bearer <token>`, `X-Tenant-ID: <id>`
- **Request Body**: None
- **Response**: `200 OK` with list of low-stock products.

### Settings

#### POST /api/settings/account/profile

- **Description**: Update user profile.
- **Headers**: `Authorization: Bearer <token>`, `X-Tenant-ID: <id>`, `Content-Type: application/json`
- **Request Body**:

  ```json
  {
    "name": "string",
    "email": "string"
  }
  ```

- **Response**: `200 OK` with updated profile data.

#### POST /api/settings/account/password

- **Description**: Update user password.
- **Headers**: `Authorization: Bearer <token>`, `X-Tenant-ID: <id>`, `Content-Type: application/json`
- **Request Body**:

  ```json
  {
    "current_password": "string",
    "password": "string",
    "password_confirmation": "string"
  }
  ```

- **Response**: `200 OK` with success message.

#### POST /api/settings/tenant/reset

- **Description**: Reset tenant data (Owner only).
- **Headers**: `Authorization: Bearer <token>`, `X-Tenant-ID: <id>`
- **Request Body**: None
- **Response**: `200 OK` with success message.

## Usage

### Example 1: Sign Up and Sign In

```bash
# Sign Up
curl -X POST http://localhost:8000/api/auth/sign-up \
  -H "Content-Type: application/json" \
  -d '{"name": "John Doe", "email": "john@example.com", "password": "password123", "password_confirmation": "password123"}'

# Sign In
curl -X POST http://localhost:8000/api/auth/sign-in \
  -H "Content-Type: application/json" \
  -d '{"email": "john@example.com", "password": "password123"}'
```

### Example 2: Create and List Products

```bash
# Create Product
curl -X POST http://localhost:8000/api/products \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "X-Tenant-ID: 1" \
  -H "Content-Type: application/json" \
  -d '{"name": "Laptop", "sku": "LAP001", "price": 1000, "stock_qty": 50, "low_stock_threshold": 10}'

# List Products
curl -X GET http://localhost:8000/api/products \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "X-Tenant-ID: 1"
```

### Example 3: Create Order

```bash
curl -X POST http://localhost:8000/api/orders \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "X-Tenant-ID: 1" \
  -H "Content-Type: application/json" \
  -d '{"customer_id": 1, "items": [{"product_id": 1, "qty": 2}]}'
```

## Postman Collection

A Postman collection is useful for manual exploration and reviewer convenience. See [docs/postman-instructions.md](docs/postman-instructions.md) for setup instructions and to import the collection.
