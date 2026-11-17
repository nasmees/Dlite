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
            self::migrateAdminDirectory();
            self::migrateDemoCases();
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

    private static function migrateAdminDirectory(): void
    {
            $db = self::$instance;
            $db->exec("
                CREATE TABLE IF NOT EXISTS organizations (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name TEXT NOT NULL,
                    logo_url TEXT,
                    verified INTEGER NOT NULL DEFAULT 0,
                    contact_name TEXT,
                    contact_phone TEXT,
                    contact_email TEXT,
                    description TEXT,
                    is_active INTEGER NOT NULL DEFAULT 1,
                    is_sample INTEGER NOT NULL DEFAULT 0,
                    created_at TEXT DEFAULT (datetime('now','localtime')),
                    updated_at TEXT DEFAULT (datetime('now','localtime'))
                );

                CREATE TABLE IF NOT EXISTS volunteer_profiles (
                    user_id INTEGER PRIMARY KEY,
                    organization_id INTEGER,
                    area_id INTEGER,
                    availability TEXT NOT NULL DEFAULT 'available'
                        CHECK(availability IN ('available', 'busy')),
                    skills_json TEXT NOT NULL DEFAULT '[]',
                    equipment_json TEXT NOT NULL DEFAULT '[]',
                    is_sample INTEGER NOT NULL DEFAULT 0,
                    updated_at TEXT DEFAULT (datetime('now','localtime')),
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                    FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE SET NULL,
                    FOREIGN KEY (area_id) REFERENCES areas(id) ON DELETE SET NULL
                );

                CREATE INDEX IF NOT EXISTS idx_volunteer_profiles_org
                    ON volunteer_profiles(organization_id);
                CREATE INDEX IF NOT EXISTS idx_volunteer_profiles_area
                    ON volunteer_profiles(area_id);

                CREATE TABLE IF NOT EXISTS announcements (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    title TEXT NOT NULL,
                    body TEXT NOT NULL,
                    level TEXT NOT NULL DEFAULT 'info'
                        CHECK(level IN ('info','warning','critical')),
                    audience TEXT NOT NULL DEFAULT 'all'
                        CHECK(audience IN ('public','staff','all')),
                    created_by INTEGER,
                    created_at TEXT DEFAULT (datetime('now','localtime')),
                    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
                );

                CREATE TABLE IF NOT EXISTS admin_migrations (
                    version TEXT PRIMARY KEY,
                    applied_at TEXT DEFAULT (datetime('now','localtime'))
                );
            ");

            // Ensure needs.scenario exists (added after initial release)
            $needCols = $db->query("PRAGMA table_info(needs)")->fetchAll(PDO::FETCH_COLUMN, 1);
            if (!in_array('scenario', $needCols, true)) {
                $db->exec("ALTER TABLE needs ADD COLUMN scenario TEXT");
            }

            $version = 'admin_directory_demo_v1';
            $stmt = $db->prepare("SELECT 1 FROM admin_migrations WHERE version = ?");
            $stmt->execute([$version]);
            if ($stmt->fetchColumn()) {
                return;
            }

            $db->beginTransaction();
            try {
                $organizations = [
                    ['เครือข่ายอาสากู้ภัย (ตัวอย่าง)', 'เครือข่ายอาสาสมัครช่วยเหลือและกู้ภัยในพื้นที่', '02-000-0001'],
                    ['ทีมปฐมพยาบาลชุมชน (ตัวอย่าง)', 'ทีมสนับสนุนการปฐมพยาบาลและดูแลผู้ประสบภัย', '02-000-0002'],
                    ['กลุ่มสนับสนุนทางน้ำ (ตัวอย่าง)', 'ทีมสนับสนุนการเดินทางและอุปกรณ์ทางน้ำ', '02-000-0003'],
                ];
                $insertOrganization = $db->prepare("
                    INSERT INTO organizations
                        (name, contact_name, contact_phone, description, is_sample)
                    VALUES (?, 'ผู้ประสานงานตัวอย่าง', ?, ?, 1)
                ");
                $organizationIds = [];
                foreach ($organizations as [$name, $description, $phone]) {
                    $insertOrganization->execute([$name, $phone, $description]);
                    $organizationIds[] = (int)$db->lastInsertId();
                }

                $areaIds = $db->query("SELECT id FROM areas ORDER BY id")->fetchAll(PDO::FETCH_COLUMN);
                $samples = [
                    ['อาสาสมัครตัวอย่าง 01', 0, 'available', ['first_aid', 'rescue'], ['first_aid_kit']],
                    ['อาสาสมัครตัวอย่าง 02', 1, 'busy', ['medical'], ['vehicle']],
                    ['อาสาสมัครตัวอย่าง 03', 2, 'available', ['boat_rescue'], ['boat', 'life_jacket']],
                    ['อาสาสมัครตัวอย่าง 04', 0, 'busy', ['logistics'], ['radio']],
                ];
                $insertUser = $db->prepare("
                    INSERT INTO users (name, password_hash, role, is_active)
                    VALUES (?, ?, 'volunteer', 0)
                ");
                $insertProfile = $db->prepare("
                    INSERT INTO volunteer_profiles
                        (user_id, organization_id, area_id, availability, skills_json, equipment_json, is_sample)
                    VALUES (?, ?, ?, ?, ?, ?, 1)
                ");
                foreach ($samples as $index => [$name, $areaIndex, $availability, $skills, $equipment]) {
                    $insertUser->execute([$name, password_hash(bin2hex(random_bytes(32)), PASSWORD_DEFAULT)]);
                    $userId = (int)$db->lastInsertId();
                    $areaId = $areaIds ? (int)$areaIds[$areaIndex % count($areaIds)] : null;
                    $insertProfile->execute([
                        $userId,
                        $organizationIds[$index % count($organizationIds)],
                        $areaId,
                        $availability,
                        json_encode($skills, JSON_UNESCAPED_UNICODE),
                        json_encode($equipment, JSON_UNESCAPED_UNICODE),
                    ]);
                }

                $db->prepare("INSERT INTO admin_migrations (version) VALUES (?)")->execute([$version]);
                $db->commit();
            } catch (Throwable $e) {
                if ($db->inTransaction()) {
                    $db->rollBack();
                }
                throw $e;
            }
    }

    /**
     * Seed demo cases for testing — runs once (versioned).
     * Delete row 'demo_cases_v1' from admin_migrations to re-seed.
     */
    private static function migrateDemoCases(): void
    {
        $db = self::$instance;

        $stmt = $db->prepare("SELECT 1 FROM admin_migrations WHERE version = ?");
        $stmt->execute(['demo_cases_v1']);
        if ($stmt->fetchColumn()) {
            return;
        }

        $areaIds = $db->query("SELECT id FROM areas ORDER BY id")->fetchAll(PDO::FETCH_COLUMN);
        if (!$areaIds) {
            return;
        }

        // [name, areaIdx, address, lat, lng, people, types, desc, urgency, status, scenario, ageHours]
        $cases = [
            ['สมชาย ใจดี',       1, 'ถนนเจริญ ต.สะบารัง อ.เมืองปัตตานี', 6.8690, 101.2505, 12, ['food','water','shelter'], 'น้ำท่วมสูงถึงเอว รถเล็กสัญจรไม่ได้ ต้องการเรือและอาหารด่วน', 'high',     'pending',     'flood',    1],
            ['ประยูร พูนสุข',     2, 'บ้านเลขที่ 12/3 ต.บางเก่า อ.เมืองนราธิวาส', 6.4261, 101.8230, 30, ['shelter','evacuation'], 'บ้านถูกน้ำพัด ต้องอพยพด่วน มีเด็กและผู้สูงอายุ', 'critical', 'pending',     'evac',     2],
            ['ราตรี แสงจันทร์',    0, 'ซอย 5 ถ.ตลาดเก่า อ.เมืองยะลา', 6.5410, 101.2810, 5,  ['medicine','evacuation'], 'มีผู้ป่วยติดอยู่ในบ้าน น้ำท่วมสูง โทรไม่ติด', 'critical', 'in_progress', 'sos',      3],
            ['วิไล รักษ์ดี',       1, 'บ้านหลังวัดมัสยิด ต.จะรัง อ.เมืองปัตตานี', 6.8702, 101.2520, 3, ['medicine'], 'เด็กเป็นไข้สูง มีแผลที่เท้า ต้องการยาฟื้นฟู', 'high',     'accepted',    'medical',  5],
            ['อนันต์ ช่วยเหลือ',   4, 'ชุมชนริมน้ำ ต.สตอ อ.เมืองสตูล', 6.6235, 100.0675, 20, ['food','water'], 'ขาดแคลนอาหารและน้ำดื่ม 2 วันแล้ว', 'normal',   'completed',   'supplies', 26],
            ['กิตติศักดิ์ รอดพ้น', 4, 'ป่าสักระวัง ต.ควนกาหลง อ.ควนโดน', 6.6400, 100.0900, 8,  ['other'], 'ไม้ล้มทับบ้าน 2 หลัง ต้องการช่างซ่อมแซม', 'normal',   'pending',     'other',    8],
            ['สมหญิง รักชาติ',    0, 'ถนนสายน้ำ อ.ธารโต จ.ยะลา', 6.6100, 101.3500, 15, ['food','water'], 'น้ำเริ่มลด แต่ยังขาดอาหารและน้ำสะอาด', 'high',     'in_progress', 'flood',    12],
            ['ประเสริฐ มั่งมี',    2, 'ตลาดน้ำ อ.รือเสาะ จ.นราธิวาส', 6.1900, 101.7500, 50, ['shelter','evacuation'], 'อพยพสำเร็จ 50 คน ไปศูนย์พักพิงวัด', 'critical', 'completed',   'evac',     30],
            ['เลิศล้ำ พูนสุข',     2, 'บ้านริมคลอง ต.บาเจาะ อ.บาเจาะ', 6.5500, 101.9000, 2,  ['medicine','evacuation'], 'หญิงตั้งครรภ์เจ็บท้องใกล้คลอด ต้องการเรือพยาบาล', 'critical', 'pending',     'sos',      0.5],
            ['สมบูรณ์ พูนสุข',     0, 'ซอยวัดถ้ำ จ.ยะลา', 6.5450, 101.2900, 4,  ['medicine'], 'ขอเวชภัณฑ์สำหรับผู้สูงอายุประจำบ้าน', 'high',     'cancelled',   'medical',  20],
        ];

        $insNeed = $db->prepare("
            INSERT INTO needs
                (code, reporter_name, reporter_phone, area_id, address, lat, lng,
                 people_count, need_types, description, urgency, status, scenario, is_public, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?)
        ");
        $insUpdate = $db->prepare("
            INSERT INTO updates (need_id, new_status, message, created_at)
            VALUES (?, ?, ?, ?)
        ");

        $statusFlow = [
            'accepted'    => [['accepted', 'รับเรื่องแล้ว']],
            'in_progress' => [['accepted', 'รับเรื่องแล้ว'], ['in_progress', 'เริ่มดำเนินการ']],
            'completed'   => [['accepted', 'รับเรื่องแล้ว'], ['in_progress', 'เริ่มดำเนินการ'], ['completed', 'ปิดเรื่องสำเร็จ']],
            'cancelled'   => [['cancelled', 'ยกเลิกคำขอ']],
        ];

        $db->beginTransaction();
        try {
            foreach ($cases as $i => $c) {
                [$name, $areaIdx, $address, $lat, $lng, $people, $types, $desc, $urgency, $status, $scenario, $ageHours] = $c;
                $created = date('Y-m-d H:i:s', time() - (int)round($ageHours * 3600));
                $insNeed->execute([
                    'DSC-DEMO' . str_pad((string)($i + 1), 2, '0', STR_PAD_LEFT),
                    $name,
                    '08' . str_pad((string)(10000000 + $i * 137), 8, '0', STR_PAD_LEFT),
                    (int)$areaIds[$areaIdx % count($areaIds)],
                    $address,
                    $lat, $lng,
                    $people,
                    json_encode($types, JSON_UNESCAPED_UNICODE),
                    $desc,
                    $urgency, $status, $scenario,
                    $created,
                ]);
                $needId = (int)$db->lastInsertId();

                $insUpdate->execute([$needId, 'pending', 'สร้างคำขอใหม่', $created]);
                $flow = $statusFlow[$status] ?? [];
                foreach ($flow as $step => [$st, $msg]) {
                    $ts = date('Y-m-d H:i:s', strtotime($created) + ($step + 1) * 3600);
                    $insUpdate->execute([$needId, $st, $msg, $ts]);
                }
            }

            $db->prepare("INSERT INTO admin_migrations (version) VALUES ('demo_cases_v1')")->execute();
            $db->commit();
        } catch (Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $e;
        }
    }
}
