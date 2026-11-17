<?php
/**
 * Announcement Model — broadcast messages to site visitors / staff
 */

class Announcement
{
    public const LEVELS = ['info', 'warning', 'critical'];
    public const AUDIENCES = ['public', 'staff', 'all'];

    public static function all(): array
    {
        $db = Database::getInstance();
        return $db->query("
            SELECT a.*, u.name AS author_name
            FROM announcements a
            LEFT JOIN users u ON u.id = a.created_by
            ORDER BY a.id DESC
        ")->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM announcements WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Latest announcement visible to a viewer context.
     * $ctx: 'public' (anonymous) or 'staff' (logged in)
     */
    public static function latestFor(string $ctx): ?array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT * FROM announcements
            WHERE audience = 'all' OR audience = ?
            ORDER BY id DESC
            LIMIT 1
        ");
        $stmt->execute([$ctx]);
        return $stmt->fetch() ?: null;
    }

    public static function create(array $data): int
    {
        $db = Database::getInstance();
        $db->prepare("
            INSERT INTO announcements (title, body, level, audience, created_by)
            VALUES (?, ?, ?, ?, ?)
        ")->execute([
            trim($data['title'] ?? ''),
            trim($data['body'] ?? ''),
            $data['level'] ?? 'info',
            $data['audience'] ?? 'all',
            $data['created_by'] ?? null,
        ]);
        return (int)$db->lastInsertId();
    }

    public static function delete(int $id): bool
    {
        $db = Database::getInstance();
        return (bool)$db->prepare("DELETE FROM announcements WHERE id = ?")->execute([$id]);
    }
}
