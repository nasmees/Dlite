<?php
/**
 * SQLite Database connection with WAL mode
 */

class Database
{
    private static ?PDO $instance = null;

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $dir = dirname(DB_PATH);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            $isNew = !file_exists(DB_PATH);

            self::$instance = new PDO('sqlite:' . DB_PATH, null, null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);

            // UTF-8 encoding for Thai text
            self::$instance->exec('PRAGMA encoding = "UTF-8";');
            // WAL mode for better concurrency
            self::$instance->exec('PRAGMA journal_mode = WAL;');
            self::$instance->exec('PRAGMA busy_timeout = ' . DB_BUSY_TIMEOUT . ';');
            self::$instance->exec('PRAGMA synchronous = NORMAL;');
            self::$instance->exec('PRAGMA foreign_keys = ON;');
            self::$instance->exec('PRAGMA cache_size = -64000;'); // 64MB

            if ($isNew) {
                self::migrate();
            }
        }
        return self::$instance;
    }

    public static function migrate(): void
    {
        $db = self::getInstance();

        $db->exec("
            CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                phone TEXT UNIQUE,
                password_hash TEXT,
                role TEXT NOT NULL DEFAULT 'volunteer' CHECK(role IN ('admin','volunteer','viewer')),
                is_active INTEGER DEFAULT 1,
                created_at TEXT DEFAULT (datetime('now','localtime')),
                updated_at TEXT DEFAULT (datetime('now','localtime'))
            );

            CREATE TABLE IF NOT EXISTS areas (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                province TEXT,
                district TEXT,
                subdistrict TEXT,
                lat REAL,
                lng REAL,
                created_at TEXT DEFAULT (datetime('now','localtime'))
            );

            CREATE TABLE IF NOT EXISTS needs (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                code TEXT UNIQUE,
                reporter_name TEXT NOT NULL,
                reporter_phone TEXT,
                area_id INTEGER,
                address TEXT,
                lat REAL,
                lng REAL,
                people_count INTEGER DEFAULT 1,
                need_types TEXT, -- JSON array: food,water,medicine,shelter,evacuation,other
                description TEXT,
                urgency TEXT DEFAULT 'normal' CHECK(urgency IN ('low','normal','high','critical')),
                status TEXT DEFAULT 'pending' CHECK(status IN ('pending','accepted','in_progress','completed','cancelled')),
                assigned_to INTEGER,
                photo_path TEXT,
                is_public INTEGER DEFAULT 1,
                created_at TEXT DEFAULT (datetime('now','localtime')),
                updated_at TEXT DEFAULT (datetime('now','localtime')),
                FOREIGN KEY (area_id) REFERENCES areas(id),
                FOREIGN KEY (assigned_to) REFERENCES users(id)
            );

            CREATE TABLE IF NOT EXISTS helps (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                need_id INTEGER NOT NULL,
                helper_name TEXT,
                helper_phone TEXT,
                helper_org TEXT,
                help_type TEXT,
                notes TEXT,
                status TEXT DEFAULT 'offered' CHECK(status IN ('offered','accepted','done','cancelled')),
                created_at TEXT DEFAULT (datetime('now','localtime')),
                FOREIGN KEY (need_id) REFERENCES needs(id) ON DELETE CASCADE
            );

            CREATE TABLE IF NOT EXISTS updates (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                need_id INTEGER NOT NULL,
                user_id INTEGER,
                old_status TEXT,
                new_status TEXT,
                message TEXT,
                created_at TEXT DEFAULT (datetime('now','localtime')),
                FOREIGN KEY (need_id) REFERENCES needs(id) ON DELETE CASCADE,
                FOREIGN KEY (user_id) REFERENCES users(id)
            );

            CREATE TABLE IF NOT EXISTS media (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                need_id INTEGER,
                file_path TEXT NOT NULL,
                file_type TEXT,
                created_at TEXT DEFAULT (datetime('now','localtime')),
                FOREIGN KEY (need_id) REFERENCES needs(id) ON DELETE CASCADE
            );

            CREATE INDEX IF NOT EXISTS idx_needs_status ON needs(status);
            CREATE INDEX IF NOT EXISTS idx_needs_created ON needs(created_at);
            CREATE INDEX IF NOT EXISTS idx_needs_area ON needs(area_id);
            CREATE INDEX IF NOT EXISTS idx_needs_lat_lng ON needs(lat, lng);
            CREATE INDEX IF NOT EXISTS idx_updates_need ON updates(need_id);
        ");

        // Seed default admin (password: admin123)
        $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE role = 'admin'");
        $stmt->execute();
        if ((int)$stmt->fetchColumn() === 0) {
            $hash = password_hash('admin123', PASSWORD_DEFAULT);
            $db->prepare("INSERT INTO users (name, phone, password_hash, role) VALUES (?, ?, ?, ?)")
               ->execute(['ผู้ดูแลระบบ', '0800000000', $hash, 'admin']);
        }

        // Seed some sample areas for deep south
        $stmt = $db->prepare("SELECT COUNT(*) FROM areas");
        $stmt->execute();
        if ((int)$stmt->fetchColumn() === 0) {
            $areas = [
                ['ยะลา', 'ยะลา', 'เมืองยะลา', null, 6.541, 101.281],
                ['ปัตตานี', 'ปัตตานี', 'เมืองปัตตานี', null, 6.869, 101.250],
                ['นราธิวาส', 'นราธิวาส', 'เมืองนราธิวาส', null, 6.426, 101.823],
                ['สงขลา', 'สงขลา', 'หาดใหญ่', null, 7.008, 100.475],
                ['สตูล', 'สตูล', 'เมืองสตูล', null, 6.623, 100.067],
            ];
            $ins = $db->prepare("INSERT INTO areas (name, province, district, subdistrict, lat, lng) VALUES (?,?,?,?,?,?)");
            foreach ($areas as $a) {
                $ins->execute($a);
            }
        }
    }
}
