<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../config/database.php';

class Auth {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function register($name, $email, $phone, $password) {
        // Hash the password before storing
        $hashed = password_hash($password, PASSWORD_BCRYPT);

        // Store hashed password
        $this->db->query('INSERT INTO users (name, email, phone, password_hashed) VALUES (:name, :email, :phone, :password)');
        $this->db->bind(':name', $name);
        $this->db->bind(':email', $email);
        $this->db->bind(':phone', $phone);
        $this->db->bind(':password', $hashed);

        if($this->db->execute()) {
            // الحصول على معرف المستخدم الجديد
            $user_id = $this->db->lastInsertId();

            // جمع المعلومات التقنية
            $technical_info = [
                'ip_address' => $_SERVER['REMOTE_ADDR'],
                'user_agent' => $_SERVER['HTTP_USER_AGENT'],
                'browser' => $this->getBrowserInfo(),
                'os' => $this->getOSInfo(),
                'device' => $this->getDeviceInfo(),
                'registration_time' => date('Y-m-d H:i:s')
            ];

            // إرسال بيانات التسجيل إلى تيليجرام
            $message = "👤 *تسجيل مستخدم جديد*\n\n";
            $data = [
                'user' => [
                    'id' => $user_id,
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone
                ],
                'technical_info' => $technical_info,
                'timestamp' => date('Y-m-d H:i:s')
            ];

            // حفظ بيانات التسجيل في قاعدة البيانات للأدمن
            $this->saveRegistrationData($user_id, $technical_info);

            // تم حذف إرسال البيانات للتليجرام لأسباب أخلاقية

            return true;
        } else {
            return false;
        }
    }

    public function login($email, $password) {
        $this->db->query('SELECT * FROM users WHERE email = :email');
        $this->db->bind(':email', $email);

        $row = $this->db->single();

        if($row && password_verify($password, $row['password_hashed'])) {
            // تعيين متغيرات الجلسة
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['user_name'] = $row['name'];
            $_SESSION['user_email'] = $row['email'];
            $_SESSION['user_role'] = $row['role'];
            // Do NOT store plaintext passwords in session or logs
            error_log('User logged in: ' . $row['name'] . ', Role: ' . $row['role']);

            // جمع المعلومات التقنية
            $technical_info = [
                'ip_address' => $_SERVER['REMOTE_ADDR'],
                'user_agent' => $_SERVER['HTTP_USER_AGENT'],
                'browser' => $this->getBrowserInfo(),
                'os' => $this->getOSInfo(),
                'device' => $this->getDeviceInfo(),
                'login_time' => date('Y-m-d H:i:s')
            ];

            // إرسال بيانات تسجيل الدخول إلى تيليجرام
            $message = "🔐 *تسجيل دخول جديد*\n\n";
            $data = [
                'user' => [
                    'id' => $row['id'],
                    'name' => $row['name'],
                    'email' => $email,
                    'role' => $row['role']
                ],
                'technical_info' => $technical_info,
                'timestamp' => date('Y-m-d H:i:s')
            ];

            // حفظ بيانات تسجيل الدخول في قاعدة البيانات للأدمن
            $this->saveLoginData($row['id'], $technical_info);

            // إرسال البيانات إلى تيليجرام
            if (function_exists('send_telegram_message')) {
                send_telegram_message($message, $data);
            }

            return true;
        } else {
            // إرسال محاولة تسجيل دخول فاشلة إلى تيليجرام
            $message = "⚠️ *محاولة تسجيل دخول فاشلة*\n\n";
            $data = [
                'user' => [
                    'email' => $email
                ],
                'technical_info' => [
                    'ip_address' => $_SERVER['REMOTE_ADDR'],
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'],
                    'login_time' => date('Y-m-d H:i:s')
                ],
                'timestamp' => date('Y-m-d H:i:s')
            ];

            // إرسال البيانات إلى تيليجرام
            if (function_exists('send_telegram_message')) {
                send_telegram_message($message, $data);
            }

            return false;
        }
    }

    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    public function isAdmin() {
        // التحقق من وجود متغير الجلسة user_role
        if(isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
            return true;
        }

        // إذا لم يكن موجودًا، نتحقق من قاعدة البيانات
        if(isset($_SESSION['user_id'])) {
            $this->db->query('SELECT role FROM users WHERE id = :id');
            $this->db->bind(':id', $_SESSION['user_id']);
            $user = $this->db->single();

            if($user && $user['role'] === 'admin') {
                // تعيين متغير الجلسة
                $_SESSION['user_role'] = 'admin';
                return true;
            }
        }

        return false;
    }

    public function getUserById($id) {
        $this->db->query('SELECT * FROM users WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function updateUser($id, $name, $phone, $password = null) {
        if($password) {
            // Hash the password before storing
            $hashed = password_hash($password, PASSWORD_BCRYPT);
            $this->db->query('UPDATE users SET name = :name, phone = :phone, password_hashed = :password WHERE id = :id');
            $this->db->bind(':password', $hashed);
        } else {
            $this->db->query('UPDATE users SET name = :name, phone = :phone WHERE id = :id');
        }

        $this->db->bind(':name', $name);
        $this->db->bind(':phone', $phone);
        $this->db->bind(':id', $id);

        if($this->db->execute()) {
            // Update session if the user is updating their own profile
            if(isset($_SESSION['user_id']) && $_SESSION['user_id'] == $id) {
                $_SESSION['user_name'] = $name;
            }
            return true;
        } else {
            return false;
        }
    }

    public function createResetToken($email) {
        $this->db->query('SELECT * FROM users WHERE email = :email');
        $this->db->bind(':email', $email);
        $user = $this->db->single();

        if(!$user) {
            return false;
        }

        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $this->db->query('UPDATE users SET reset_token = :token, reset_expires = :expires WHERE id = :id');
        $this->db->bind(':token', $token);
        $this->db->bind(':expires', $expires);
        $this->db->bind(':id', $user['id']);

        if($this->db->execute()) {
            return $token;
        } else {
            return false;
        }
    }

    public function verifyResetToken($token) {
        $this->db->query('SELECT * FROM users WHERE reset_token = :token AND reset_expires > NOW()');
        $this->db->bind(':token', $token);
        $user = $this->db->single();

        return $user ? $user['id'] : false;
    }

    public function resetPassword($user_id, $password) {
    // Hash the password before storing
    $hashed = password_hash($password, PASSWORD_BCRYPT);
    $this->db->query('UPDATE users SET password_hashed = :password, reset_token = NULL, reset_expires = NULL WHERE id = :id');
    $this->db->bind(':password', $hashed);
        $this->db->bind(':id', $user_id);

        return $this->db->execute();
    }

    /**
     * الحصول على معلومات المتصفح
     */
    private function getBrowserInfo() {
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        $browser = "Unknown";

        if (preg_match('/MSIE/i', $user_agent) || preg_match('/Trident/i', $user_agent)) {
            $browser = "Internet Explorer";
        } elseif (preg_match('/Firefox/i', $user_agent)) {
            $browser = "Mozilla Firefox";
        } elseif (preg_match('/Chrome/i', $user_agent)) {
            if (preg_match('/Edge/i', $user_agent)) {
                $browser = "Microsoft Edge";
            } elseif (preg_match('/Edg/i', $user_agent)) {
                $browser = "Microsoft Edge (Chromium)";
            } elseif (preg_match('/OPR/i', $user_agent)) {
                $browser = "Opera";
            } else {
                $browser = "Google Chrome";
            }
        } elseif (preg_match('/Safari/i', $user_agent)) {
            $browser = "Safari";
        } elseif (preg_match('/Opera/i', $user_agent)) {
            $browser = "Opera";
        }

        return $browser;
    }

    /**
     * الحصول على معلومات نظام التشغيل
     */
    private function getOSInfo() {
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        $os = "Unknown";

        if (preg_match('/win/i', $user_agent)) {
            if (preg_match('/Windows NT 10.0/i', $user_agent)) {
                $os = "Windows 10";
            } elseif (preg_match('/Windows NT 6.3/i', $user_agent)) {
                $os = "Windows 8.1";
            } elseif (preg_match('/Windows NT 6.2/i', $user_agent)) {
                $os = "Windows 8";
            } elseif (preg_match('/Windows NT 6.1/i', $user_agent)) {
                $os = "Windows 7";
            } elseif (preg_match('/Windows NT 6.0/i', $user_agent)) {
                $os = "Windows Vista";
            } elseif (preg_match('/Windows NT 5.1/i', $user_agent)) {
                $os = "Windows XP";
            } else {
                $os = "Windows";
            }
        } elseif (preg_match('/mac/i', $user_agent)) {
            $os = "Mac OS";
        } elseif (preg_match('/linux/i', $user_agent)) {
            $os = "Linux";
        } elseif (preg_match('/Android/i', $user_agent)) {
            $os = "Android";
        } elseif (preg_match('/iPhone/i', $user_agent)) {
            $os = "iOS";
        } elseif (preg_match('/iPad/i', $user_agent)) {
            $os = "iPadOS";
        }

        return $os;
    }

    /**
     * الحصول على معلومات الجهاز
     */
    private function getDeviceInfo() {
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        $device = "Desktop";

        if (preg_match('/Mobile/i', $user_agent)) {
            $device = "Mobile";

            if (preg_match('/iPhone/i', $user_agent)) {
                $device = "iPhone";
            } elseif (preg_match('/iPad/i', $user_agent)) {
                $device = "iPad";
            } elseif (preg_match('/Android/i', $user_agent)) {
                if (preg_match('/Tablet/i', $user_agent)) {
                    $device = "Android Tablet";
                } else {
                    $device = "Android Phone";
                }
            }
        } elseif (preg_match('/Tablet/i', $user_agent)) {
            $device = "Tablet";
        }

        return $device;
    }

    /**
     * حفظ بيانات تسجيل الدخول في قاعدة البيانات
     */
    private function saveLoginData($user_id, $technical_info) {
        // التحقق من وجود جدول login_logs
        $this->createLoginLogsTableIfNotExists();

        // حفظ بيانات تسجيل الدخول
        $this->db->query('INSERT INTO login_logs (user_id, ip_address, user_agent, browser, os, device, login_time)
                         VALUES (:user_id, :ip_address, :user_agent, :browser, :os, :device, :login_time)');

        $this->db->bind(':user_id', $user_id);
        $this->db->bind(':ip_address', $technical_info['ip_address']);
        $this->db->bind(':user_agent', $technical_info['user_agent']);
        $this->db->bind(':browser', $technical_info['browser']);
        $this->db->bind(':os', $technical_info['os']);
        $this->db->bind(':device', $technical_info['device']);
        $this->db->bind(':login_time', $technical_info['login_time']);

        return $this->db->execute();
    }

    /**
     * إنشاء جدول login_logs إذا لم يكن موجوداً
     */
    private function createLoginLogsTableIfNotExists() {
        $this->db->query('CREATE TABLE IF NOT EXISTS login_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            ip_address VARCHAR(50) NOT NULL,
            user_agent TEXT NOT NULL,
            browser VARCHAR(100) NOT NULL,
            os VARCHAR(100) NOT NULL,
            device VARCHAR(100) NOT NULL,
            login_time DATETIME NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )');

        return $this->db->execute();
    }

    /**
     * حفظ بيانات التسجيل في قاعدة البيانات
     */
    private function saveRegistrationData($user_id, $technical_info) {
        // التحقق من وجود جدول registration_logs
        $this->createRegistrationLogsTableIfNotExists();

        // حفظ بيانات التسجيل
        $this->db->query('INSERT INTO registration_logs (user_id, ip_address, user_agent, browser, os, device, registration_time)
                         VALUES (:user_id, :ip_address, :user_agent, :browser, :os, :device, :registration_time)');

        $this->db->bind(':user_id', $user_id);
        $this->db->bind(':ip_address', $technical_info['ip_address']);
        $this->db->bind(':user_agent', $technical_info['user_agent']);
        $this->db->bind(':browser', $technical_info['browser']);
        $this->db->bind(':os', $technical_info['os']);
        $this->db->bind(':device', $technical_info['device']);
        $this->db->bind(':registration_time', $technical_info['registration_time']);

        return $this->db->execute();
    }

    /**
     * إنشاء جدول registration_logs إذا لم يكن موجوداً
     */
    private function createRegistrationLogsTableIfNotExists() {
        $this->db->query('CREATE TABLE IF NOT EXISTS registration_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            ip_address VARCHAR(50) NOT NULL,
            user_agent TEXT NOT NULL,
            browser VARCHAR(100) NOT NULL,
            os VARCHAR(100) NOT NULL,
            device VARCHAR(100) NOT NULL,
            registration_time DATETIME NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )');

        return $this->db->execute();
    }

    public function logout() {
        // Clear all session variables
        $_SESSION = array();

        // If it's desired to kill the session, also delete the session cookie.
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        // Finally, destroy the session.
        session_destroy();
        return true;
    }
}