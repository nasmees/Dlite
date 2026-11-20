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
                urgency, status, photo_path, scenario, client_token
            ) VALUES (
                :code, :reporter_name, :reporter_phone, :area_id, :address,
                :lat, :lng, :people_count, :need_types, :description,
                :urgency, 'pending', :photo_path, :scenario, :client_token
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
            ':scenario' => $data['scenario'] ?? null,
            ':client_token' => $data['client_token'] ?? null,
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

    /**
     * Find cases by reporter phone — tolerant of +66/66/0 prefixes and separators.
     * Matches on the last 9 digits so formatting differences don't matter.
     */
    public static function findByPhone(string $phone): array
    {
        $digits = preg_replace('/\D/', '', $phone);
        if ($digits === '') return [];
        if (str_starts_with($digits, '66')) {
            $digits = '0' . substr($digits, 2);
        }
        $tail = substr($digits, -9);
        if (strlen($tail) < 9) return [];

        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT id FROM needs
            WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(reporter_phone,'-',''),' ',''),'+',''),'()',''),'_', '') LIKE ?
            ORDER BY created_at DESC
            LIMIT 10
        ");
        $stmt->execute(['%' . $tail . '%']);
        $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $out = [];
        foreach ($ids as $id) {
            $n = self::find((int)$id);
            if ($n) $out[] = $n;
        }
        return $out;
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
        if (!empty($filters['scenario'])) {
            $where[] = 'n.scenario = ?';
            $params[] = $filters['scenario'];
        }
        if (!empty($filters['q'])) {
            $where[] = '(n.reporter_name LIKE ? OR n.address LIKE ? OR n.description LIKE ? OR n.code LIKE ?)';
            $q = '%' . $filters['q'] . '%';
            $params = array_merge($params, [$q, $q, $q, $q]);
        }
        if (isset($filters['has_location']) && $filters['has_location']) {
            $where[] = 'n.lat IS NOT NULL AND n.lng IS NOT NULL';
        }
        if (!empty($filters['assigned_to'])) {
            $where[] = 'n.assigned_to = ?';
            $params[] = (int)$filters['assigned_to'];
        }
        if (!empty($filters['unassigned'])) {
            $where[] = 'n.assigned_to IS NULL';
        }
        if (!empty($filters['active'])) {
            $where[] = "n.status IN ('pending','accepted','in_progress')";
        }
        if (!empty($filters['statuses']) && is_array($filters['statuses'])) {
            $statuses = array_values(array_intersect($filters['statuses'], ['pending','accepted','in_progress','completed','cancelled']));
            if ($statuses) {
                $where[] = 'n.status IN (' . implode(',', array_fill(0, count($statuses), '?')) . ')';
                $params = array_merge($params, $statuses);
            }
        }

        $orderBy = "n.created_at DESC";
        if (($filters['sort'] ?? '') !== 'latest') {
            $orderBy = "
                CASE n.urgency
                    WHEN 'critical' THEN 1
                    WHEN 'high' THEN 2
                    WHEN 'normal' THEN 3
                    ELSE 4
                END,
                n.created_at DESC
            ";
        }

        $sql = "
            SELECT n.*, a.name as area_name, a.district, a.province, u.name as assigned_name,
                (SELECT u2.photo_path FROM updates u2
                 WHERE u2.need_id = n.id AND u2.photo_path IS NOT NULL AND u2.photo_path <> ''
                 ORDER BY u2.id DESC LIMIT 1) AS latest_work_photo
            FROM needs n
            LEFT JOIN areas a ON a.id = n.area_id
            LEFT JOIN users u ON u.id = n.assigned_to
            WHERE " . implode(' AND ', $where) . "
            ORDER BY {$orderBy}
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
     * Pending cases with high/critical urgency — homepage "urgent incidents" section
     */
    public static function urgentCases(bool $publicOnly = false, int $limit = 5): array
    {
        $db = Database::getInstance();
        $where = ["n.status = 'pending'", "n.urgency IN ('critical','high')"];
        if ($publicOnly) {
            $where[] = 'n.is_public = 1';
        }
        $sql = "
            SELECT n.*, a.name as area_name, a.district, a.province
            FROM needs n
            LEFT JOIN areas a ON a.id = n.area_id
            WHERE " . implode(' AND ', $where) . "
            ORDER BY CASE n.urgency WHEN 'critical' THEN 1 ELSE 2 END, n.created_at DESC
            LIMIT " . (int)$limit . "
        ";
        $rows = $db->query($sql)->fetchAll();
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
        $counts = ['all' => 0, 'pending' => 0, 'accepted' => 0, 'in_progress' => 0, 'completed' => 0, 'cancelled' => 0];
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
        if (!empty($filters['urgency'])) {
            $where[] = 'urgency = ?';
            $params[] = $filters['urgency'];
        }
        if (!empty($filters['scenario'])) {
            $where[] = 'scenario = ?';
            $params[] = $filters['scenario'];
        }
        if (!empty($filters['q'])) {
            $where[] = '(reporter_name LIKE ? OR address LIKE ? OR description LIKE ? OR code LIKE ?)';
            $q = '%' . $filters['q'] . '%';
            $params = array_merge($params, [$q, $q, $q, $q]);
        }
        if (!empty($filters['assigned_to'])) {
            $where[] = 'assigned_to = ?';
            $params[] = (int)$filters['assigned_to'];
        }
        if (!empty($filters['unassigned'])) {
            $where[] = 'assigned_to IS NULL';
        }
        if (!empty($filters['active'])) {
            $where[] = "status IN ('pending','accepted','in_progress')";
        }
        if (!empty($filters['statuses']) && is_array($filters['statuses'])) {
            $statuses = array_values(array_intersect($filters['statuses'], ['pending','accepted','in_progress','completed','cancelled']));
            if ($statuses) {
                $where[] = 'status IN (' . implode(',', array_fill(0, count($statuses), '?')) . ')';
                $params = array_merge($params, $statuses);
            }
        }
        $sql = "SELECT COUNT(*) FROM needs WHERE " . implode(' AND ', $where);
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public static function countToday(): int
    {
        $db = Database::getInstance();
        return (int)$db->query("SELECT COUNT(*) FROM needs WHERE created_at >= date('now','localtime')")->fetchColumn();
    }

    /**
     * Update editable request fields (admin/staff correction)
     */
    public static function updateDetails(int $id, array $data): bool
    {
        $db = Database::getInstance();
        $fields = [];
        $params = [];
        $map = [
            'reporter_name' => 'reporter_name',
            'reporter_phone' => 'reporter_phone',
            'area_id' => 'area_id',
            'address' => 'address',
            'lat' => 'lat',
            'lng' => 'lng',
            'people_count' => 'people_count',
            'need_types' => 'need_types',
            'description' => 'description',
            'urgency' => 'urgency',
            'scenario' => 'scenario',
        ];
        foreach ($map as $key => $col) {
            if (array_key_exists($key, $data)) {
                $fields[] = "{$col} = ?";
                $params[] = $data[$key];
            }
        }
        if (!$fields) return false;
        $params[] = $id;
        $stmt = $db->prepare("UPDATE needs SET " . implode(', ', $fields) . ", updated_at = datetime('now','localtime') WHERE id = ?");
        $stmt->execute($params);
        return $stmt->rowCount() > 0 || self::find($id) !== null;
    }

    public static function updateStatus(int $id, string $status, ?int $userId = null, ?string $message = null, ?string $photoPath = null): bool
    {
        $db = Database::getInstance();
        $need = self::find($id);
        if (!$need) return false;

        $db->prepare("UPDATE needs SET status = ?, updated_at = datetime('now','localtime') WHERE id = ?")
           ->execute([$status, $id]);

        $db->prepare("INSERT INTO updates (need_id, user_id, old_status, new_status, message, photo_path) VALUES (?,?,?,?,?,?)")
           ->execute([$id, $userId, $need['status'], $status, $message, $photoPath]);

        return true;
    }

    /**
     * Append a work-log entry (message and/or photo) without changing status.
     * Public timeline entry — visible to the reporter.
     */
    public static function addNote(int $id, ?int $userId, string $message, ?string $photoPath = null): bool
    {
        $need = self::find($id);
        if (!$need) return false;

        Database::getInstance()
            ->prepare("INSERT INTO updates (need_id, user_id, old_status, new_status, message, photo_path) VALUES (?,?,?,?,?,?)")
            ->execute([$id, $userId, $need['status'], $need['status'], $message !== '' ? $message : null, $photoPath]);

        return true;
    }

    public static function delete(int $id): bool
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("DELETE FROM needs WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }

    public static function assign(int $id, int $userId): bool
    {
        $db = Database::getInstance();
        $db->prepare("UPDATE needs SET assigned_to = ?, updated_at = datetime('now','localtime') WHERE id = ?")
           ->execute([$userId, $id]);
        return true;
    }

    /**
     * Volunteer claims an unassigned case — atomic guard against double-claiming.
     * Allowed from pending/accepted; sets status to accepted.
     */
    public static function claimCase(int $id, int $userId, string $actorName): bool
    {
        $need = self::find($id);
        if (!$need || $need['assigned_to'] !== null) return false;
        if (!in_array($need['status'], ['pending', 'accepted'], true)) return false;

        $db = Database::getInstance();
        $db->prepare("UPDATE needs SET assigned_to = ?, status = 'accepted', updated_at = datetime('now','localtime') WHERE id = ?")
           ->execute([$userId, $id]);
        $db->prepare("INSERT INTO updates (need_id, user_id, old_status, new_status, message) VALUES (?,?,?,?,?)")
           ->execute([$id, $userId, $need['status'], 'accepted', 'อาสาสมัคร ' . $actorName . ' รับเคส']);
        return true;
    }

    /**
     * Volunteer returns their own case to the open pool.
     * Only for cases still in progress workflow (not completed/cancelled).
     */
    public static function releaseCase(int $id, int $userId, string $actorName): bool
    {
        $need = self::find($id);
        if (!$need || (int)($need['assigned_to'] ?? 0) !== $userId) return false;
        if (in_array($need['status'], ['completed', 'cancelled'], true)) return false;

        $db = Database::getInstance();
        $db->prepare("UPDATE needs SET assigned_to = NULL, status = 'pending', updated_at = datetime('now','localtime') WHERE id = ?")
           ->execute([$id]);
        $db->prepare("INSERT INTO updates (need_id, user_id, old_status, new_status, message) VALUES (?,?,?,?,?)")
           ->execute([$id, $userId, $need['status'], 'pending', 'อาสาสมัคร ' . $actorName . ' คืนเคสเข้ากองรวม']);
        return true;
    }

    /**
     * Transfer a case the actor currently owns to another volunteer.
     */
    public static function transferCase(int $id, int $fromUserId, int $toUserId, string $actorName, string $toName): bool
    {
        if ($fromUserId === $toUserId) return false;
        $need = self::find($id);
        if (!$need || (int)($need['assigned_to'] ?? 0) !== $fromUserId) return false;

        $db = Database::getInstance();
        $db->prepare("UPDATE needs SET assigned_to = ?, updated_at = datetime('now','localtime') WHERE id = ?")
           ->execute([$toUserId, $id]);
        $db->prepare("INSERT INTO updates (need_id, user_id, old_status, new_status, message) VALUES (?,?,?,?,?)")
           ->execute([$id, $fromUserId, $need['status'], $need['status'], 'อาสาสมัคร ' . $actorName . ' โอนเคสให้ ' . $toName]);
        return true;
    }

    /**
     * Counts for the volunteer dashboard — scoped to one assignee.
     */
    public static function myStats(int $userId): array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT
                SUM(CASE WHEN status IN ('accepted','in_progress') THEN 1 ELSE 0 END) AS active,
                SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) AS working,
                SUM(CASE WHEN status = 'completed' AND date(updated_at) = date('now','localtime') THEN 1 ELSE 0 END) AS done_today
            FROM needs WHERE assigned_to = ?
        ");
        $stmt->execute([$userId]);
        $row = $stmt->fetch() ?: [];
        return [
            'active' => (int)($row['active'] ?? 0),
            'working' => (int)($row['working'] ?? 0),
            'done_today' => (int)($row['done_today'] ?? 0),
        ];
    }

    /**
     * Aggregate stats for the admin dashboard charts
     */
    public static function reportStats(): array
    {
        $db = Database::getInstance();
        $daily = array_reverse($db->query("
            SELECT date(created_at) AS d, COUNT(*) AS c
            FROM needs
            GROUP BY d ORDER BY d DESC LIMIT 14
        ")->fetchAll());
        $byStatus = $db->query("SELECT status, COUNT(*) AS c FROM needs GROUP BY status")->fetchAll(PDO::FETCH_KEY_PAIR);
        $byScenario = $db->query("
            SELECT COALESCE(NULLIF(scenario, ''), 'other') AS s, COUNT(*) AS c
            FROM needs GROUP BY s ORDER BY c DESC
        ")->fetchAll();
        $byProvince = $db->query("
            SELECT COALESCE(NULLIF(a.province, ''), 'ไม่ระบุ') AS p, COUNT(*) AS c
            FROM needs n LEFT JOIN areas a ON a.id = n.area_id
            GROUP BY p ORDER BY c DESC LIMIT 8
        ")->fetchAll();
        $byUrgency = $db->query("SELECT urgency, COUNT(*) AS c FROM needs GROUP BY urgency")->fetchAll(PDO::FETCH_KEY_PAIR);
        $totals = $db->query("SELECT COUNT(*) AS total, COALESCE(SUM(people_count),0) AS people FROM needs")->fetch();
        $helpCount = (int)$db->query("SELECT COUNT(*) FROM helps")->fetchColumn();
        $volunteerCount = (int)$db->query("SELECT COUNT(*) FROM users WHERE role = 'volunteer' AND is_active = 1")->fetchColumn();

        return [
            'daily' => $daily,
            'byStatus' => $byStatus,
            'byScenario' => $byScenario,
            'byProvince' => $byProvince,
            'byUrgency' => $byUrgency,
            'total' => (int)($totals['total'] ?? 0),
            'people' => (int)($totals['people'] ?? 0),
            'helpCount' => $helpCount,
            'volunteerCount' => $volunteerCount,
        ];
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
                    'place' => trim(implode(' ', array_filter([$r['district'] ?? '', $r['province'] ?? '']))),
                    'types' => $r['need_types'],
                    'people' => $r['people_count'],
                ];
            }
        }
        return $points;
    }
}
