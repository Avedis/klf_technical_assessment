<?php
require_once('User.php');
include 'db.php';
session_start();

// fix db.php and run this file

// Example data
$_POST = [
    'action' => 'insert',
    'first_name' => "John",
    'last_name' => "Doe99",
    'email' => "example@email.com",
    'job_title' => "developper222",
    'address_1' => "example address",
    'date_of_birth' => "1985-10-22",
    'password' => 'password'
];

// logged in user
$_SESSION['user_id'] = 1;

// create instance of user to interact with database
$user = new User($pdo);

// controller/actions simulation
if(!empty($_POST)) {
    $data = $_POST;
    switch($_POST['action']) {
        // did not include salt due to password_hash safely generating and storing one by default
        case 'insert':
            $data['user_inserted'] = $_SESSION['user_id'];
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            $user->insert($data);
            break;
        case 'update':
            $data['user_modified'] = $_SESSION['user_id'];
            $user->update($data);
            break;
        case 'delete':
            $data['user_modified'] = $_SESSION['user_id'];
            $user->softDelete($data);
            break;
        default:
            echo "Invalid action";
            break;
    }
}

if(!empty($_GET)) {
    switch($_GET['action']) {
        case 'select':
            $user->select($_GET['user_id']);
            break;
        default:
            echo "Invalid action";
            break;
    }
}
?>