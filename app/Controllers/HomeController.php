<?php
/**
 * Home / Request Help (main entry for victims)
 */

class HomeController
{
    public function index(): void
    {
        // Sectioned home — map card + status summary + latest requests
        $publicOnly = !Auth::check();
        View::render('needs/home', [
            'title' => 'Deep South Care',
            'areas' => Area::all(),
            'needTypes' => Helper::needTypes(),
            'csrf' => Auth::csrfToken(),
            'counts' => Need::statusCounts($publicOnly),
            'latest' => Need::list([], 5, 0, $publicOnly),
            'statusLabels' => Helper::statusLabels(),
            'urgencyLabels' => Helper::urgencyLabels(),
        ]);
    }

    /**
     * Standalone form page — fallback for no-JS / direct link
     */
    public function requestForm(): void
    {
        View::render('needs/request', [
            'title' => 'ขอความช่วยเหลือ',
            'areas' => Area::all(),
            'needTypes' => Helper::needTypes(),
            'csrf' => Auth::csrfToken(),
        ]);
    }

    public function store(): void
    {
        // Progressive enhancement: if request is a normal (non-JS) form POST,
        // redirect back with result instead of returning JSON
        $wantsJson = isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')
            || !empty($_POST['ajax']);

        if (!Auth::verifyCsrf($_POST['csrf_token'] ?? null)) {
            if ($wantsJson) {
                View::json(['ok' => false, 'message' => 'โทเค็นไม่ถูกต้อง กรุณารีเฟรชหน้า'], 403);
            }
            View::redirect('/?error=csrf');
        }

        $name = trim($_POST['reporter_name'] ?? '');
        $phone = trim($_POST['reporter_phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $lat = !empty($_POST['lat']) ? (float)$_POST['lat'] : null;
        $lng = !empty($_POST['lng']) ? (float)$_POST['lng'] : null;
        $people = max(1, (int)($_POST['people_count'] ?? 1));
        $types = $_POST['need_types'] ?? [];
        if (!is_array($types)) $types = [];
        $description = trim($_POST['description'] ?? '');
        $urgency = $_POST['urgency'] ?? 'normal';
        $areaId = !empty($_POST['area_id']) ? (int)$_POST['area_id'] : null;

        if ($name === '') {
            if ($wantsJson) {
                View::json(['ok' => false, 'message' => 'กรุณาระบุชื่อผู้แจ้ง'], 422);
            }
            View::redirect('/?error=name');
        }

        // Handle photo upload
        $photoPath = null;
        if (!empty($_FILES['photo']['tmp_name']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $photoPath = $this->handleUpload($_FILES['photo']);
        }

        $id = Need::create([
            'reporter_name' => $name,
            'reporter_phone' => $phone,
            'area_id' => $areaId,
            'address' => $address,
            'lat' => $lat,
            'lng' => $lng,
            'people_count' => $people,
            'need_types' => $types,
            'description' => $description,
            'urgency' => in_array($urgency, ['low','normal','high','critical'], true) ? $urgency : 'normal',
            'photo_path' => $photoPath,
        ]);

        Need::flushCache();
        $need = Need::find($id);

        if (!$wantsJson) {
            View::redirect('/success?code=' . urlencode($need['code']));
        }

        View::json([
            'ok' => true,
            'message' => 'ส่งคำขอสำเร็จ',
            'code' => $need['code'],
            'id' => $id,
        ]);
    }

    private function handleUpload(array $file): ?string
    {
        if ($file['size'] > UPLOAD_MAX_SIZE) return null;
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        if (!in_array($mime, UPLOAD_ALLOWED, true)) return null;

        $ext = match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
            default => 'jpg',
        };

        $dir = PUBLIC_PATH . '/uploads/' . date('Y/m');
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $filename = bin2hex(random_bytes(8)) . '.' . $ext;
        $dest = $dir . '/' . $filename;
        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            return null;
        }

        // Compress/resize if GD is available — reduces payload on slow networks
        $this->compressImage($dest, $mime);

        return 'uploads/' . date('Y/m') . '/' . $filename;
    }

    /**
     * Resize image to max 1600px and re-encode to keep file small.
     * Silently keeps original if GD is missing or processing fails.
     */
    private function compressImage(string $path, string $mime): void
    {
        if (!function_exists('imagecreatefromjpeg')) {
            return; // GD not available — keep original
        }
        if ($mime === 'image/webp' && !function_exists('imagewebp')) {
            return; // can't safely re-encode webp — keep original
        }

        $maxDim = 1600;
        $src = match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($path),
            'image/png' => @imagecreatefrompng($path),
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false,
            'image/gif' => @imagecreatefromgif($path),
            default => false,
        };
        if (!$src) {
            return;
        }

        $w = imagesx($src);
        $h = imagesy($src);

        // Output encoder matching the original format (keeps extension/mime correct)
        $encode = function (string $file, $img, int $q = 80) use ($mime): bool {
            return match ($mime) {
                'image/jpeg' => imagejpeg($img, $file, $q),
                'image/png' => imagepng($img, $file, 6),
                'image/webp' => imagewebp($img, $file, $q),
                'image/gif' => imagegif($img, $file),
                default => false,
            };
        };

        if ($w <= $maxDim && $h <= $maxDim) {
            // Already small — just re-encode to trim metadata
            $encode($path, $src, 82);
            imagedestroy($src);
            return;
        }

        $scale = min($maxDim / $w, $maxDim / $h);
        $nw = (int)round($w * $scale);
        $nh = (int)round($h * $scale);

        $dst = imagecreatetruecolor($nw, $nh);
        // Preserve transparency for PNG/WebP
        if ($mime === 'image/png' || $mime === 'image/webp') {
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
        }
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);
        $encode($path, $dst, 80);
        imagedestroy($src);
        imagedestroy($dst);
    }

    public function success(): void
    {
        $code = $_GET['code'] ?? '';
        View::render('needs/success', [
            'title' => 'ส่งคำขอสำเร็จ',
            'code' => $code,
        ]);
    }
}
