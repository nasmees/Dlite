<?php
/**
 * User Model
 */

class User
{
    public static function findByPhone(string $phone): ?array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM users WHERE phone = ? AND is_active = 1");
        $stmt->execute([$phone]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Find by phone regardless of active status — used to detect pending accounts
     */
    public static function findByPhoneAny(string $phone): ?array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM users WHERE phone = ?");
        $stmt->execute([$phone]);
        return $stmt->fetch() ?: null;
    }

    public static function find(int $id): ?array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function phoneExists(string $phone, ?int $exceptId = null): bool
    {
        $db = Database::getInstance();
        $sql = "SELECT 1 FROM users WHERE phone = ?";
        $params = [$phone];
        if ($exceptId !== null) {
            $sql .= " AND id != ?";
            $params[] = $exceptId;
        }
        $stmt = $db->prepare($sql . " LIMIT 1");
        $stmt->execute($params);
        return (bool)$stmt->fetchColumn();
    }

    public static function authenticate(string $phone, string $password): ?array
    {
        $user = self::findByPhone($phone);
        if ($user && password_verify($password, $user['password_hash'])) {
            return $user;
        }
        return null;
    }

    public static function create(array $data): int
    {
        $db = Database::getInstance();
        $hash = password_hash($data['password'], PASSWORD_DEFAULT);
        $stmt = $db->prepare("
            INSERT INTO users (name, phone, password_hash, role)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['name'],
            $data['phone'],
            $hash,
            $data['role'] ?? 'volunteer',
        ]);
        return (int)$db->lastInsertId();
    }

    public static function all(): array
    {
        $db = Database::getInstance();
        return $db->query("SELECT id, name, phone, role, is_active, created_at FROM users ORDER BY id")->fetchAll();
    }

    /**
     * Back-office accounts only (admin + viewer) — volunteers have their own directory
     */
    public static function staff(): array
    {
        $db = Database::getInstance();
        return $db->query("SELECT id, name, phone, role, is_active, created_at FROM users WHERE role IN ('admin','viewer') ORDER BY id")->fetchAll();
    }

    public static function update(int $id, array $data): bool
    {
        $db = Database::getInstance();
        $sets = [];
        $params = [];
        if (isset($data['name'])) { $sets[] = 'name = ?'; $params[] = $data['name']; }
        if (isset($data['phone'])) { $sets[] = 'phone = ?'; $params[] = $data['phone']; }
        if (isset($data['role'])) {
            if (!in_array($data['role'], ['admin', 'volunteer', 'viewer'], true)) return false;
            $sets[] = 'role = ?';
            $params[] = $data['role'];
        }
        if (isset($data['password']) && $data['password'] !== '') {
            $sets[] = 'password_hash = ?';
            $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        if (!$sets) return false;
        $params[] = $id;
        return $db->prepare("UPDATE users SET " . implode(', ', $sets) . " WHERE id = ?")->execute($params);
    }

    /**
     * Permanently delete a user — unassigns their cases first (FK constraints)
     */
    public static function delete(int $id): bool
    {
        $db = Database::getInstance();
        $db->beginTransaction();
        try {
            $db->prepare("UPDATE needs SET assigned_to = NULL WHERE assigned_to = ?")->execute([$id]);
            $db->prepare("UPDATE updates SET user_id = NULL WHERE user_id = ?")->execute([$id]);
            $ok = $db->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
            $db->commit();
            return (bool)$ok;
        } catch (Throwable $e) {
            $db->rollBack();
            return false;
        }
    }

    public static function setActive(int $id, bool $active): bool
    {
        $db = Database::getInstance();
        return $db->prepare("UPDATE users SET is_active = ? WHERE id = ?")
                  ->execute([$active ? 1 : 0, $id]);
    }

    public static function setRememberToken(int $id, ?string $tokenHash): bool
    {
        $db = Database::getInstance();
        return $db->prepare("UPDATE users SET remember_token = ? WHERE id = ?")
                  ->execute([$tokenHash, $id]);
    }

    /**
     * Resolve a remember-me cookie value ("uid:token") to a user row.
     * Token is stored hashed — cookie holds the raw token.
     */
    public static function findByRememberCookie(string $cookie): ?array
    {
        $parts = explode(':', $cookie, 2);
        if (count($parts) !== 2) return null;
        [$uid, $token] = $parts;
        if (!ctype_digit($uid) || strlen($token) < 20) return null;
        $user = self::find((int)$uid);
        if (!$user || empty($user['remember_token']) || !($user['is_active'] ?? 0)) return null;
        if (!hash_equals($user['remember_token'], hash('sha256', $token))) return null;
        return $user;
    }
}
