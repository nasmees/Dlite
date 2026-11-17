<?php
/**
 * Image upload handling — validate, store under uploads/, compress via GD
 */

class ImageUploader
{
    public static function store(array $file): ?string
    {
        if (empty($file['tmp_name']) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return null;
        }
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

        self::compress($dest, $mime);

        return 'uploads/' . date('Y/m') . '/' . $filename;
    }

    /**
     * Resize image to max 1600px and re-encode to keep file small.
     * Silently keeps original if GD is missing or processing fails.
     */
    private static function compress(string $path, string $mime): void
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
            $encode($path, $src, 82);
            imagedestroy($src);
            return;
        }

        $scale = min($maxDim / $w, $maxDim / $h);
        $nw = (int)round($w * $scale);
        $nh = (int)round($h * $scale);

        $dst = imagecreatetruecolor($nw, $nh);
        if ($mime === 'image/png' || $mime === 'image/webp') {
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
        }
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);
        $encode($path, $dst, 80);
        imagedestroy($src);
        imagedestroy($dst);
    }
}
