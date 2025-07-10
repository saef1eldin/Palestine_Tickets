<?php
// إعدادات الاتصال بقاعدة البيانات
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'tickets_db');

class Database {
    private $host = DB_HOST;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $dbname = DB_NAME;

    private $dbh;
    private $stmt;
    private $error;

    /**
     * الحصول على اتصال PDO
     *
     * @return PDO|null
     */
    public function getConnection() {
        return $this->dbh;
    }

    public function __construct() {
        // إعداد DSN
        $dsn = 'mysql:host=' . $this->host . ';dbname=' . $this->dbname . ';charset=utf8mb4';
        $options = array(
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        );

        // إنشاء اتصال PDO
        try {
            $this->dbh = new PDO($dsn, $this->user, $this->pass, $options);
        } catch(PDOException $e) {
            $this->error = $e->getMessage();
            error_log('Database Connection Error: ' . $this->error);
            echo 'Database Connection Error: ' . $this->error;
        }
    }

    // إعداد الاستعلام
    public function query($sql) {
        $this->stmt = $this->dbh->prepare($sql);
    }

    // ربط القيم
    public function bind($param, $value, $type = null) {
        if(is_null($type)) {
            switch(true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
        }

        $this->stmt->bindValue($param, $value, $type);
    }

    // تنفيذ الاستعلام
    public function execute() {
        try {
            return $this->stmt->execute();
        } catch(PDOException $e) {
            $this->error = $e->getMessage();
            error_log('Query Execution Error: ' . $this->error);
            return false;
        }
    }

    // الحصول على مجموعة نتائج كمصفوفة من الكائنات
    public function resultSet() {
        $this->execute();
        return $this->stmt->fetchAll();
    }

    // الحصول على سجل واحد ككائن
    public function single() {
        $this->execute();
        return $this->stmt->fetch();
    }

    // الحصول على عدد الصفوف المتأثرة
    public function rowCount() {
        return $this->stmt->rowCount();
    }

    // الحصول على آخر معرف تم إدراجه
    public function lastInsertId() {
        return $this->dbh->lastInsertId();
    }

    // بدء المعاملة
    public function beginTransaction() {
        return $this->dbh->beginTransaction();
    }

    // تنفيذ المعاملة
    public function commit() {
        return $this->dbh->commit();
    }

    // التراجع عن المعاملة
    public function rollBack() {
        return $this->dbh->rollBack();
    }
}
?>
