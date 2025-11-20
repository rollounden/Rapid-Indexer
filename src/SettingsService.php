<?php

require_once __DIR__ . '/Db.php';

class SettingsService
{
    private static $cache = [];

    public static function get(string $key, $default = null)
    {
        if (array_key_exists($key, self::$cache)) {
            return self::$cache[$key];
        }

        $pdo = Db::conn();
        // Check if table exists first to avoid errors during migration/setup
        try {
            $stmt = $pdo->prepare("SELECT value FROM settings WHERE `key` = ? LIMIT 1");
            $stmt->execute([$key]);
            $row = $stmt->fetch();
            
            $value = $row ? $row['value'] : $default;
            self::$cache[$key] = $value;
            return $value;
        } catch (PDOException $e) {
            // Table might not exist yet, return default
            return $default;
        }
    }

    public static function set(string $key, ?string $value)
    {
        $pdo = Db::conn();
        $stmt = $pdo->prepare("INSERT INTO settings (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = ?");
        $stmt->execute([$key, $value, $value]);
        self::$cache[$key] = $value;
    }

    public static function getDecrypted(string $key, $default = null)
    {
        $encryptedValue = self::get($key, null);
        if ($encryptedValue === null) {
            return $default;
        }

        // Try to decrypt
        $decrypted = self::decrypt($encryptedValue);
        
        // If decryption fails (e.g. it wasn't encrypted or key changed), return original or default
        // But here we assume if we call getDecrypted, it SHOULD be encrypted. 
        // If it fails, it might be legacy plaintext.
        if ($decrypted === false) {
            // Fallback: if value looks like base64:iv, it might be broken encryption
            // Otherwise assume it's plaintext (legacy)
            return $encryptedValue;
        }

        return $decrypted;
    }

    public static function setEncrypted(string $key, ?string $value)
    {
        if ($value === null) {
            self::set($key, null);
            return;
        }
        
        $encrypted = self::encrypt($value);
        self::set($key, $encrypted);
    }

    private static function encrypt($data)
    {
        $key = base64_decode(ENCRYPTION_KEY);
        $ivLength = openssl_cipher_iv_length('aes-256-cbc');
        $iv = openssl_random_pseudo_bytes($ivLength);
        $encrypted = openssl_encrypt($data, 'aes-256-cbc', $key, 0, $iv);
        return base64_encode($iv . $encrypted);
    }

    private static function decrypt($data)
    {
        $key = base64_decode(ENCRYPTION_KEY);
        $data = base64_decode($data);
        $ivLength = openssl_cipher_iv_length('aes-256-cbc');
        
        if (strlen($data) < $ivLength) {
            return false;
        }

        $iv = substr($data, 0, $ivLength);
        $encrypted = substr($data, $ivLength);
        return openssl_decrypt($encrypted, 'aes-256-cbc', $key, 0, $iv);
    }
}
