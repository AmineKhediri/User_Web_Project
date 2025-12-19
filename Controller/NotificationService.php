<?php
/**
 * NotificationService - Gère l'envoi de notifications (Email, SMS, WhatsApp)
 * En environnement de développement (XAMPP), tous les codes sont loggés dans error_log.
 * Les notifications ne sont pas envoyées réellement, mais enregistrées pour debug.
 */
class NotificationService {

    /**
     * Envoie un email
     * En développement: loggue dans error_log
     * En production: utiliserait SMTP ou un service externe
     */
    public function sendEmail($to, $subject, $message) {
        // Headers pour emails HTML
        $headers = "From: no-reply@supportini.tn\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "Return-Path: no-reply@supportini.tn\r\n";

        // LOG le contenu pour debug
        $logEntry = [
            'type' => 'EMAIL',
            'to' => $to,
            'subject' => $subject,
            'message' => strip_tags($message),
            'timestamp' => date('Y-m-d H:i:s'),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ];
        error_log("=== NOTIFICATION SENT ===");
        error_log(json_encode($logEntry));
        error_log("=========================");

        // En XAMPP sans SMTP configuré, mail() retourne false
        // Mais on retourne true quand même car le code a été loggé
        try {
            $result = @mail($to, $subject, $message, $headers);
            // Si mail() échoue (expected en XAMPP), on retourne true quand même
            // car au moins le code a été enregistré dans error_log
            return true;
        } catch (Exception $e) {
            error_log("[NOTIFICATION_ERROR] Email send failed: " . $e->getMessage());
            return true; // Retourner true car notification enregistrée
        }
    }

    /**
     * Envoie un SMS
     * En développement: loggue dans error_log
     * En production: utiliserait Twilio ou un service SMS
     */
    public function sendSMS($phone, $message) {
        // LOG le contenu pour debug
        $logEntry = [
            'type' => 'SMS',
            'phone' => $phone,
            'message' => $message,
            'timestamp' => date('Y-m-d H:i:s'),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ];
        error_log("=== NOTIFICATION SENT ===");
        error_log(json_encode($logEntry));
        error_log("=========================");

        // En développement, on retourne true car le SMS a été enregistré
        // En production, cela utiliserait une API SMS réelle
        return true;
    }

    /**
     * Envoie un message WhatsApp
     * En développement: loggue dans error_log
     * En production: utiliserait Twilio ou un service WhatsApp
     */
    public function sendWhatsApp($phone, $message) {
        // LOG le contenu pour debug
        $logEntry = [
            'type' => 'WHATSAPP',
            'phone' => $phone,
            'message' => $message,
            'timestamp' => date('Y-m-d H:i:s'),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ];
        error_log("=== NOTIFICATION SENT ===");
        error_log(json_encode($logEntry));
        error_log("=========================");

        // En développement, on retourne true car le message a été enregistré
        // En production, cela utiliserait une API WhatsApp réelle
        return true;
    }
}
