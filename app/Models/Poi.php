<?php
/**
 * Important places (POIs): shelters, evacuation points, supply stations…
 * Admin-managed static markers shown on public maps.
 */

class Poi
{
    public static function all(bool $activeOnly = false): array
    {
        $db = Database::getInstance();
        $sql = "SELECT p.*, u.name AS created_by_name
                FROM pois p LEFT JOIN users u ON u.id = p.created_by";
        if ($activeOnly) {
            $sql .= " WHERE p.is_active = 1";
        }
        $sql .= " ORDER BY p.category, p.title";
        return $db->query($sql)->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM pois WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(array $data, ?int $userId): int
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            INSERT INTO pois (title, category, description, lat, lng, is_active, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            trim((string)($data['title'] ?? '')),
            self::cleanCategory($data['category'] ?? 'other'),
            trim((string)($data['description'] ?? '')) ?: null,
            (float)$data['lat'],
            (float)$data['lng'],
            !empty($data['is_active']) ? 1 : 0,
            $userId,
        ]);
        return (int)$db->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            UPDATE pois SET title=?, category=?, description=?, lat=?, lng=?, is_active=?,
                updated_at=datetime('now','localtime')
            WHERE id=?
        ");
        return $stmt->execute([
            trim((string)($data['title'] ?? '')),
            self::cleanCategory($data['category'] ?? 'other'),
            trim((string)($data['description'] ?? '')) ?: null,
            (float)$data['lat'],
            (float)$data['lng'],
            !empty($data['is_active']) ? 1 : 0,
            $id,
        ]);
    }

    public static function delete(int $id): bool
    {
        $db = Database::getInstance();
        return $db->prepare("DELETE FROM pois WHERE id = ?")->execute([$id]);
    }

    private static function cleanCategory(string $key): string
    {
        return array_key_exists($key, Helper::poiCategories()) ? $key : 'other';
    }
}
