<?php
class Database {
    private $host = "localhost";
    private $dbname = "phichaia_student";
    private $username = "root";
    private $password = "";
    private $conn;

    public function getConnection() {
        $this->conn = null;

        // Auto-detect environment for production credentials if needed
        $is_local = in_array(
            $_SERVER['SERVER_NAME'] ?? $_SERVER['HTTP_HOST'] ?? 'localhost',
            ['localhost', '127.0.0.1']
        );

        if (php_sapi_name() === 'cli') {
            // Treat CLI as local only if running on Windows or within xampp folder
            $is_local = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' || strpos(__DIR__, 'xampp') !== false);
        }

        if (!$is_local) {
            // Fill in production db details if deployed
            $this->username = 'phichaia_stdcare';
            $this->password = '48dv_m64N';
        }

        try {
            $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4";
            $this->conn = new PDO($dsn, $this->username, $this->password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
        } catch (PDOException $exception) {
            die("Connection Error: " . $exception->getMessage());
        }

        return $this->conn;
    }
}
?>