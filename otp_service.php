<?php
require_once 'vendor/autoload.php';
require_once 'otp_config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class OTPService {
    private $config;
    private $conn;
    
    public function __construct($connection) {
        $this->config = include 'otp_config.php';
        $this->conn = $connection;
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
            
            // Send email
            $mail = new PHPMailer(true);
            
            // Server settings
            $mail->isSMTP();
            $mail->Host = $this->config['email']['smtp_host'];
            $mail->SMTPAuth = true;
            $mail->Username = $this->config['email']['smtp_username'];
            $mail->Password = $this->config['email']['smtp_password'];
            $mail->SMTPSecure = $this->config['email']['smtp_encryption'];
            $mail->Port = $this->config['email']['smtp_port'];
            
            // Timeout settings
            $mail->Timeout = 30;
            $mail->SMTPKeepAlive = true;
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );
            
            // Recipients
            $mail->setFrom($this->config['email']['from_email'], $this->config['email']['from_name']);
            $mail->addAddress($email);
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = 'OTP Verification - Yakap Daycare Center';
            $mail->Body = $this->getEmailTemplate($otp);
            
            $mail->send();
            
            return [
                'success' => true,
                'message' => 'OTP sent successfully to ' . $email
            ];
            
        } catch (Exception $e) {
            // Log the error for debugging
            error_log("OTP Send Error: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'OTP could not be sent. Mailer Error: ' . $e->getMessage()
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
    
    private function getEmailTemplate($otp) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>OTP Verification</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #1B5E20, #2E7D32); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                .otp-code { background: #1B5E20; color: white; font-size: 32px; font-weight: bold; padding: 20px; text-align: center; border-radius: 10px; margin: 20px 0; letter-spacing: 5px; }
                .footer { text-align: center; margin-top: 20px; color: #666; font-size: 14px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🔐 OTP Verification</h1>
                    <p>Yakap Daycare Center Management System</p>
                </div>
                <div class='content'>
                    <h2>Hello!</h2>
                    <p>You have requested to verify your email address. Please use the following One-Time Password (OTP) to complete your verification:</p>
                    
                    <div class='otp-code'>$otp</div>
                    
                    <p><strong>Important:</strong></p>
                    <ul>
                        <li>This OTP is valid for 5 minutes only</li>
                        <li>Do not share this code with anyone</li>
                        <li>If you didn't request this, please ignore this email</li>
                    </ul>
                    
                    <p>If you have any questions, please contact our support team.</p>
                </div>
                <div class='footer'>
                    <p>© 2024 Yakap Daycare Center. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
    
    public function cleanupExpiredOTPs() {
        $sql = "DELETE FROM otp_verification WHERE expires_at < NOW()";
        $this->conn->query($sql);
    }
}
?>




