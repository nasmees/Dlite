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

    public static function setActive(int $id, bool $active): bool
    {
        $db = Database::getInstance();
        return $db->prepare("UPDATE users SET is_active = ? WHERE id = ?")
                  ->execute([$active ? 1 : 0, $id]);
    }
}
