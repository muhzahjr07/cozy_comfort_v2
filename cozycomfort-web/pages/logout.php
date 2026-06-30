<?php
// Clear all session data
session_unset();
session_destroy();

// Start a new session for the notification
session_start();
setNotification('You have been logged out successfully.', 'success');

// Redirect to home page
redirect('?page=home');
?>

