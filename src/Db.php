<?php

class Db
{
	private static ?PDO $pdo = null;

	public static function conn(): PDO
	{
		if (self::$pdo === null) {
            // Try standard TCP connection first (default)
            // If 'localhost', try to force TCP loopback if socket fails
            $host = DB_HOST;
            $dsn = 'mysql:host=' . $host . ';dbname=' . DB_NAME . ';charset=utf8mb4';
            
            // If running in CLI and standard connection fails, try explicit socket paths
            if (php_sapi_name() === 'cli') {
                // Attempt to detect if standard host/socket is failing
                // This is tricky to do pre-connection. 
                // Instead, we will try to connect, and if it fails, try socket fallback.
                try {
                     self::$pdo = new PDO($dsn, DB_USER, DB_PASS, [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    ]);
                    return self::$pdo;
                } catch (\PDOException $e) {
                    // Fallback 1: Try 127.0.0.1 if host was localhost
                    if ($host === 'localhost') {
                         try {
                            $dsn = 'mysql:host=127.0.0.1;dbname=' . DB_NAME . ';charset=utf8mb4';
                            self::$pdo = new PDO($dsn, DB_USER, DB_PASS, [
                                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                            ]);
                            return self::$pdo;
                         } catch (\Exception $e2) {
                             // Continue to socket check
                         }
                    }

                    // Fallback 2: Common Socket Paths
                    $sockets = [
                        '/var/lib/mysql/mysql.sock',
                        '/var/run/mysqld/mysqld.sock',
                        '/tmp/mysql.sock'
                    ];
                    
                    foreach ($sockets as $sock) {
                        if (file_exists($sock)) {
                            try {
                                $dsn = 'mysql:unix_socket=' . $sock . ';dbname=' . DB_NAME . ';charset=utf8mb4';
                                self::$pdo = new PDO($dsn, DB_USER, DB_PASS, [
                                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                                ]);
                                return self::$pdo;
                            } catch (\Exception $e3) {
                                // Continue
                            }
                        }
                    }
                    // If we get here, throw original error
                    throw $e;
                }
            }

			self::$pdo = new PDO($dsn, DB_USER, DB_PASS, [
				PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
				PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
			]);
		}
		return self::$pdo;
	}
}
