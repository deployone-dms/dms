<?php
require_once 'vendor/autoload.php';
require_once 'otp_config.php';
require_once 'email_service.php';

class OTPService {
    private $config;
    private $conn;
    private $emailService;
    
    public function __construct($connection) {
        $this->config = include 'otp_config.php';
        $this->conn = $connection;
        $this->emailService = new EmailService();
        $this->createOTPTable();
    }
    
    private function createOTPTable() {
        $sql = "CREATE TABLE IF NOT EXISTS otp_verification (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) NOT NULL,
            otp_code VARCHAR(10) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            expires_at TIMESTAMP NOT NULL,
            attempts INT DEFAULT 0,
            is_verified BOOLEAN DEFAULT FALSE,
            INDEX (email),
            INDEX (otp_code)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        $this->conn->query($sql);
    }
    
    public function generateOTP() {
        $length = $this->config['otp']['length'];
        $otp = '';
        for ($i = 0; $i < $length; $i++) {
            $otp .= rand(0, 9);
        }
        return $otp;
    }
    
    public function sendOTP($email) {
        try {
            // Generate OTP
            $otp = $this->generateOTP();
            $expiry = date('Y-m-d H:i:s', strtotime('+' . $this->config['otp']['expiry_minutes'] . ' minutes'));
            
            // Store OTP in database
            $this->storeOTP($email, $otp, $expiry);
            
            // Send email using the new email service
            $result = $this->emailService->sendOTP($email, $otp);
            
            if ($result['success']) {
                return [
                    'success' => true,
                    'message' => 'OTP sent successfully to ' . $email
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'OTP could not be sent. ' . $result['message']
                ];
            }
            
        } catch (Exception $e) {
            // Log the error for debugging
            error_log("OTP Send Error: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'OTP could not be sent. Error: ' . $e->getMessage()
            ];
        }
    }
    
    public function verifyOTP($email, $otp) {
        $sql = "SELECT * FROM otp_verification 
                WHERE email = ? AND otp_code = ? AND expires_at > NOW() AND is_verified = FALSE
                ORDER BY created_at DESC LIMIT 1";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('ss', $email, $otp);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            
            // Check attempts
            if ($row['attempts'] >= $this->config['otp']['max_attempts']) {
                return [
                    'success' => false,
                    'message' => 'Maximum verification attempts exceeded'
                ];
            }
            
            // Mark as verified
            $updateSql = "UPDATE otp_verification SET is_verified = TRUE WHERE id = ?";
            $updateStmt = $this->conn->prepare($updateSql);
            $updateStmt->bind_param('i', $row['id']);
            $updateStmt->execute();
            
            return [
                'success' => true,
                'message' => 'OTP verified successfully'
            ];
        } else {
            // Increment attempts
            $this->incrementAttempts($email, $otp);
            
            return [
                'success' => false,
                'message' => 'Invalid or expired OTP'
            ];
        }
    }
    
    private function storeOTP($email, $otp, $expiry) {
        // Invalidate previous OTPs for this email
        $sql = "UPDATE otp_verification SET is_verified = TRUE WHERE email = ? AND is_verified = FALSE";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        
        // Store new OTP
        $sql = "INSERT INTO otp_verification (email, otp_code, expires_at) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('sss', $email, $otp, $expiry);
        $stmt->execute();
    }
    
    private function incrementAttempts($email, $otp) {
        $sql = "UPDATE otp_verification 
                SET attempts = attempts + 1 
                WHERE email = ? AND otp_code = ? AND expires_at > NOW() AND is_verified = FALSE
                ORDER BY created_at DESC LIMIT 1";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('ss', $email, $otp);
        $stmt->execute();
    }
    
    
    public function cleanupExpiredOTPs() {
        $sql = "DELETE FROM otp_verification WHERE expires_at < NOW()";
        $this->conn->query($sql);
    }
}
?>




