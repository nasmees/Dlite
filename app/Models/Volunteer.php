<?php
/**
 * Volunteer directory and profile data
 */

class Volunteer
{
    public static function skillOptions(): array
    {
        return [
            'first_aid' => 'ปฐมพยาบาล',
            'rescue' => 'กู้ชีพ / กู้ภัย',
            'boat_rescue' => 'กู้ภัยทางน้ำ',
            'medical' => 'บุคลากรทางการแพทย์',
            'logistics' => 'ขนส่ง / ลำเลียง',
            'communications' => 'สื่อสาร',
        ];
    }

    public static function equipmentOptions(): array
    {
        return [
            'boat' => 'เรือ',
            'life_jacket' => 'เสื้อชูชีพ',
            'first_aid_kit' => 'ชุดปฐมพยาบาล',
            'vehicle' => 'รถ',
            'radio' => 'วิทยุสื่อสาร',
            'water_pump' => 'เครื่องสูบน้ำ',
        ];
    }

    public static function all(array $filters = []): array
    {
        $db = Database::getInstance();
        $where = ["u.role = 'volunteer'"];
        $params = [];

        if (!empty($filters['q'])) {
            $where[] = '(u.name LIKE ? OR u.phone LIKE ? OR o.name LIKE ? OR a.name LIKE ?)';
            $query = '%' . $filters['q'] . '%';
            array_push($params, $query, $query, $query, $query);
        }
        if (!empty($filters['skill'])) {
            $where[] = "instr(COALESCE(vp.skills_json, '[]'), ?) > 0";
            $params[] = '"' . $filters['skill'] . '"';
        }

        $stmt = $db->prepare("
            SELECT u.id, u.name, u.phone, u.is_active, u.created_at,
                   vp.organization_id, vp.area_id,
                   COALESCE(vp.availability, 'available') AS availability,
                   COALESCE(vp.skills_json, '[]') AS skills_json,
                   COALESCE(vp.equipment_json, '[]') AS equipment_json,
                   COALESCE(vp.is_sample, 0) AS is_sample,
                   o.name AS organization_name,
                   a.name AS area_name
            FROM users u
            LEFT JOIN volunteer_profiles vp ON vp.user_id = u.id
            LEFT JOIN organizations o ON o.id = vp.organization_id
            LEFT JOIN areas a ON a.id = vp.area_id
            WHERE " . implode(' AND ', $where) . "
            ORDER BY u.is_active DESC, u.name COLLATE NOCASE
        ");
        $stmt->execute($params);
        $rows = $stmt->fetchAll();
        foreach ($rows as &$row) {
            $row['skills'] = json_decode($row['skills_json'], true) ?: [];
            $row['equipment'] = json_decode($row['equipment_json'], true) ?: [];
        }
        unset($row);
        return $rows;
    }

    public static function saveProfile(int $userId, array $data): void
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            INSERT INTO volunteer_profiles
                (user_id, organization_id, area_id, availability, skills_json, equipment_json, is_sample)
            VALUES (?, ?, ?, ?, ?, ?, 0)
            ON CONFLICT(user_id) DO UPDATE SET
                organization_id = excluded.organization_id,
                area_id = excluded.area_id,
                availability = excluded.availability,
                skills_json = excluded.skills_json,
                equipment_json = excluded.equipment_json,
                is_sample = 0,
                updated_at = datetime('now','localtime')
        ");
        $stmt->execute([
            $userId,
            $data['organization_id'],
            $data['area_id'],
            $data['availability'],
            json_encode($data['skills'], JSON_UNESCAPED_UNICODE),
            json_encode($data['equipment'], JSON_UNESCAPED_UNICODE),
        ]);
    }

    /**
     * Current user's volunteer profile row (availability etc.) — null fields defaulted
     */
    public static function myProfile(int $userId): array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT availability, organization_id, area_id, skills_json, equipment_json
            FROM volunteer_profiles WHERE user_id = ?
        ");
        $stmt->execute([$userId]);
        $row = $stmt->fetch() ?: [];
        $row['availability'] = $row['availability'] ?? 'available';
        $row['skills'] = json_decode($row['skills_json'] ?? '[]', true) ?: [];
        $row['equipment'] = json_decode($row['equipment_json'] ?? '[]', true) ?: [];
        return $row;
    }

    /**
     * Active volunteers — used for the "transfer case" picker
     */
    public static function options(): array
    {
        $db = Database::getInstance();
        return $db->query("
            SELECT u.id, u.name
            FROM users u
            LEFT JOIN volunteer_profiles vp ON vp.user_id = u.id
            WHERE u.role = 'volunteer' AND u.is_active = 1
            ORDER BY u.name COLLATE NOCASE
        ")->fetchAll();
    }

    public static function setAvailability(int $userId, string $availability): bool
    {
        $db = Database::getInstance();
        return $db->prepare("
            INSERT INTO volunteer_profiles (user_id, availability)
            VALUES (?, ?)
            ON CONFLICT(user_id) DO UPDATE SET
                availability = excluded.availability,
                updated_at = datetime('now','localtime')
        ")->execute([$userId, $availability]);
    }
}
