# Auth Module — Hugo (Student 1)

## What I built
I was responsible for the Authentication & Users module.

## Files I wrote
- `auth/UserModel.php` — Database operations (insert, find by email, find by token, update token)
- `auth/AuthController.php` — Register and login endpoints
- `auth/AuthMiddleware.php` — Token validation middleware
- `auth/register.html` — Registration page
- `auth/login.html` — Login page
- `auth/auth-guard.js` — Redirects unauthenticated users to login
- `users.sql` — Users table SQL

## My endpoints
- `POST /api/register`
- `POST /api/login`

## Author
Hugo — Student 1 (Authentication & Users)