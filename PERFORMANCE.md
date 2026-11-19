# Performance Test Report — Dlite

ทดสอบเมื่อ: 19 พ.ย. 2026

## สภาพแวดล้อมทดสอบ

- **Server:** Apache (Laragon) บน Windows
- **Runtime:** PHP 8.3.33 (ZTS) + SQLite (WAL mode)
- **เครื่องมือ:** curl_multi load test (PHP script), ไม่ใช่การจำลอง
- **Endpoint ที่ทดสอบ:** `/` (หน้าแรก), `/api/needs-summary`, `/track`, static CSS

## ผลการทดสอบ

| Endpoint | Concurrent | Requests | Req/sec | Avg | p50 | p95 | Errors |
|---|---|---|---|---|---|---|---|
| `/` หน้าแรก | 20 | 100 | 399 | 48ms | 25ms | 245ms | 0 |
| `/` หน้าแรก | 50 | 200 | 399 | 123ms | 119ms | 160ms | 0 |
| `/` หน้าแรก | 64 | 300 | 270 | 229ms | 143ms | 592ms | 0 |
| `/` หน้าแรก | 100 | 300 | 38.7 | 1.07s | 142ms | **7.73s** | 0 |
| `/api/needs-summary` | 50 | 200 | 508 | 96ms | 92ms | 157ms | 0 |
| `/api/needs-summary` | 100 | 300 | 38.6 | 1.06s | 140ms | 7.72s | 0 |
| `/track` | 50 | 150 | 460 | 108ms | 103ms | 128ms | 0 |
| `assets/css/app.css` (static) | 100 | 200 | 26.3 | 1.49s | 176ms | 7.57s | 0 |

## สิ่งที่พบ

**แอปไม่ใช่คอขวด — เซิร์ฟเวอร์เป็นตัวจำกัด**

ไฟล์ CSS static ก็พังที่ concurrency 100 เหมือนหน้า PHP ทุกหน้า แปลว่าติดเพดาน
**Apache worker thread limit** (Windows `mpm_winnt` ค่าเริ่มต้น `ThreadsPerChild=64`)
ไม่ใช่ PHP logic หรือ SQLite

## สรุปความจุโดยประมาณ

| สภาพแวดล้อม | ความจุโดยประมาณ |
|---|---|
| เครื่องนี้ (Apache/PHP local) | ~64 concurrent connections, ~400–500 req/s |
| แปลเป็น user พร้อมกัน | ~1,500–3,000 คน (1 คน ≈ 1 req / 2–5 วินาที) |
| Shared hosting ทั่วไป | ~50–200 concurrent users, ~20–80 req/s |

## ข้อจำกัดที่ทราบ

- **SQLite single-writer** — อ่านพร้อมกันได้ดี (WAL) แต่ POST พร้อมกันหลายร้อย/วิ
  อาจเจอ `database is locked`; file cache 15 วินาทีช่วยลด read load แล้ว
- **ไม่มี OPcache ในการทดสอบ** — เปิดแล้วน่าจะเร็วขึ้น 2–3 เท่าสำหรับ PHP
- **Apache thread ceiling** — ปรับ `ThreadsPerChild`/`MaxRequestWorkers` ได้บน
  เครื่องตัวเอง; บน shared hosting ปรับไม่ได้

## คำแนะนำเพิ่มประสิทธิภาพ

1. **เปิด OPcache** — มี `.user.ini` ตัวอย่างที่ root (ใช้ได้กับ PHP-FPM/LiteSpeed)
2. **ปรับ Apache** — `ThreadsPerChild`, `MaxRequestWorkers` (Windows) หรือย้ายไป
   Linux + nginx/php-fpm
3. **Asset CDN** — เสิร์ฟ CSS/JS/รูปผ่าน CDN ลดโหลด origin
4. **HTTP caching headers** สำหรับ static assets
5. **ย้าย DB** — ถ้า write traffic สูงมาก (หลายร้อย POST/วิ) พิจารณา MySQL/PostgreSQL

## วิธีทำซ้ำการทดสอบ

ใช้สคริปต์ load test แบบ curl_multi ยิง concurrent requests ไปยัง endpoint ที่ต้องการ
วัดเวลาเฉลี่ย, percentile (p50/p95/p99), throughput (req/s) และ error rate
