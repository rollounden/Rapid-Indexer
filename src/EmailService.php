<?php

class EmailService
{
    private $fromEmail;
    private $fromName;

    public function __construct()
    {
        // Use definitions from config, or defaults
        $this->fromEmail = defined('MAIL_FROM_ADDRESS') ? MAIL_FROM_ADDRESS : 'noreply@' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
        $this->fromName = defined('MAIL_FROM_NAME') ? MAIL_FROM_NAME : 'Rapid Indexer';
    }

    public function send(string $to, string $subject, string $htmlBody): bool
    {
        // Hostinger / Standard PHP Mail Headers
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=utf-8',
            'From: ' . $this->fromName . ' <' . $this->fromEmail . '>',
            'Reply-To: ' . $this->fromEmail,
            'X-Mailer: PHP/' . phpversion()
        ];

        // Send the email
        return mail($to, $subject, $this->wrapTemplate($htmlBody), implode("\r\n", $headers));
    }

    // Wrap content in a nice HTML template
    private function wrapTemplate($content): string 
    {
        return '
        <!DOCTYPE html>
        <html>
        <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f4f4f4; padding: 20px;">
            <div style="max-width: 600px; margin: 0 auto; background: #fff; padding: 20px; border-radius: 8px;">
                <div style="text-align: center; padding-bottom: 20px; border-bottom: 1px solid #eee;">
                    <h1 style="color: #be123c; margin: 0;">' . $this->fromName . '</h1>
                </div>
                <div style="padding: 20px 0;">
                    ' . $content . '
                </div>
                <div style="text-align: center; font-size: 12px; color: #999; margin-top: 20px; border-top: 1px solid #eee; padding-top: 10px;">
                    &copy; ' . date('Y') . ' ' . $this->fromName . '. All rights reserved.
                </div>
            </div>
        </body>
        </html>';
    }

    public function sendPasswordReset(string $to, string $token)
    {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $resetLink = $protocol . "://" . $host . "/reset_password.php?token=" . $token;
        
        $body = "
            <h2>Reset Your Password</h2>
            <p>We received a request to reset the password for your account associated with this email address.</p>
            <p style='text-align: center; margin: 30px 0;'>
                <a href='{$resetLink}' style='background-color: #be123c; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; font-weight: bold;'>Reset Password</a>
            </p>
            <p>Or copy and paste this link into your browser:</p>
            <p><a href='{$resetLink}'>{$resetLink}</a></p>
            <p>This link will expire in 1 hour. If you did not request a password reset, please ignore this email.</p>
        ";

        return $this->send($to, "Reset Your Password - Rapid Indexer", $body);
    }

    public function sendPromo(string $to, string $subject, string $message)
    {
        return $this->send($to, $subject, $message);
    }
}

