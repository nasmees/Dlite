<?php
/**
 * Help Model — volunteer offers of assistance for a need
 */

class Help
{
    public static function create(array $data): int
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            INSERT INTO helps (need_id, helper_name, helper_phone, helper_org, help_type, notes)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            (int)$data['need_id'],
            $data['helper_name'] ?? null,
            $data['helper_phone'] ?? null,
            $data['helper_org'] ?? null,
            $data['help_type'] ?? null,
            $data['notes'] ?? null,
        ]);
        return (int)$db->lastInsertId();
    }

    public static function forNeed(int $needId): array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT h.*, u.name as user_name
            FROM helps h
            LEFT JOIN users u ON u.phone = h.helper_phone
            WHERE h.need_id = ?
            ORDER BY h.created_at DESC
        ");
        $stmt->execute([$needId]);
        return $stmt->fetchAll();
    }

    public static function helpTypes(): array
    {
        return [
            'food' => 'อาหาร / น้ำดื่ม',
            'medicine' => 'ยา / เวชภัณฑ์',
            'vehicle' => 'ยานพาหนะ / ขนส่ง',
            'volunteer' => 'อาสาสมัคร',
            'shelter' => 'ที่พัก',
            'other' => 'อื่นๆ',
        ];
    }
}
