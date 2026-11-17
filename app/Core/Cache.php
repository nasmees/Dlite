<?php
/**
 * Simple file-based cache for shared hosting
 * Stores serialized data in STORAGE_PATH/cache/
 */

class Cache
{
    private static function dir(): string
    {
        $dir = STORAGE_PATH . '/cache';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        return $dir;
    }

    private static function file(string $key): string
    {
        return self::dir() . '/' . md5($key) . '.cache';
    }

    public static function get(string $key): mixed
    {
        $file = self::file($key);
        if (!is_file($file)) {
            return null;
        }
        $raw = @file_get_contents($file);
        if ($raw === false || strlen($raw) < 11) {
            return null;
        }
        $expires = (int)substr($raw, 0, 10);
        if ($expires > 0 && $expires < time()) {
            @unlink($file);
            return null;
        }
        $data = substr($raw, 10);
        $value = @unserialize($data);
        return $value === false && $data !== 'b:0;' ? null : $value;
    }

    public static function set(string $key, mixed $value, int $ttl = 15): bool
    {
        $expires = time() + $ttl;
        $payload = str_pad((string)$expires, 10, '0', STR_PAD_LEFT) . serialize($value);
        $tmp = self::file($key) . '.' . uniqid('', true) . '.tmp';
        if (@file_put_contents($tmp, $payload, LOCK_EX) === false) {
            return false;
        }
        return @rename($tmp, self::file($key));
    }

    public static function delete(string $key): void
    {
        $file = self::file($key);
        if (is_file($file)) {
            @unlink($file);
        }
    }

    /**
     * Clear all cached entries — call after any write to needs/users
     */
    public static function flush(): void
    {
        $dir = self::dir();
        foreach (glob($dir . '/*.cache') ?: [] as $f) {
            @unlink($f);
        }
    }

    public static function remember(string $key, int $ttl, callable $callback): mixed
    {
        $cached = self::get($key);
        if ($cached !== null) {
            return $cached;
        }
        $value = $callback();
        self::set($key, $value, $ttl);
        return $value;
    }
}
