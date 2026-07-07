<?php
// controllers/ProfileController.php

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../config/ValidationHelper.php';

class ProfileController {
    private $userModel;
    private $productModel;
    private $orderModel;
    
    public function __construct() {
        $this->userModel = new User();
        $this->productModel = new Product();
        $this->orderModel = new Order();
    }
    
    public function showProfile() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=login');
            exit;
        }
        
        $user = $this->userModel->getById($_SESSION['user_id']);
        $listings = $this->productModel->getBySeller($_SESSION['user_id']);
        $purchases = $this->orderModel->getByBuyer($_SESSION['user_id']);
        $sales = $this->orderModel->getBySeller($_SESSION['user_id']);
        
        include __DIR__ . '/../views/profile.php';
    }
    
    public function updateProfile() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=login');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=profile');
            exit;
        }
        
        $fullName = $_POST['full_name'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $location = $_POST['location'] ?? '';
        $postalCode = $_POST['postal_code'] ?? '';
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        $validator = new ValidationHelper(['full_name' => $fullName]);
        $validator->validateRequired('full_name')
                  ->validateLength('full_name', 2, 100);
        
        if ($validator->fails()) {
            $_SESSION['profile_errors'] = $validator->getErrors();
            header('Location: index.php?action=profile');
            exit;
        }
        
        $updateData = [
            'full_name' => $fullName,
            'phone' => $phone,
            'location' => $location,
            'postal_code' => $postalCode
        ];
        
        if (!empty($currentPassword) || !empty($newPassword) || !empty($confirmPassword)) {
            $user = $this->userModel->getById($_SESSION['user_id']);
            
            if (!password_verify($currentPassword, $user['password_hash'])) {
                $_SESSION['profile_errors'] = ['current_password' => 'Current password is incorrect'];
                header('Location: index.php?action=profile');
                exit;
            }
            
            $passValidator = new ValidationHelper(['new_password' => $newPassword]);
            $passValidator->validatePassword('new_password');
            
            if ($newPassword !== $confirmPassword) {
                $_SESSION['profile_errors'] = ['confirm_password' => 'Passwords do not match'];
                header('Location: index.php?action=profile');
                exit;
            }
            
            if ($passValidator->fails()) {
                $_SESSION['profile_errors'] = $passValidator->getErrors();
                header('Location: index.php?action=profile');
                exit;
            }
            
            $updateData['password_hash'] = password_hash($newPassword, PASSWORD_BCRYPT);
        }
        
        $this->userModel->update($_SESSION['user_id'], $updateData);
        
        $_SESSION['full_name'] = $fullName;
        $_SESSION['location'] = $location;
        
        $_SESSION['profile_success'] = 'Profile updated successfully!';
        header('Location: index.php?action=profile');
        exit;
    }
}