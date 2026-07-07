<?php
// controllers/AuthController.php

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../config/ValidationHelper.php';

class AuthController {
    private $userModel;
    
    public function __construct() {
        $this->userModel = new User();
    }
    
    public function showLogin() {
        if (isset($_SESSION['user_id'])) {
            header('Location: index.php?action=marketplace');
            exit;
        }
        include __DIR__ . '/../views/login.php';
    }
    
    public function showRegister() {
        if (isset($_SESSION['user_id'])) {
            header('Location: index.php?action=marketplace');
            exit;
        }
        include __DIR__ . '/../views/register.php';
    }
    
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=login');
            exit;
        }
        
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        
        $validator = new ValidationHelper(['email' => $email, 'password' => $password]);
        $validator->validateRequired('email')->validateEmail('email')
                  ->validateRequired('password');
        
        if ($validator->fails()) {
            $_SESSION['login_error'] = $validator->getFirstError();
            header('Location: index.php?action=login');
            exit;
        }
        
        $user = $this->userModel->validateCredentials($email, $password);
        
        if (!$user) {
            $_SESSION['login_error'] = 'Invalid email or password';
            header('Location: index.php?action=login');
            exit;
        }
        
        session_regenerate_id(true);
        
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['location'] = $user['location'];
        $_SESSION['language_pref'] = $user['language_pref'] ?? 'english';
        
        if ($user['role'] === 'admin' || $user['role'] === 'moderator') {
            header('Location: admin.php');
        } else {
            header('Location: index.php?action=marketplace');
        }
        exit;
    }
    
    public function register() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=register');
            exit;
        }
        
        $username = $_POST['username'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $fullName = $_POST['full_name'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $location = $_POST['location'] ?? '';
        $postalCode = $_POST['postal_code'] ?? '';
        
        $validator = new ValidationHelper([
            'username' => $username,
            'email' => $email,
            'password' => $password,
            'full_name' => $fullName
        ]);
        
        $validator->validateRequired('username')
                  ->validateLength('username', 3, 50)
                  ->validateRequired('email')
                  ->validateEmail('email')
                  ->validateRequired('password')
                  ->validatePassword('password')
                  ->validateRequired('full_name')
                  ->validateLength('full_name', 2, 100);
        
        if ($validator->fails()) {
            $_SESSION['register_errors'] = $validator->getErrors();
            $_SESSION['register_data'] = $_POST;
            header('Location: index.php?action=register');
            exit;
        }
        
        if ($this->userModel->isUsernameTaken($username)) {
            $_SESSION['register_errors'] = ['username' => 'Username is already taken'];
            header('Location: index.php?action=register');
            exit;
        }
        
        if ($this->userModel->isEmailTaken($email)) {
            $_SESSION['register_errors'] = ['email' => 'Email is already registered'];
            header('Location: index.php?action=register');
            exit;
        }
        
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        $result = $this->userModel->create($username, $email, $passwordHash, $fullName, $phone, $location, $postalCode);
        
        if ($result) {
            $_SESSION['register_success'] = 'Account created successfully! Please login.';
            header('Location: index.php?action=login');
        } else {
            $_SESSION['register_errors'] = ['general' => 'Failed to create account. Please try again.'];
            header('Location: index.php?action=register');
        }
        exit;
    }
    
    public function logout() {
        session_destroy();
        header('Location: index.php?action=home');
        exit;
    }
}