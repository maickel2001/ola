<?php
/**
 * Ola Store Electronics - Database Connection Class
 * Secure database operations using PDO
 */

require_once 'config.php';

class Database {
    private $connection;
    private static $instance = null;

    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }

    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Database query error: " . $e->getMessage());
            return false;
        }
    }

    public function fetch($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt ? $stmt->fetch() : false;
    }

    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt ? $stmt->fetchAll() : false;
    }

    public function insert($table, $data) {
        try {
            $columns = implode(', ', array_keys($data));
            $placeholders = ':' . implode(', :', array_keys($data));
            
            $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($data);
            
            return $this->connection->lastInsertId();
        } catch (PDOException $e) {
            error_log("Database insert error: " . $e->getMessage());
            return false;
        }
    }

    public function update($table, $data, $where, $whereParams = []) {
        try {
            $setClause = [];
            foreach (array_keys($data) as $column) {
                $setClause[] = "$column = :$column";
            }
            
            $sql = "UPDATE $table SET " . implode(', ', $setClause) . " WHERE $where";
            $stmt = $this->connection->prepare($sql);
            
            $allParams = array_merge($data, $whereParams);
            $stmt->execute($allParams);
            
            return $stmt->rowCount();
        } catch (PDOException $e) {
            error_log("Database update error: " . $e->getMessage());
            return false;
        }
    }

    public function delete($table, $where, $params = []) {
        try {
            $sql = "DELETE FROM $table WHERE $where";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->rowCount();
        } catch (PDOException $e) {
            error_log("Database delete error: " . $e->getMessage());
            return false;
        }
    }

    public function count($table, $where = '1', $params = []) {
        try {
            $sql = "SELECT COUNT(*) as count FROM $table WHERE $where";
            $result = $this->fetch($sql, $params);
            return $result ? $result['count'] : 0;
        } catch (PDOException $e) {
            error_log("Database count error: " . $e->getMessage());
            return 0;
        }
    }

    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }

    public function commit() {
        return $this->connection->commit();
    }

    public function rollback() {
        return $this->connection->rollback();
    }

    public function inTransaction() {
        return $this->connection->inTransaction();
    }

    public function close() {
        $this->connection = null;
    }

    public function __destruct() {
        $this->close();
    }
}

// Helper function to get database instance
function getDB() {
    return Database::getInstance();
}

// Helper function to escape SQL
function escape_sql($string) {
    $db = getDB();
    return $db->getConnection()->quote($string);
}
?>