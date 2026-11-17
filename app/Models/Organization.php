<?php
/**
 * Partner organization directory
 */

class Organization
{
    public static function all(): array
    {
        $db = Database::getInstance();
        return $db->query("
            SELECT o.*, COUNT(vp.user_id) AS volunteer_count
            FROM organizations o
            LEFT JOIN volunteer_profiles vp ON vp.organization_id = o.id
            GROUP BY o.id
            ORDER BY o.is_active DESC, o.name COLLATE NOCASE
        ")->fetchAll();
    }

    public static function activeOptions(): array
    {
        $db = Database::getInstance();
        return $db->query("
            SELECT id, name FROM organizations
            WHERE is_active = 1
            ORDER BY name COLLATE NOCASE
        ")->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT o.*, COUNT(vp.user_id) AS volunteer_count
            FROM organizations o
            LEFT JOIN volunteer_profiles vp ON vp.organization_id = o.id
            WHERE o.id = ?
            GROUP BY o.id
        ");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function save(array $data, ?int $id = null): int
    {
        $db = Database::getInstance();
        if ($id === null) {
            $stmt = $db->prepare("
                INSERT INTO organizations
                    (name, logo_url, verified, contact_name, contact_phone, contact_email, description)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $data['name'],
                $data['logo_url'],
                $data['verified'],
                $data['contact_name'],
                $data['contact_phone'],
                $data['contact_email'],
                $data['description'],
            ]);
            return (int)$db->lastInsertId();
        }

        $stmt = $db->prepare("
            UPDATE organizations
            SET name = ?, logo_url = ?, verified = ?, contact_name = ?,
                contact_phone = ?, contact_email = ?, description = ?,
                updated_at = datetime('now','localtime')
            WHERE id = ?
        ");
        $stmt->execute([
            $data['name'],
            $data['logo_url'],
            $data['verified'],
            $data['contact_name'],
            $data['contact_phone'],
            $data['contact_email'],
            $data['description'],
            $id,
        ]);
        return $id;
    }

    public static function setActive(int $id, bool $active): bool
    {
        $db = Database::getInstance();
        return $db->prepare("
            UPDATE organizations
            SET is_active = ?, updated_at = datetime('now','localtime')
            WHERE id = ?
        ")->execute([$active ? 1 : 0, $id]);
    }
}
