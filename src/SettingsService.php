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
}

