<?php
/**
 * Area Model
 */

class Area
{
    public static function all(): array
    {
        $db = Database::getInstance();
        return $db->query("SELECT * FROM areas ORDER BY province, name")->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM areas WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function create(array $data): int
    {
        $db = Database::getInstance();
        $db->prepare("INSERT INTO areas (name, province, district, subdistrict, lat, lng) VALUES (?,?,?,?,?,?)")
           ->execute([
               trim($data['name'] ?? ''),
               trim($data['province'] ?? '') ?: null,
               trim($data['district'] ?? '') ?: null,
               trim($data['subdistrict'] ?? '') ?: null,
               isset($data['lat']) && $data['lat'] !== '' ? (float)$data['lat'] : null,
               isset($data['lng']) && $data['lng'] !== '' ? (float)$data['lng'] : null,
           ]);
        return (int)$db->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $db = Database::getInstance();
        return (bool)$db->prepare("UPDATE areas SET name=?, province=?, district=?, subdistrict=?, lat=?, lng=? WHERE id=?")
           ->execute([
               trim($data['name'] ?? ''),
               trim($data['province'] ?? '') ?: null,
               trim($data['district'] ?? '') ?: null,
               trim($data['subdistrict'] ?? '') ?: null,
               isset($data['lat']) && $data['lat'] !== '' ? (float)$data['lat'] : null,
               isset($data['lng']) && $data['lng'] !== '' ? (float)$data['lng'] : null,
               $id,
           ]);
    }

    public static function delete(int $id): bool
    {
        $db = Database::getInstance();
        // Detach needs first so reports keep their address text
        $db->prepare("UPDATE needs SET area_id = NULL WHERE area_id = ?")->execute([$id]);
        return (bool)$db->prepare("DELETE FROM areas WHERE id = ?")->execute([$id]);
    }

    /** Areas used by at least one need — cannot be deleted safely */
    public static function usageCount(int $id): int
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT COUNT(*) FROM needs WHERE area_id = ?");
        $stmt->execute([$id]);
        return (int)$stmt->fetchColumn();
    }
}
