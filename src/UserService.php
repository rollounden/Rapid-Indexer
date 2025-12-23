<?php

require_once __DIR__ . '/Db.php';

class UserService
{
    public static function create(string $email, string $password): array
    {
        if (empty($email) || empty($password)) {
            throw new Exception('Email and password are required.');
        }
        if (strlen($password) < 6) {
            throw new Exception('Password must be at least 6 characters long.');
        }

        $pdo = Db::conn();
        
        // Check if email already exists
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            throw new Exception('Email address already registered.');
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare('INSERT INTO users (email, password_hash, credits_balance) VALUES (?, ?, 30)');
        $stmt->execute([$email, $hash]);
        
        $userId = $pdo->lastInsertId();
        
        return [
            'id' => (int)$userId,
            'email' => $email,
            'created_at' => date('Y-m-d H:i:s')
        ];
    }

    public static function getByEmail(string $email): ?array
    {
        $pdo = Db::conn();
        $stmt = $pdo->prepare('SELECT id, email, credits_balance, status, role, created_at FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    public static function getById(int $id): ?array
    {
        $pdo = Db::conn();
        $stmt = $pdo->prepare('SELECT id, email, credits_balance, status, role, created_at FROM users WHERE id = ?');
        $stmt->execute([$id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    public static function getRecentPayments(int $limit = 50): array
    {
        $pdo = Db::conn();
        $stmt = $pdo->prepare('
            SELECT p.id, p.user_id, u.email, p.amount, p.currency, p.method, p.status, p.created_at 
            FROM payments p 
            JOIN users u ON p.user_id = u.id 
            ORDER BY p.created_at DESC 
            LIMIT ?
        ');
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getByApiKey(string $apiKey): ?array
    {
        $pdo = Db::conn();
        $stmt = $pdo->prepare('SELECT id, email, credits_balance, status, role, created_at FROM users WHERE api_key = ?');
        $stmt->execute([$apiKey]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    public static function generateApiKey(int $userId): string
    {
        $pdo = Db::conn();
        
        // Generate a secure random key
        try {
            $key = bin2hex(random_bytes(32));
        } catch (Exception $e) {
            // Fallback if random_bytes fails (unlikely on modern PHP)
            $key = bin2hex(openssl_random_pseudo_bytes(32));
        }
        
        $stmt = $pdo->prepare('UPDATE users SET api_key = ? WHERE id = ?');
        $stmt->execute([$key, $userId]);
        
        return $key;
    }
}

