<?php
require_once __DIR__ . '/vendor/autoload.php';

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
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #1B5E20, #2E7D32); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                .otp-code { background: #1B5E20; color: white; font-size: 32px; font-weight: bold; padding: 20px; text-align: center; border-radius: 10px; margin: 20px 0; letter-spacing: 5px; display: inline-block; min-width: 200px; }
                .footer { text-align: center; margin-top: 30px; color: #666; font-size: 14px; padding-top: 20px; border-top: 1px solid #eee; }
                .button { display: inline-block; padding: 12px 24px; background-color: #1B5E20; color: white; text-decoration: none; border-radius: 5px; margin: 15px 0; font-weight: bold; }
                .note { background: #f0f0f0; padding: 15px; border-radius: 5px; margin: 20px 0; font-size: 14px; color: #555; }
            </style>
        </head>
        <body style='background-color: #f5f5f5; padding: 20px 0;'>
            <div class='container' style='background-color: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);'>
                <div class='header'>
                    <h1 style='margin: 0;'>🔐 OTP Verification</h1>
                    <p style='margin: 10px 0 0;'>Yakap Daycare Center Management System</p>
                </div>
                <div class='content'>
                    <h2 style='margin-top: 0;'>Hello!</h2>
                    <p>Your One-Time Password (OTP) for verification is:</p>
                    <div style='text-align: center;'>
                        <div class='otp-code'>$otp</div>
                    </div>
                    <p>Please use this code to complete your verification process. This code is valid for 10 minutes.</p>
                    
                    <div class='note'>
                        <strong>Note:</strong> For security reasons, please do not share this OTP with anyone. If you didn't request this code, please ignore this email or contact our support team.
                    </div>
                    
                    <p>If you have any questions, feel free to contact our support team.</p>
                    
                    <div class='footer'>
                        <p>© " . date('Y') . " Yakap Daycare Center. All rights reserved.</p>
                        <p>This is an automated message, please do not reply to this email.</p>
                    </div>
                </div>
            </div>
        </body>
        </html>
                    <p> You have requested to verify your email address. Please use the following One-Time Password (OTP) to complete your verification:</p>
                    
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
