<?php
// config/ValidationHelper.php

class ValidationHelper {
    private $errors = [];
    private $data = [];
    
    public function __construct($data = []) {
        $this->data = $data;
    }
    
    public function validateRequired($field, $message = null) {
        $value = $this->data[$field] ?? '';
        if (empty(trim($value))) {
            $this->errors[$field] = $message ?? ucfirst($field) . ' is required';
        }
        return $this;
    }
    
    public function validateEmail($field, $message = null) {
        $value = $this->data[$field] ?? '';
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = $message ?? 'Valid email address is required';
        }
        return $this;
    }
    
    public function validatePassword($field, $message = null) {
        $value = $this->data[$field] ?? '';
        if (strlen($value) < 8) {
            $this->errors[$field] = $message ?? 'Password must be at least 8 characters';
            return $this;
        }
        if (!preg_match('/[A-Z]/', $value)) {
            $this->errors[$field] = $message ?? 'Password must contain at least one uppercase letter';
            return $this;
        }
        if (!preg_match('/[0-9]/', $value)) {
            $this->errors[$field] = $message ?? 'Password must contain at least one number';
            return $this;
        }
        return $this;
    }
    
    public function validateNumeric($field, $message = null) {
        $value = $this->data[$field] ?? '';
        if (!is_numeric($value) || $value <= 0) {
            $this->errors[$field] = $message ?? ucfirst($field) . ' must be a positive number';
        }
        return $this;
    }
    
    public function validateLength($field, $min, $max, $message = null) {
        $value = $this->data[$field] ?? '';
        $len = strlen($value);
        if ($len < $min || $len > $max) {
            $this->errors[$field] = $message ?? ucfirst($field) . " must be between $min and $max characters";
        }
        return $this;
    }
    
    public function validateInArray($field, $allowed, $message = null) {
        $value = $this->data[$field] ?? '';
        if (!in_array($value, $allowed)) {
            $this->errors[$field] = $message ?? ucfirst($field) . ' has an invalid value';
        }
        return $this;
    }
    
    public function passes() {
        return empty($this->errors);
    }
    
    public function fails() {
        return !empty($this->errors);
    }
    
    public function getErrors() {
        return $this->errors;
    }
    
    public function getFirstError() {
        return !empty($this->errors) ? reset($this->errors) : null;
    }
    
    public static function sanitizeInput($input) {
        if (is_array($input)) {
            return array_map([self::class, 'sanitizeInput'], $input);
        }
        return strip_tags(trim($input));
    }
    
    public static function escapeOutput($output) {
        return htmlspecialchars($output, ENT_QUOTES, 'UTF-8');
    }
}