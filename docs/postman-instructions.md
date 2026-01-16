# Postman Collection Setup

This guide explains how to import and use the Postman collection for testing the POS Backend API.

## Importing the Collection and Environment

1. **Open Postman**: Launch the Postman application on your computer.

2. **Import Collection**:
   - Click on "Import" in the top left.
   - Select "File" tab.
   - Choose `docs/postman/pos-backend.postman_collection.json` from the repository.
   - Click "Import".

3. **Import Environment**:
   - Again, click "Import".
   - Select "File" tab.
   - Choose `docs/postman/local.postman_environment.json`.
   - Click "Import".

4. **Select Environment**:
   - In the top right of Postman, select the "local" environment from the dropdown (it should appear after import).

## Environment Variables

The environment file sets up the following variables:

- `base_url` — Set to `http://localhost:8000/api` (adjust if your server runs on a different port).
- `token` — Initially empty; will be populated after sign-in.
- `tenant_id` — Initially set to `1`; update based on your tenant.

## Usage Guide

1. **Sign Up/Sign In**:
   - Run the "Sign Up" request under "Auth" folder to create a new user and tenant.
   - Or use "Sign In" to authenticate an existing user.
   - The token will be automatically saved to the `token` environment variable.

2. **Set Tenant**:
   - After authentication, note your tenant ID from the response.
   - Update the `tenant_id` variable in the environment if needed.

3. **Authenticated Requests**:
   - For requests requiring authentication, the `Authorization: Bearer {{token}}` header is pre-configured.
   - For tenant-specific endpoints, the `X-Tenant-ID: {{tenant_id}}` header is included.

4. **Explore Endpoints**:
   - Use folders like "Products", "Orders", etc., to test various API endpoints.
   - Check responses and adjust variables as needed.

## Tips

- Ensure your Laravel server is running (`php artisan serve`).
- If you encounter authentication errors, re-run the sign-in request to refresh the token.
- For production testing, create a new environment with the appropriate `base_url`.

If you have issues, refer to the main [README.md](../README.md) for API details.
