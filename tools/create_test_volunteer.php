<?php
/**
 * ONE-TIME TEST ACCOUNT CREATOR — upload, visit once in browser, then DELETE this file.
 * Creates a volunteer login for testing the /volunteer workspace.
 *
 * Login: phone 0812345678 / password volunteer123
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/config.php';

spl_autoload_register(function ($class) {
    $paths = [
        APP_PATH . '/Core/' . $class . '.php',
        APP_PATH . '/Models/' . $class . '.php',
        APP_PATH . '/Controllers/' . $class . '.php',
        APP_PATH . '/Services/' . $class . '.php',
    ];
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

Auth::start();
Database::getInstance();

$phone = '0812345678';
$password = 'volunteer123';

if (User::phoneExists($phone)) {
    echo "มีบัญชีนี้อยู่แล้ว — phone: {$phone} / pass: {$password} (หรือตั้งรหัสใหม่ใน /admin/volunteers)";
    exit;
}

$userId = User::create([
    'name' => 'อาสาสมัครทดสอบ',
    'phone' => $phone,
    'password' => $password,
    'role' => 'volunteer',
]);

Volunteer::saveProfile($userId, [
    'organization_id' => null,
    'area_id' => null,
    'availability' => 'available',
    'skills' => [],
    'equipment' => [],
]);

echo "สร้างบัญชีอาสาสมัครสำเร็จ<br>"
   . "เบอร์: <b>{$phone}</b><br>"
   . "รหัสผ่าน: <b>{$password}</b><br><br>"
   . "<b>ลบบไฟล์นี้ออกจาก server ทันทีหลังใช้งาน</b>";
