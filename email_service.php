<?php
require_once 'vendor/autoload.php';

class EmailService {
    private $apiKey;
    private $fromEmail;
    private $fromName;
    
    public function __construct() {
        $this->apiKey = getenv('BREVO_API_KEY') ?: '';
        $this->fromEmail = getenv('FROM_EMAIL') ?: 'deployone73@gmail.com';
        $this->fromName = getenv('FROM_NAME') ?: 'Yakap Daycare Center';
    }
    
    public function sendEmail($to, $subject, $htmlContent, $textContent = null) {
        // If no API key, fall back to a simple log (for development)
        if (empty($this->apiKey)) {
            error_log("Email would be sent to: $to");
            error_log("Subject: $subject");
            error_log("Content: $htmlContent");
            return [
                'success' => true,
                'message' => 'Email logged (no API key configured)'
            ];
        }
        
        try {
            $client = new \GuzzleHttp\Client();
            
            $response = $client->post('https://api.brevo.com/v3/smtp/email', [
                'headers' => [
                    'api-key' => $this->apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'sender' => [
                        'name' => $this->fromName,
                        'email' => $this->fromEmail
                    ],
                    'to' => [
                        [
                            'email' => $to,
                            'name' => $to
                        ]
                    ],
                    'subject' => $subject,
                    'htmlContent' => $htmlContent,
                    'textContent' => $textContent ?: strip_tags($htmlContent)
                ]
            ]);
            
            $responseData = json_decode($response->getBody(), true);
            
            return [
                'success' => true,
                'message' => 'Email sent successfully',
                'id' => $responseData['messageId'] ?? null
            ];
            
        } catch (Exception $e) {
            error_log("Email send error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to send email: ' . $e->getMessage()
            ];
        }
    }
    
    public function sendOTP($to, $otp) {
        $subject = 'OTP Verification - Yakap Daycare Center';
        $htmlContent = $this->getOTPEmailTemplate($otp);
        
        return $this->sendEmail($to, $subject, $htmlContent);
    }
    
    private function getOTPEmailTemplate($otp) {
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
                        <li>This OTP is valid for 10 minutes only</li>
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
}
?>
