// Author: Hugo
// Description: Auth guard - redirects to login if user is not authenticated

function checkAuth() {
    const token = localStorage.getItem('token');

    // If no token found, redirect to login
    if (!token) {
        window.location.href = '/auth/login.html';
    }
}

function logout() {
    // Remove all user data from localStorage
    localStorage.removeItem('token');
    localStorage.removeItem('user_id');
    localStorage.removeItem('username');

    // Redirect to login page
    window.location.href = '/auth/login.html';
}

// Run the check immediately when the script is loaded
checkAuth();