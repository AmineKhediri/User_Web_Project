<?php
/**
 * Standalone TOTP Class (RFC 6238)
 * No Composer dependencies required.
 */
class TOTP {

    /**
     * Generate a random Base32 secret
     */
    public static function generateSecret($length = 16) {
        $base32Chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = '';
        for ($i = 0; $i < $length; $i++) {
            $secret .= $base32Chars[random_int(0, 31)];
        }
        return $secret;
    }

    /**
     * Verify a code
     */
    public static function verifyCode($secret, $code, $discrepancy = 1, $timeSlice = null) {
        if ($timeSlice === null) {
            $timeSlice = floor(time() / 30);
        }

        // Check window (current time +/- discrepancy)
        for ($i = -$discrepancy; $i <= $discrepancy; $i++) {
            $calculatedCode = self::getCode($secret, $timeSlice + $i);
            if (hash_equals((string)$calculatedCode, (string)$code)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the code for a specific time slice
     */
    public static function getCode($secret, $timeSlice) {
        $secretKey = self::base32Decode($secret);
        
        // Pack time into 8-byte binary string (big-endian)
        $time = chr(0).chr(0).chr(0).chr(0).pack('N*', $timeSlice);
        
        // HMAC-SHA1
        $hmac = hash_hmac('sha1', $time, $secretKey, true);
        
        // Get offset
        $offset = ord(substr($hmac, -1)) & 0x0F;
        
        // Read 4 bytes starting at offset
        $hashPart = substr($hmac, $offset, 4);
        
        // Unpack and mask
        $value = unpack('N', $hashPart);
        $value = $value[1];
        $value = $value & 0x7FFFFFFF;

        return str_pad($value % 1000000, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Generate OTPAuth URL for QR Code
     */
    public static function getOtpAuthUrl($issuer, $label, $secret) {
        return 'otpauth://totp/' . rawurlencode($issuer) . ':' . rawurlencode($label) . 
               '?secret=' . $secret . '&issuer=' . rawurlencode($issuer);
    }

    /**
     * Helper: Base32 Decode
     */
    private static function base32Decode($secret) {
        $secret = strtoupper($secret);
        $base32Chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $base32Vals = array_flip(str_split($base32Chars));
        
        $output = '';
        $buffer = 0;
        $bufferSize = 0;
        
        for ($i = 0; $i < strlen($secret); $i++) {
            $char = $secret[$i];
            if (!isset($base32Vals[$char])) continue; // Ignore invalid chars
            
            $buffer = ($buffer << 5) | $base32Vals[$char];
            $bufferSize += 5;
            
            if ($bufferSize >= 8) {
                $bufferSize -= 8;
                $output .= chr(($buffer >> $bufferSize) & 0xFF);
            }
        }
        
        return $output;
    }
}
?>
