<?php
require_once __DIR__ . '/../config/database.php';

/**
 * دوال إدارة صلاحيات المدراء
 */

// التحقق من صلاحيات المستخدم
function check_admin_permission($user_id, $permission_type) {
    $db = new Database();
    
    try {
        // التحقق من السوبر أدمن (له جميع الصلاحيات)
        $db->query("SELECT role FROM users WHERE id = :user_id AND role = 'super_admin'");
        $db->bind(':user_id', $user_id);
        $super_admin = $db->single();
        
        if ($super_admin) {
            return true;
        }
        
        // التحقق من الصلاحية المحددة
        $db->query("SELECT ap.* FROM admin_permissions ap 
                   JOIN users u ON ap.user_id = u.id 
                   WHERE ap.user_id = :user_id 
                   AND ap.permission_type = :permission_type 
                   AND ap.is_active = 1 
                   AND u.status = 'active'");
        $db->bind(':user_id', $user_id);
        $db->bind(':permission_type', $permission_type);
        $permission = $db->single();
        
        return $permission ? true : false;
    } catch (Exception $e) {
        error_log("Error checking admin permission: " . $e->getMessage());
        return false;
    }
}

// الحصول على جميع صلاحيات المستخدم
function get_user_permissions($user_id) {
    $db = new Database();
    
    try {
        // التحقق من السوبر أدمن
        $db->query("SELECT role FROM users WHERE id = :user_id");
        $db->bind(':user_id', $user_id);
        $user = $db->single();
        
        if ($user && $user['role'] === 'super_admin') {
            return ['transport', 'notifications', 'site', 'super'];
        }
        
        // الحصول على الصلاحيات المحددة
        $db->query("SELECT permission_type FROM admin_permissions 
                   WHERE user_id = :user_id AND is_active = 1");
        $db->bind(':user_id', $user_id);
        $permissions = $db->resultSet();
        
        return array_column($permissions, 'permission_type');
    } catch (Exception $e) {
        error_log("Error getting user permissions: " . $e->getMessage());
        return [];
    }
}

// منح صلاحية لمستخدم
function grant_admin_permission($user_id, $permission_type, $granted_by) {
    $db = new Database();
    
    try {
        // التحقق من أن المانح له صلاحية السوبر أدمن
        if (!check_admin_permission($granted_by, 'super')) {
            return false;
        }
        
        $db->query("INSERT INTO admin_permissions (user_id, permission_type, granted_by, is_active) 
                   VALUES (:user_id, :permission_type, :granted_by, 1)
                   ON DUPLICATE KEY UPDATE is_active = 1, granted_by = :granted_by, granted_at = NOW()");
        $db->bind(':user_id', $user_id);
        $db->bind(':permission_type', $permission_type);
        $db->bind(':granted_by', $granted_by);
        
        $result = $db->execute();
        
        if ($result) {
            // تحديث دور المستخدم
            update_user_role($user_id);
            
            // تسجيل النشاط
            log_admin_activity($granted_by, 'grant_permission', 'user', $user_id, 
                             "منح صلاحية {$permission_type} للمستخدم {$user_id}");
        }
        
        return $result;
    } catch (Exception $e) {
        error_log("Error granting admin permission: " . $e->getMessage());
        return false;
    }
}

// سحب صلاحية من مستخدم
function revoke_admin_permission($user_id, $permission_type, $revoked_by) {
    $db = new Database();
    
    try {
        // التحقق من أن الساحب له صلاحية السوبر أدمن
        if (!check_admin_permission($revoked_by, 'super')) {
            return false;
        }
        
        $db->query("UPDATE admin_permissions 
                   SET is_active = 0 
                   WHERE user_id = :user_id AND permission_type = :permission_type");
        $db->bind(':user_id', $user_id);
        $db->bind(':permission_type', $permission_type);
        
        $result = $db->execute();
        
        if ($result) {
            // تحديث دور المستخدم
            update_user_role($user_id);
            
            // تسجيل النشاط
            log_admin_activity($revoked_by, 'revoke_permission', 'user', $user_id, 
                             "سحب صلاحية {$permission_type} من المستخدم {$user_id}");
        }
        
        return $result;
    } catch (Exception $e) {
        error_log("Error revoking admin permission: " . $e->getMessage());
        return false;
    }
}

// تحديث دور المستخدم بناءً على الصلاحيات
function update_user_role($user_id) {
    $db = new Database();
    
    try {
        $permissions = get_user_permissions($user_id);
        
        if (in_array('super', $permissions)) {
            $role = 'super_admin';
        } elseif (in_array('site', $permissions)) {
            $role = 'site_admin';
        } elseif (in_array('notifications', $permissions)) {
            $role = 'notifications_admin';
        } elseif (in_array('transport', $permissions)) {
            $role = 'transport_admin';
        } else {
            $role = 'user';
        }
        
        $db->query("UPDATE users SET role = :role WHERE id = :user_id");
        $db->bind(':role', $role);
        $db->bind(':user_id', $user_id);
        
        return $db->execute();
    } catch (Exception $e) {
        error_log("Error updating user role: " . $e->getMessage());
        return false;
    }
}

// تسجيل نشاط المدير
function log_admin_activity($admin_id, $action_type, $target_type = null, $target_id = null, $description = null) {
    $db = new Database();
    
    try {
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        $db->query("INSERT INTO admin_activity_log (admin_id, action_type, target_type, target_id, description, ip_address, user_agent) 
                   VALUES (:admin_id, :action_type, :target_type, :target_id, :description, :ip_address, :user_agent)");
        $db->bind(':admin_id', $admin_id);
        $db->bind(':action_type', $action_type);
        $db->bind(':target_type', $target_type);
        $db->bind(':target_id', $target_id);
        $db->bind(':description', $description);
        $db->bind(':ip_address', $ip_address);
        $db->bind(':user_agent', $user_agent);
        
        return $db->execute();
    } catch (Exception $e) {
        error_log("Error logging admin activity: " . $e->getMessage());
        return false;
    }
}

// الحصول على قائمة المدراء
function get_admin_users() {
    $db = new Database();
    
    try {
        $db->query("SELECT u.id, u.name, u.email, u.role, u.created_at,
                          GROUP_CONCAT(ap.permission_type) as permissions
                   FROM users u
                   LEFT JOIN admin_permissions ap ON u.id = ap.user_id AND ap.is_active = 1
                   WHERE u.role IN ('transport_admin', 'notifications_admin', 'site_admin', 'super_admin')
                   GROUP BY u.id
                   ORDER BY u.role DESC, u.name");
        
        return $db->resultSet();
    } catch (Exception $e) {
        error_log("Error getting admin users: " . $e->getMessage());
        return [];
    }
}

// الحصول على سجل أنشطة المدراء
function get_admin_activity_log($limit = 50, $admin_id = null) {
    $db = new Database();
    
    try {
        $sql = "SELECT aal.*, u.name as admin_name 
                FROM admin_activity_log aal
                JOIN users u ON aal.admin_id = u.id";
        
        if ($admin_id) {
            $sql .= " WHERE aal.admin_id = :admin_id";
        }
        
        $sql .= " ORDER BY aal.created_at DESC LIMIT :limit";
        
        $db->query($sql);
        
        if ($admin_id) {
            $db->bind(':admin_id', $admin_id);
        }
        
        $db->bind(':limit', $limit);
        
        return $db->resultSet();
    } catch (Exception $e) {
        error_log("Error getting admin activity log: " . $e->getMessage());
        return [];
    }
}

// التحقق من إمكانية الوصول لصفحة إدارية
function require_admin_permission($permission_type, $redirect_url = 'index.php') {
    if (!isset($_SESSION['user_id'])) {
        redirect('login.php');
    }
    
    if (!check_admin_permission($_SESSION['user_id'], $permission_type)) {
        redirect($redirect_url);
    }
}

// الحصول على أسماء الصلاحيات بالعربية
function get_permission_name($permission_type) {
    $names = [
        'transport' => 'إدارة المواصلات',
        'notifications' => 'إدارة الإشعارات',
        'site' => 'إدارة الموقع',
        'super' => 'المدير العام'
    ];
    
    return $names[$permission_type] ?? $permission_type;
}

// الحصول على أسماء الأدوار بالعربية
function get_role_name($role) {
    $names = [
        'user' => 'مستخدم عادي',
        'transport_admin' => 'مدير المواصلات',
        'notifications_admin' => 'مدير الإشعارات',
        'site_admin' => 'مدير الموقع',
        'super_admin' => 'المدير العام'
    ];
    
    return $names[$role] ?? $role;
}

// التحقق من وجود مدير عام واحد على الأقل
function ensure_super_admin_exists() {
    $db = new Database();
    
    try {
        $db->query("SELECT COUNT(*) as count FROM users WHERE role = 'super_admin' AND status = 'active'");
        $result = $db->single();
        
        if ($result['count'] == 0) {
            // إنشاء مدير عام افتراضي إذا لم يوجد
            $db->query("SELECT id FROM users WHERE status = 'active' ORDER BY created_at ASC LIMIT 1");
            $first_user = $db->single();
            
            if ($first_user) {
                $db->query("UPDATE users SET role = 'super_admin' WHERE id = :user_id");
                $db->bind(':user_id', $first_user['id']);
                $db->execute();
                
                // إضافة صلاحية السوبر أدمن
                $db->query("INSERT IGNORE INTO admin_permissions (user_id, permission_type, is_active) 
                           VALUES (:user_id, 'super', 1)");
                $db->bind(':user_id', $first_user['id']);
                $db->execute();
                
                return $first_user['id'];
            }
        }
        
        return true;
    } catch (Exception $e) {
        error_log("Error ensuring super admin exists: " . $e->getMessage());
        return false;
    }
}
?>
