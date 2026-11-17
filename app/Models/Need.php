<?php
/**
 * Need Model
 */

class Need
{
    public static function create(array $data): int
    {
        $db = Database::getInstance();
        $code = Helper::generateCode();

        $stmt = $db->prepare("
            INSERT INTO needs (
                code, reporter_name, reporter_phone, area_id, address,
                lat, lng, people_count, need_types, description,
                urgency, status, photo_path
            ) VALUES (
                :code, :reporter_name, :reporter_phone, :area_id, :address,
                :lat, :lng, :people_count, :need_types, :description,
                :urgency, 'pending', :photo_path
            )
        ");

        $stmt->execute([
            ':code' => $code,
            ':reporter_name' => $data['reporter_name'],
            ':reporter_phone' => $data['reporter_phone'] ?? null,
            ':area_id' => $data['area_id'] ?? null,
            ':address' => $data['address'] ?? null,
            ':lat' => $data['lat'] ?? null,
            ':lng' => $data['lng'] ?? null,
            ':people_count' => (int)($data['people_count'] ?? 1),
            ':need_types' => is_array($data['need_types'] ?? null)
                ? json_encode($data['need_types'], JSON_UNESCAPED_UNICODE)
                : ($data['need_types'] ?? '[]'),
            ':description' => $data['description'] ?? null,
            ':urgency' => $data['urgency'] ?? 'normal',
            ':photo_path' => $data['photo_path'] ?? null,
        ]);

        $id = (int)$db->lastInsertId();

        // Log update
        $db->prepare("INSERT INTO updates (need_id, new_status, message) VALUES (?, 'pending', ?)")
           ->execute([$id, 'สร้างคำขอใหม่']);

        return $id;
    }

    public static function find(int $id): ?array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT n.*, a.name as area_name, a.province, a.district,
                   u.name as assigned_name
            FROM needs n
            LEFT JOIN areas a ON a.id = n.area_id
            LEFT JOIN users u ON u.id = n.assigned_to
            WHERE n.id = ?
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row) {
            $row['need_types'] = json_decode($row['need_types'] ?? '[]', true) ?: [];
        }
        return $row ?: null;
    }

    public static function findByCode(string $code): ?array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT id FROM needs WHERE code = ?");
        $stmt->execute([$code]);
        $row = $stmt->fetch();
        return $row ? self::find((int)$row['id']) : null;
    }

    public static function list(array $filters = [], int $limit = 20, int $offset = 0, bool $publicOnly = false): array
    {
        $db = Database::getInstance();
        $where = ['1=1'];
        $params = [];

        if ($publicOnly) {
            $where[] = 'n.is_public = 1';
        }
        if (!empty($filters['status'])) {
            $where[] = 'n.status = ?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['urgency'])) {
            $where[] = 'n.urgency = ?';
            $params[] = $filters['urgency'];
        }
        if (!empty($filters['q'])) {
            $where[] = '(n.reporter_name LIKE ? OR n.address LIKE ? OR n.description LIKE ? OR n.code LIKE ?)';
            $q = '%' . $filters['q'] . '%';
            $params = array_merge($params, [$q, $q, $q, $q]);
        }
        if (isset($filters['has_location']) && $filters['has_location']) {
            $where[] = 'n.lat IS NOT NULL AND n.lng IS NOT NULL';
        }

        $sql = "
            SELECT n.*, a.name as area_name, a.province
            FROM needs n
            LEFT JOIN areas a ON a.id = n.area_id
            WHERE " . implode(' AND ', $where) . "
            ORDER BY 
                CASE n.urgency 
                    WHEN 'critical' THEN 1 
                    WHEN 'high' THEN 2 
                    WHEN 'normal' THEN 3 
                    ELSE 4 
                END,
                n.created_at DESC
            LIMIT {$limit} OFFSET {$offset}
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();
        foreach ($rows as &$r) {
            $r['need_types'] = json_decode($r['need_types'] ?? '[]', true) ?: [];
        }
        return $rows;
    }

    /**
     * Single GROUP BY query for status counts (used on home page summary).
     */
    public static function statusCounts(bool $publicOnly = false): array
    {
        $db = Database::getInstance();
        $where = $publicOnly ? 'WHERE is_public = 1' : '';
        $rows = $db->query("SELECT status, COUNT(*) AS c FROM needs {$where} GROUP BY status")->fetchAll();
        $counts = ['all' => 0, 'pending' => 0, 'in_progress' => 0, 'completed' => 0, 'cancelled' => 0];
        foreach ($rows as $r) {
            $counts['all'] += (int)$r['c'];
            if (isset($counts[$r['status']])) $counts[$r['status']] = (int)$r['c'];
        }
        return $counts;
    }

    public static function count(array $filters = [], bool $publicOnly = false): int
    {
        $db = Database::getInstance();
        $where = ['1=1'];
        $params = [];
        if ($publicOnly) {
            $where[] = 'is_public = 1';
        }
        if (!empty($filters['status'])) {
            $where[] = 'status = ?';
            $params[] = $filters['status'];
        }
        $sql = "SELECT COUNT(*) FROM needs WHERE " . implode(' AND ', $where);
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public static function updateStatus(int $id, string $status, ?int $userId = null, ?string $message = null): bool
    {
        $db = Database::getInstance();
        $need = self::find($id);
        if (!$need) return false;

        $db->prepare("UPDATE needs SET status = ?, updated_at = datetime('now','localtime') WHERE id = ?")
           ->execute([$status, $id]);

        $db->prepare("INSERT INTO updates (need_id, user_id, old_status, new_status, message) VALUES (?,?,?,?,?)")
           ->execute([$id, $userId, $need['status'], $status, $message]);

        return true;
    }

    public static function assign(int $id, int $userId): bool
    {
        $db = Database::getInstance();
        $db->prepare("UPDATE needs SET assigned_to = ?, updated_at = datetime('now','localtime') WHERE id = ?")
           ->execute([$userId, $id]);
        return true;
    }

    public static function flushCache(): void
    {
        Cache::flush();
    }

    public static function mapPoints(array $filters = [], bool $publicOnly = true): array
    {
        $filters['has_location'] = true;
        $rows = self::list($filters, 500, 0, $publicOnly);
        $points = [];
        foreach ($rows as $r) {
            if ($r['lat'] && $r['lng']) {
                $points[] = [
                    'id' => $r['id'],
                    'code' => $r['code'],
                    'lat' => (float)$r['lat'],
                    'lng' => (float)$r['lng'],
                    'status' => $r['status'],
                    'urgency' => $r['urgency'],
                    'name' => $r['reporter_name'],
                    'types' => $r['need_types'],
                    'people' => $r['people_count'],
                ];
            }
        }
        return $points;
    }
}
