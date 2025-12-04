<?php

/**
 * Mailer Utility
 * Handles sending emails for LaunchPad notifications
 * Uses PHP's built-in mail() function (works on Hostinger shared hosting)
 */

class Mailer {
    private static $fromEmail = MAIL_FROM_EMAIL;
    private static $fromName = MAIL_FROM_NAME;
    
    /**
     * Send an email
     * @param string $to Recipient email
     * @param string $subject Email subject
     * @param string $htmlBody HTML email body
     * @param string $plainBody Plain text fallback (optional)
     * @return bool Success status
     */
    public static function send($to, $subject, $htmlBody, $plainBody = '') {
        try {
            // Generate boundary for multipart email
            $boundary = md5(time());
            
            // Headers
            $headers = [
                'From: ' . self::$fromName . ' <' . self::$fromEmail . '>',
                'Reply-To: ' . self::$fromEmail,
                'MIME-Version: 1.0',
                'Content-Type: multipart/alternative; boundary="' . $boundary . '"',
                'X-Mailer: LaunchPad/1.0',
            ];
            
            // Plain text fallback
            if (empty($plainBody)) {
                $plainBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $htmlBody));
            }
            
            // Build multipart message
            $message = "--{$boundary}\r\n";
            $message .= "Content-Type: text/plain; charset=UTF-8\r\n";
            $message .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
            $message .= $plainBody . "\r\n\r\n";
            
            $message .= "--{$boundary}\r\n";
            $message .= "Content-Type: text/html; charset=UTF-8\r\n";
            $message .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
            $message .= $htmlBody . "\r\n\r\n";
            
            $message .= "--{$boundary}--";
            
            // Send email
            $result = mail($to, $subject, $message, implode("\r\n", $headers));
            
            if ($result) {
                Logger::info("Email sent successfully", ['to' => $to, 'subject' => $subject]);
            } else {
                Logger::error("Failed to send email", ['to' => $to, 'subject' => $subject]);
            }
            
            return $result;
            
        } catch (Exception $e) {
            Logger::exception($e, ['to' => $to, 'subject' => $subject]);
            return false;
        }
    }
    
    /**
     * Send verification approved email
     * @param string $to Recipient email
     * @param string $name Recipient name
     * @param string $userType Type of user (student, company, cdc)
     * @param array $extraData Additional data for the email
     * @return bool Success status
     */
    public static function sendVerificationApproved($to, $name, $userType, $extraData = []) {
        $isStudent = ($userType === 'student');

        $subject = $isStudent
            ? 'Your LaunchPad account has been verified'
            : 'Your LaunchPad partner account has been verified';

        $userTypeDisplay = 'Student';
        if ($userType === 'company') {
            $userTypeDisplay = 'Partner Company';
        } elseif ($userType === 'cdc') {
            $userTypeDisplay = 'CDC Staff';
        }

        $loginUrl = MAIL_APP_URL;

        $htmlBody = self::getEmailTemplate([
            'name' => $name,
            'userType' => $userTypeDisplay,
            'userTypeKey' => $userType,
            'loginUrl' => $loginUrl,
            'extraData' => $extraData
        ]);

        return self::send($to, $subject, $htmlBody);
    }
    
    /**
     * Get the HTML email template for verification approved
     */
    private static function getEmailTemplate($data) {
        $name = htmlspecialchars($data['name']);
        $userType = htmlspecialchars($data['userType']);
        $loginUrl = htmlspecialchars($data['loginUrl']);
        $userTypeKey = isset($data['userTypeKey']) ? $data['userTypeKey'] : 'student';

        $isStudent = ($userTypeKey === 'student');

        $companyLine = '';
        if (!empty($data['extraData']['company_name'])) {
            $companyName = htmlspecialchars($data['extraData']['company_name']);
            $companyLine = "<p style='margin: 0 0 12px 0; color: #2c3e50;'>Assigned company: <strong>{$companyName}</strong></p>";
        }

        if ($isStudent) {
            $mainMessage = "
                <p style='margin: 0 0 12px 0; color: #2c3e50;'>Hi <strong>{$name}</strong>,</p>
                <p style='margin: 0 0 12px 0; color: #2c3e50;'>Your LaunchPad student account has been verified.</p>
                {$companyLine}
                <p style='margin: 0; color: #2c3e50;'>You can now log in to LaunchPad using the account you registered with.</p>
            ";
            $ctaSection = "";
        } else {
            $mainMessage = "
                <p style='margin: 0 0 12px 0; color: #2c3e50;'>Hi <strong>{$name}</strong>,</p>
                <p style='margin: 0 0 12px 0; color: #2c3e50;'>Your {$userType} account has been verified.</p>
                {$companyLine}
                <p style='margin: 0 0 20px 0; color: #2c3e50;'>You can now access the LaunchPad website.</p>
            ";
            $ctaSection = "
                <tr>
                    <td style=\"padding: 0 24px 20px 24px;\">
                        <a href=\"{$loginUrl}\" style=\"display: inline-block; background-color: #395886; color: #ffffff; text-decoration: none; padding: 10px 22px; border-radius: 999px; font-size: 14px; font-weight: 500;\">Open LaunchPad Website</a>
                    </td>
                </tr>
            ";
        }

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Verified - LaunchPad</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Poppins', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #F0F3FA;">
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #F0F3FA;">
        <tr>
            <td style="padding: 24px 16px;">
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="max-width: 560px; margin: 0 auto; background-color: #ffffff; border-radius: 16px;">
                    <tr>
                        <td style="padding: 16px 24px; border-bottom: 1px solid #F0F3FA;">
                            <h1 style="margin: 0; font-size: 20px; font-weight: 600; color: #395886;">LaunchPad</h1>
                            <p style="margin: 4px 0 0 0; font-size: 12px; color: #2c3e50;">OJT Tracking &amp; Management</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 20px 24px 24px 24px;">
                            <h2 style="margin: 0 0 16px 0; font-size: 18px; font-weight: 600; color: #395886;">Account verified</h2>
                            {$mainMessage}
                        </td>
                    </tr>
                    {$ctaSection}
                    <tr>
                        <td style="padding: 14px 24px 18px 24px; border-top: 1px solid #F0F3FA;">
                            <p style="margin: 0; font-size: 11px; color: #2c3e50;">This email was sent by LaunchPad.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }
}
