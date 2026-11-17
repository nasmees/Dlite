# Deep South Care Lite

ระบบประสานงานช่วยเหลือผู้ประสบภัยน้ำท่วมชายแดนใต้  
**เล็ก • เบา • รวดเร็ว** | Pure PHP 8.2+ + SQLite + Tailwind + Leaflet

## คุณสมบัติหลัก

- หน้าแรก = ฟอร์มขอความช่วยเหลือ (เน้นมือถือ ใช้เวลา < 1 นาที)
- Quick-select สำหรับสถานการณ์ทั่วไป (น้ำท่วม / ต้องอพยพ / เจ็บป่วย)
- แผนที่แสดงจุดขอความช่วยเหลือ (Leaflet + OpenStreetMap)
- รายการคำขอ + รายละเอียด + pagination + รูปภาพ (lazy load)
- **ติดตามสถานะด้วยรหัส** (`/track`) — ผู้แจ้งเช็คความคืบหน้าได้เอง
- แดชบอร์ดสำหรับอาสา/แอดมิน พร้อม action อัปเดตสถานะเร็ว
- อาสาเสนอความช่วยเหลือ (อาหาร/น้ำ/ยา/ยานพาหนะ/อื่นๆ)
- จัดการผู้ใช้ — แอดมินสร้างบัญชีอาสา/ผู้ดูได้
- รองรับ Offline (localStorage draft + queue เมื่อออฟไลน์)
- ฟอนต์ Kanit, โทนสีฟ้า, ไม่ใช้ emoji icons
- Progressive Enhancement — ใช้งานได้แม้ JavaScript ปิด
- Deploy ง่ายบน Shared Hosting Apache ผ่าน FTP

## โครงสร้าง

```
deep-south-care-lite/
├── index.php               ← จุดเข้าใช้งาน (อยู่ที่ root แล้ว)
├── .htaccess
├── assets/
├── uploads/                ← รูปที่อัปโหลด (ต้อง writable)
├── app/
│   ├── config.php
│   ├── Controllers/
│   ├── Models/
│   ├── Views/
│   └── Core/
├── storage/                ← database.sqlite + cache + logs (ต้อง writable)
└── README.md
```

## การติดตั้งบน Shared Hosting (Apache)

1. อัปโหลดทั้งโฟลเดอร์ผ่าน FTP ขึ้น hosting
2. **มี `index.php` อยู่ที่ root ของโปรเจกต์แล้ว** — ไม่ต้องย้าย Document Root
   - ถ้าอัปเข้าโฟลเดอร์ย่อย เช่น `public_html/flood/` เปิด `https://yourdomain.com/flood/` ได้เลย
3. ให้สิทธิ์โฟลเดอร์ `storage/` และ `uploads/` เป็น writable (chmod 755 หรือ 775)
4. เปิดเว็บไซต์ — ระบบจะสร้างฐานข้อมูล SQLite + cache อัตโนมัติครั้งแรก
5. ต้องเปิด `mod_rewrite` (โฮสต์ส่วนใหญ่เปิดอยู่แล้ว)

### ค่าเริ่มต้นผู้ดูแลระบบ

- เบอร์โทร: `0800000000`
- รหัสผ่าน: `admin123`

**เปลี่ยนรหัสผ่านทันทีหลังเข้าสู่ระบบ** ผ่านหน้าจัดการบัญชีผู้ใช้

### สร้างบัญชีอาสาสมัคร

1. Login ด้วยบัญชี admin แล้วเปิดหน้า **อาสาสมัคร**
2. กรอกข้อมูลบัญชี พื้นที่ องค์กร ทักษะ และอุปกรณ์
3. ส่งเบอร์โทรและรหัสผ่านให้ทีมงาน — login ที่หน้า `/login`

หน้าแอดมินประกอบด้วย **ศูนย์บัญชาการ** (เคสเร่งด่วนและแผนที่สด), **อาสาสมัคร** และ **องค์กรเครือข่าย** ข้อมูลตัวอย่างที่ระบบติดตั้งให้อัตโนมัติจะมีป้ายกำกับว่าเป็นข้อมูลตัวอย่าง และบัญชีตัวอย่างจะถูกปิดใช้งาน

## UX/UI (jitasa.care style)

- หน้าแรก `/` = แผนที่สด (shared live map) + ปุ่ม "แจ้งเหตุ" เปิด bottom-sheet form
- Scenario cards ขนาดใหญ่ (น้ำท่วม/อพยพ/SOS/เจ็บป่วย/อื่นๆ) — SOS ทำให้ปุ่มส่งเป็นสีแดง
- GPS auto-detect + ช่อง "จุดสังเกต" แบบสั้น + รูปบีบอัดฝั่ง client (<300KB ผ่าน Canvas)
- Overlay loading → เครื่องหมายถูกสีเขียว → บันทึกรายการลง localStorage
- Banner "เรื่องแจ้งของคุณ" ด้านบนทุกหน้า + ปุ่ม center บนแผนที่ + pin กระพริบของตัวเอง
- พินแผนที่: แดง=รอช่วย, เหลือง=กำลังเข้าพื้นที่, เขียว=สำเร็จ
- Light mode เท่านั้น โทน Deep Trust Blue `#0F3D6E` + Royal `#2563EB`
- หน้า Admin ใช้โทน Shadcn/UI สีน้ำเงิน `#004C97` บนพื้นหลัง `#F8FAFC`
- `/request` = ฟอร์มฉบับเต็ม (fallback สำหรับ no-JS)

## Performance

- **SQLite WAL Mode** + `busy_timeout` 5s + `synchronous=NORMAL` — อ่าน/เขียนพร้อมกันได้ดี
- **File cache** สำหรับหน้ารายการ (TTL 15 วินาที, เก็บใน `storage/cache/`) — ลดการยิง DB ตอน traffic สูง
- **รูปภาพ** ถูก resize เหลือ max 1600px + re-encode (JPEG q80) อัตโนมัติเมื่อมี GD extension
- **Lazy load** รูปและแผนที่ (โหลดเมื่อ scroll เข้า viewport)
- **OPcache**: แนะนำสร้างไฟล์ `.user.ini` ที่ root:
  ```ini
  opcache.enable=1
  opcache.memory_consumption=64
  opcache.max_accelerated_files=10000
  ```

## ความต้องการ

- PHP 8.2+ (แนะนำ 8.2 หรือ 8.3)
- ส่วนขยาย: pdo_sqlite, sqlite3, fileinfo, **gd** (สำหรับ compress รูป — ไม่มีก็ใช้ได้ แต่รูปจะใหญ่กว่า)
- Apache + mod_rewrite
- ไม่ต้องใช้ Composer / Node / MySQL

## สถาปัตยกรรม

- Front Controller (`index.php` ที่ root)
- Thin MVC
- SQLite WAL Mode + busy_timeout
- File cache สำหรับ public lists
- ไม่ใช้ ORM — SQL ตรง
- Tailwind CDN + Vanilla JS
- Progressive Enhancement (ฟอร์มทำงานได้แม้ไม่มี JS)

## Offline

- บันทึก draft ฟอร์มใน localStorage ขณะกรอก
- ถ้าออฟไลน์ตอนกดส่ง → เก็บ queue ไว้ส่งเมื่อกลับมาออนไลน์ (รูปภาพไม่ถูก queue — ต้องส่งใหม่ตอนออนไลน์)

## ปรับแต่ง

- พื้นที่เริ่มต้น: แก้ไข seed ใน `app/Core/Database.php`
- สี: แก้ `brand` ใน Tailwind config ภายใน layout
- ขนาดอัปโหลดรูป: `UPLOAD_MAX_SIZE` ใน `app/config.php`
- Cache TTL: `CACHE_TTL` ใน `app/config.php` (วินาที)
- Login throttle: `MAX_ATTEMPTS`/`LOCK_SECONDS` ใน `AuthController`

## Routes

| Method | Path | คำอธิบาย |
|--------|------|----------|
| GET | `/` | ฟอร์มแจ้งความต้องการ |
| POST | `/request` | ส่งคำขอ (JSON หรือ form ปกติ) |
| GET | `/success` | หน้าสำเร็จ + รหัส |
| GET | `/track?code=` | ติดตามสถานะด้วยรหัส |
| GET | `/map` | แผนที่ |
| GET | `/needs` | รายการ (pagination) |
| GET | `/needs/{id}` | รายละเอียด |
| POST | `/needs/{id}/status` | อัปเดตสถานะ (login) |
| POST | `/needs/{id}/help` | เสนอความช่วยเหลือ (login) |
| GET | `/api/points` | ข้อมูลจุดบนแผนที่ (JSON) |
| GET | `/api/needs-summary` | จำนวนตามสถานะ (JSON) |
| GET/POST | `/login`, `/logout` | Auth |
| GET | `/admin` | แดชบอร์ด (login) |
| GET | `/admin/map/points` | จุดบนแผนที่สำหรับทีมงาน (login) |
| GET/POST | `/admin/volunteers` | จัดการอาสาสมัคร (admin) |
| POST | `/admin/volunteers/{id}/availability` | เปลี่ยนสถานะพร้อมงาน (admin) |
| GET/POST | `/admin/organizations` | จัดการองค์กรเครือข่าย (admin) |
| POST | `/admin/organizations/{id}/toggle` | เปิด/ปิดใช้งานองค์กร (admin) |
| GET/POST | `/admin/users*` | จัดการผู้ใช้ (admin) |

## License

ใช้งานภายในทีม / โครงการช่วยเหลือภัยพิบัติ
