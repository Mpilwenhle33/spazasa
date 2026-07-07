<?php
// controllers/LanguageController.php

class LanguageController {
    
    public function setLanguage() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);
            $language = $input['language'] ?? 'english';
            
            $allowed = ['english', 'isizulu', 'sesotho', 'afrikaans'];
            if (in_array($language, $allowed)) {
                $_SESSION['language_pref'] = $language;
                
                // Update user preference if logged in
                if (isset($_SESSION['user_id'])) {
                    try {
                        $db = DatabaseConnection::getInstance()->getConnection();
                        $stmt = $db->prepare("UPDATE users SET language_pref = ? WHERE user_id = ?");
                        $stmt->execute([$language, $_SESSION['user_id']]);
                    } catch (Exception $e) {
                        // Silent fail
                    }
                }
                
                echo json_encode(['success' => true]);
                exit;
            }
        }
        
        echo json_encode(['success' => false]);
        exit;
    }
}