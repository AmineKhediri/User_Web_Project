<?php
/**
 * NotificationService - Gère l'envoi d'emails
 * Mode sécurisé : Utilise mail() standard mais loggue tout dans error_log pour debug facile.
 */
class NotificationService {

    public function sendEmail($to, $subject, $message) {
        $headers = "From: no-reply@supportini.tn\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

        // 1. Log content for Debugging (Crucial for Localhost without SMTP)
        error_log("=== EMAIL OUTGOING ===");
        error_log("To: $to");
        error_log("Subject: $subject");
        error_log("Message: " . strip_tags($message));
        error_log("======================");

        // 2. Try native PHP mail
        // On XAMPP/macOS, this requires postfix/sendmail configuration.
        // If it fails, it returns false, but at least we logged it above.
        try {
            return mail($to, $subject, $message, $headers);
        } catch (Exception $e) {
            error_log("Email send failed: " . $e->getMessage());
            return false;
        }
    }
}
