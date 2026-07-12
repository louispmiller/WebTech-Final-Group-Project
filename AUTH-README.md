# Authentication Module — Hugo (Student 1)

## What I built
I was responsible for the Authentication & Users module for the Smart City Dashboard.
This module handles everything related to user accounts: registration, login, logout,
and protecting pages and routes from unauthenticated access.

## Files I wrote

### Backend
- `auth/UserModel.php` — Handles all database operations for the users table.
  Creates users, finds them by email or token, and updates their token after login.

- `auth/AuthController.php` — Handles the register and login logic.
  Validates all inputs, hashes passwords securely, verifies them at login,
  and generates a secure random token on successful authentication.

- `auth/AuthMiddleware.php` — Protects API routes from unauthenticated requests.
  Reads the token from the request header, checks it exists in the database
  and hasn't expired, and blocks the request if the token is invalid.

### Frontend
- `auth/register.html` — Registration page where new users create their account.

- `auth/login.html` — Login page where existing users sign in.
  Stores the token in localStorage after a successful login and redirects to the dashboard.

- `auth/auth-guard.js` — Protects every frontend page that requires authentication.
  Automatically redirects unauthenticated users to the login page.
  Also provides a logout function that clears all user data from localStorage.

### Database
- `users.sql` — SQL script to create the users table.

## API Endpoints
- `POST /api/register` — Creates a new user account
- `POST /api/login` — Authenticates a user and returns a session token

## How the token works
After a successful login, a unique token is generated and saved in the database
with an expiration time of 2 hours. The frontend stores this token in localStorage
and sends it with every protected API request. The middleware verifies the token
before allowing access to protected routes.

## Database
I created the `users` table which stores user accounts with hashed passwords
and authentication tokens.

## Author
Hugo — Student 1 (Authentication & Users)