<?php
if (!class_exists('Database')) {
    require_once __DIR__ . '/Database.php';
}

class User {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function register($username, $email, $password, $firstName, $lastName) {
        // Check if username or email already exists
        if ($this->usernameExists($username)) {
            throw new Exception('ชื่อผู้ใช้นี้มีอยู่แล้ว');
        }
        
        if ($this->emailExists($email)) {
            throw new Exception('อีเมลนี้มีอยู่แล้ว');
        }
        
        // Validate password strength
        if (strlen($password) < 6) {
            throw new Exception('รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร');
        }
        
        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert user
        $userData = [
            'username' => $username,
            'email' => $email,
            'password' => $hashedPassword,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'role' => 'customer',
            'is_active' => 1
        ];
        
        return $this->db->insert('users', $userData);
    }
    
    public function login($username, $password) {
        $user = $this->db->fetch(
            "SELECT * FROM users WHERE (username = :username OR email = :email) AND is_active = 1",
            ['username' => $username, 'email' => $username]
        );
        
        if (!$user) {
            throw new Exception('ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง');
        }
        
        if (!password_verify($password, $user['password'])) {
            throw new Exception('ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง');
        }
        
        // Note: last_login column doesn't exist in current schema
        
        // Create session
        $this->createSession($user['id']);
        
        return $user;
    }
    
    public function logout() {
        if (isset($_SESSION['user_id'])) {
            // Delete session from database
            $this->db->delete('user_sessions', 'user_id = :user_id', ['user_id' => $_SESSION['user_id']]);
            
            // Clear PHP session
            session_unset();
            session_destroy();
        }
    }
    
    public function getCurrentUser() {
        if (!isset($_SESSION['user_id'])) {
            return null;
        }
        
        // Check if session is valid
        $session = $this->db->fetch(
            "SELECT * FROM user_sessions WHERE user_id = :user_id AND expires_at > NOW()",
            ['user_id' => $_SESSION['user_id']]
        );
        
        if (!$session) {
            $this->logout();
            return null;
        }
        
        // Get user data
        $user = $this->db->fetch(
            "SELECT * FROM users WHERE id = :id AND is_active = 1",
            ['id' => $_SESSION['user_id']]
        );
        
        return $user;
    }
    
    public function isLoggedIn() {
        return $this->getCurrentUser() !== null;
    }
    
    public function isAdmin() {
        $user = $this->getCurrentUser();
        return $user && $user['role'] === 'admin';
    }
    
    public function updateProfile($userId, $data) {
        $allowedFields = ['first_name', 'last_name', 'email', 'phone', 'address'];
        $updateData = [];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updateData[$field] = $data[$field];
            }
        }
        
        if (!empty($updateData)) {
            $updateData['updated_at'] = date('Y-m-d H:i:s');
            return $this->db->update('users', $updateData, 'id = :id', ['id' => $userId]);
        }
        
        return false;
    }
    
    public function changePassword($userId, $currentPassword, $newPassword) {
        $user = $this->db->fetch("SELECT password FROM users WHERE id = :id", ['id' => $userId]);
        
        if (!$user || !password_verify($currentPassword, $user['password'])) {
            throw new Exception('รหัสผ่านปัจจุบันไม่ถูกต้อง');
        }
        
        if (strlen($newPassword) < 6) {
            throw new Exception('รหัสผ่านใหม่ต้องมีอย่างน้อย 6 ตัวอักษร');
        }
        
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        return $this->db->update('users', 
            ['password' => $hashedPassword, 'updated_at' => date('Y-m-d H:i:s')], 
            'id = :id', 
            ['id' => $userId]
        );
    }
    
    private function usernameExists($username) {
        $user = $this->db->fetch("SELECT id FROM users WHERE username = :username", ['username' => $username]);
        return $user !== false;
    }
    
    private function emailExists($email) {
        $user = $this->db->fetch("SELECT id FROM users WHERE email = :email", ['email' => $email]);
        return $user !== false;
    }
    
    private function createSession($userId) {
        // Delete existing sessions for this user
        $this->db->delete('user_sessions', 'user_id = :user_id', ['user_id' => $userId]);
        
        // Generate session ID
        $sessionId = bin2hex(random_bytes(32));
        
        // Create new session
        $sessionData = [
            'id' => $sessionId,
            'user_id' => $userId,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'expires_at' => date('Y-m-d H:i:s', time() + SESSION_LIFETIME)
        ];
        
        $this->db->insert('user_sessions', $sessionData);
        
        // Set PHP session
        $_SESSION['user_id'] = $userId;
        $_SESSION['session_id'] = $sessionId;
    }
}
?>