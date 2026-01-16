# Mini SaaS / POS Backend System (API-First)

A scalable, secure, and multi-tenant Point-of-Sale (POS) backend system built with Laravel 11. This API-first application provides inventory management, order processing, and reporting features for multiple independent businesses (tenants), ensuring strict data isolation.

## Table of Contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation & Setup](#installation--setup)
- [Architecture Overview](#architecture-overview)
- [Multi-Tenancy Strategy](#multi-tenancy-strategy)
- [Database Schema](#database-schema)
- [API Endpoints](#api-endpoints)
- [Authentication Flow](#authentication-flow)
- [Key Design Decisions and Trade-offs](#key-design-decisions-and-trade-offs)
- [Performance Considerations](#performance-considerations)
- [Testing](#testing)
- [Deployment](#deployment)
- [Contributing](#contributing)
- [License](#license)

## Features

- **Multi-Tenant Architecture**: Complete data isolation between tenants using global scopes and middleware.
- **Authentication & Authorization**: Laravel Sanctum with role-based access control (Owner/Staff roles).
- **Inventory Management**: Product CRUD with stock tracking, low-stock alerts, and SKU uniqueness per tenant.
- **Order Management**: Multi-item orders with transactional stock deduction, status tracking (Pending/Paid/Cancelled), and inventory restoration on cancellation.
- **Reporting**: Optimized reports for daily sales, top-selling products, and low-stock items.
- **Security**: Form request validation, mass assignment protection, rate limiting, and secure error handling.
- **Performance**: Eager loading, optimized queries, and database indexing.
- **Background Jobs**: Top products report uses a queued job (`GenerateTopProductsReport`) for heavy queries, with results cached for 1 hour to improve performance.

## Requirements

- PHP 8.2+
- Composer
- MySQL 8.0+
- Laravel 11
- Node.js & npm (for asset compilation)

## Installation & Setup

1. **Clone the Repository**:
   ```bash
   git clone https://github.com/sehabkhanzehad/pos-backend.git
   cd pos-backend
   ```

2. **Install Dependencies**:
   ```bash
   composer install
   npm install
   ```

3. **Environment Configuration**:
   - Copy `.env.example` to `.env`:
     ```bash
     cp .env.example .env
     ```
   - Update database credentials and other settings in `.env`:
     ```
     APP_NAME="POS Backend"
     APP_ENV=local
     APP_DEBUG=false
     DB_CONNECTION=mysql
     DB_HOST=127.0.0.1
     DB_PORT=3306
     DB_DATABASE=pos_backend
     DB_USERNAME=your_username
     DB_PASSWORD=your_password
     SANCTUM_STATEFUL_DOMAINS=localhost,127.0.0.1
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

7. **Build Assets**:
   ```bash
   npm run build
   ```

8. **Start the Server**:
   ```bash
   php artisan serve
   ```

9. **Run Queue Worker** (for background jobs):
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

## API Endpoints

### Authentication
- `POST /api/auth/sign-up` - Register a new user/tenant. Body: `{name, email, password}`.
- `POST /api/auth/sign-in` - Login and get token. Body: `{email, password}`. Returns: `{accessToken, tokenType}`.
- `GET /api/auth/user` - Get authenticated user. Headers: `Authorization: Bearer <token>`.
- `POST /api/auth/sign-out` - Logout. Headers: `Authorization: Bearer <token>`.

### Tenants
- `GET /api/tenants` - List user's tenants.
- `POST /api/tenants` - Create a tenant. Body: `{name}`.
- `GET /api/tenants/{tenant}` - Show tenant details.
- `PUT /api/tenants/{tenant}` - Update tenant. Body: `{name}`.
- `DELETE /api/tenants/{tenant}` - Delete tenant.

### Customers (Tenant-Scoped)
- `GET /api/customers` - List customers (paginated).
- `POST /api/customers` - Create customer. Body: `{name, phone, email, address}`.
- `GET /api/customers/{customer}` - Show customer.
- `PUT /api/customers/{customer}` - Update customer.
- `DELETE /api/customers/{customer}` - Delete customer.

### Products (Tenant-Scoped)
- `GET /api/products` - List products (paginated).
- `POST /api/products` - Create product. Body: `{name, sku, price, stock_qty, low_stock_threshold}`.
- `GET /api/products/{product}` - Show product.
- `PUT /api/products/{product}` - Update product.
- `DELETE /api/products/{product}` - Delete product.

### Orders (Tenant-Scoped)
- `GET /api/orders` - List orders (paginated).
- `POST /api/orders` - Create order. Body: `{customer_id, items: [{product_id, qty}]}`.
- `GET /api/orders/{order}` - Show order with items.
- `POST /api/orders/{order}/paid` - Mark as paid.
- `POST /api/orders/{order}/cancel` - Cancel order (restores stock).

### Reports (Tenant-Scoped)
- `GET /api/reports/daily-sales?date=2026-01-16` - Daily sales: `{total_orders, total_revenue}`.
- `GET /api/reports/top-products?start_date=2026-01-01&end_date=2026-01-16` - Top 5: `[{id, name, total_sold}]`.
- `GET /api/reports/low-stock` - Low-stock: Products where stock <= threshold.

### Other
- `GET /api/staffs` - Manage staff (Owner only).
- `GET /api/roles` - Manage roles/permissions.

All tenant-scoped endpoints require `Authorization: Bearer <token>` and `X-Tenant-ID: <id>` headers.

## API Usage Examples

### Sign Up
```bash
curl -X POST http://localhost:8000/api/auth/sign-up \
  -H "Content-Type: application/json" \
  -d '{"name": "John Doe", "email": "john@example.com", "password": "password123"}'
```

### Sign In
```bash
curl -X POST http://localhost:8000/api/auth/sign-in \
  -H "Content-Type: application/json" \
  -d '{"email": "john@example.com", "password": "password123"}'
```
Response: `{"success": true, "data": {"accessToken": "token_here", "tokenType": "Bearer"}}`

### Create Product (Requires Token and Tenant-ID)
```bash
curl -X POST http://localhost:8000/api/products \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "X-Tenant-ID: 1" \
  -H "Content-Type: application/json" \
  -d '{"name": "Laptop", "sku": "LAP001", "price": 1000, "stock_qty": 50, "low_stock_threshold": 10}'
```

### Get Orders
```bash
curl -X GET http://localhost:8000/api/orders \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "X-Tenant-ID: 1"
```

For a full Postman collection, import the following JSON (save as `pos-backend.postman_collection.json`):

```json
{
  "info": {
    "name": "POS Backend API",
    "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
  },
  "item": [
    {
      "name": "Auth",
      "item": [
        {
          "name": "Sign Up",
          "request": {
            "method": "POST",
            "header": [{"key": "Content-Type", "value": "application/json"}],
            "body": {"mode": "raw", "raw": "{\"name\": \"John Doe\", \"email\": \"john@example.com\", \"password\": \"password123\"}"},
            "url": {"raw": "{{base_url}}/auth/sign-up", "host": ["{{base_url}}"], "path": ["auth", "sign-up"]}
          }
        },
        {
          "name": "Sign In",
          "request": {
            "method": "POST",
            "header": [{"key": "Content-Type", "value": "application/json"}],
            "body": {"mode": "raw", "raw": "{\"email\": \"john@example.com\", \"password\": \"password123\"}"},
            "url": {"raw": "{{base_url}}/auth/sign-in", "host": ["{{base_url}}"], "path": ["auth", "sign-in"]}
          }
        }
      ]
    },
    {
      "name": "Products",
      "item": [
        {
          "name": "Get Products",
          "request": {
            "method": "GET",
            "header": [
              {"key": "Authorization", "value": "Bearer {{token}}"},
              {"key": "X-Tenant-ID", "value": "{{tenant_id}}"}
            ],
            "url": {"raw": "{{base_url}}/products", "host": ["{{base_url}}"], "path": ["products"]}
          }
        },
        {
          "name": "Create Product",
          "request": {
            "method": "POST",
            "header": [
              {"key": "Authorization", "value": "Bearer {{token}}"},
              {"key": "X-Tenant-ID", "value": "{{tenant_id}}"},
              {"key": "Content-Type", "value": "application/json"}
            ],
            "body": {"mode": "raw", "raw": "{\"name\": \"Laptop\", \"sku\": \"LAP001\", \"price\": 1000, \"stock_qty\": 50, \"low_stock_threshold\": 10}"},
            "url": {"raw": "{{base_url}}/products", "host": ["{{base_url}}"], "path": ["products"]}
          }
        }
      ]
    }
  ],
  "variable": [
    {"key": "base_url", "value": "http://localhost:8000/api"},
    {"key": "token", "value": ""},
    {"key": "tenant_id", "value": "1"}
  ]
}
```

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
- **No Frontend**: API-only for flexibility. Trade-off: Requires separate frontend, but allows any client.
- **Database Transactions**: Used for order creation/cancellation to ensure consistency. Trade-off: Slight performance hit, but critical for inventory accuracy.

## Performance Considerations

- **Eager Loading**: Used `with()` in controllers (e.g., orders with items/customers) to avoid N+1 queries.
- **Optimized Queries**: Raw SQL in reports (e.g., joins for top products) for speed.
- **Database Indexes**: Added on `tenant_id`, `status`, `created_at`, `sku` (unique per tenant) to speed queries.
- **Rate Limiting**: 60 requests/min for auth, 30 for reports to prevent abuse.
- **Background Jobs**: Top products report uses a queued job (`GenerateTopProductsReport`) for heavy queries, with results cached for 1 hour to improve performance.
- **Trade-offs**: Prioritized read performance over write complexity; transactions add overhead but ensure data integrity.

## Testing

Run tests with:
```bash
php artisan test
```

Basic feature tests are included for auth and order creation. Expand as needed.

## Deployment

- Set `APP_ENV=production` and `APP_DEBUG=false`.
- Use a web server like Nginx with PHP-FPM.
- Configure database backups and monitoring.
- For scaling, consider load balancers and Redis for sessions/cache.

## Contributing

1. Fork the repo.
2. Create a feature branch.
3. Commit changes.
4. Push and create a PR.

## License

This project is licensed under the MIT License.
