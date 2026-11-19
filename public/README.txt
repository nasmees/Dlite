Dlite
=====================

Document root ควรเป็นโฟลเดอร์หลักของโปรเจกต์ (ที่มี index.php)

สำหรับ Apache + PHP 8.2+:
1. อัปโหลดทั้งโฟลเดอร์ขึ้น hosting
2. chmod storage/ และ uploads/ เป็น 755 หรือ 775
3. เปิด mod_rewrite
4. ระบบสร้าง SQLite DB + cache อัตโนมัติครั้งแรก

ดูรายละเอียดเต็มที่ README.md
