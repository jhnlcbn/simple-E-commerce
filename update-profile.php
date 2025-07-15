<?php
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: auth/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: profile.php');
    exit();
}

$user = $_SESSION['user'];
$users = json_decode(file_get_contents('data/users.json'), true);
$currentUserIndex = -1;

// Find the current user in the users array
foreach ($users as $index => $u) {
    if ($u['id'] === $user['id']) {
        $currentUserIndex = $index;
        break;
    }
}

if ($currentUserIndex === -1) {
    $_SESSION['error'] = "User not found.";
    header('Location: profile.php');
    exit();
}

// Validate current password
if (!password_verify($_POST['current_password'], $users[$currentUserIndex]['password'])) {
    $_SESSION['error'] = "Current password is incorrect.";
    header('Location: profile.php');
    exit();
}

// Update user information
$users[$currentUserIndex]['name'] = $_POST['name'];
$users[$currentUserIndex]['email'] = $_POST['email'];

// Update password if new one is provided
if (!empty($_POST['new_password'])) {
    $users[$currentUserIndex]['password'] = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
}

// Save updated users data
file_put_contents('data/users.json', json_encode($users, JSON_PRETTY_PRINT));

// Update session user data
$_SESSION['user'] = $users[$currentUserIndex];
$_SESSION['success'] = "Profile updated successfully.";

header('Location: profile.php');
exit(); 