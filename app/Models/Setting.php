<?php
/**
 * Site settings — simple key/value store (white-label: app name, logo)
 */
class Setting
{
    private static array $cache = [];

    public static function get(string $key, ?string $default = null): ?string
    {
        if (array_key_exists($key, self::$cache)) {
            return self::$cache[$key];
        }
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT svalue FROM settings WHERE skey = ?");
        $stmt->execute([$key]);
        $v = $stmt->fetchColumn();
        return self::$cache[$key] = $v === false ? $default : (string)$v;
    }

    public static function set(string $key, ?string $value): void
    {
        $db = Database::getInstance();
        if ($value === null || $value === '') {
            $db->prepare("DELETE FROM settings WHERE skey = ?")->execute([$key]);
        } else {
            $db->prepare("INSERT OR REPLACE INTO settings (skey, svalue) VALUES (?, ?)")
               ->execute([$key, $value]);
        }
        self::$cache[$key] = $value;
    }
}
