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

        $final = self::compress($dest, $mime);
        $filename = basename($final);

        return 'uploads/' . date('Y/m') . '/' . $filename;
    }

    /**
     * Resize image to max 1280px, re-encode as JPEG/WebP-friendly format and
     * strip metadata to keep file small. PNGs with real transparency stay PNG;
     * opaque PNGs are converted to JPEG. Silently keeps original on failure.
     */
    private static function compress(string $path, string $mime): string
    {
        if (!function_exists('imagecreatefromjpeg')) {
            return $path; // GD not available — keep original
        }
        if ($mime === 'image/webp' && !function_exists('imagewebp')) {
            return $path; // can't safely re-encode webp — keep original
        }

        $maxDim = 1280;
        $src = match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($path),
            'image/png' => @imagecreatefrompng($path),
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false,
            'image/gif' => @imagecreatefromgif($path),
            default => false,
        };
        if (!$src) {
            return $path;
        }

        $w = imagesx($src);
        $h = imagesy($src);

        // Opaque PNG → JPEG (much smaller); keep PNG only when it has alpha
        $outMime = $mime;
        $outPath = $path;
        if ($mime === 'image/png' && !self::hasAlpha($src)) {
            $outMime = 'image/jpeg';
            $outPath = preg_replace('/\.png$/i', '.jpg', $path);
        }

        $scale = min(1.0, $maxDim / max($w, $h));
        $nw = (int)round($w * $scale);
        $nh = (int)round($h * $scale);

        $dst = $src;
        if ($nw !== $w || $nh !== $h) {
            $dst = imagecreatetruecolor($nw, $nh);
            if ($outMime === 'image/png' || $outMime === 'image/webp') {
                imagealphablending($dst, false);
                imagesavealpha($dst, true);
            } elseif ($outMime === 'image/gif') {
                imagepalettetotruecolor($dst);
            }
            imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);
            imagedestroy($src);
        }

        match ($outMime) {
            'image/jpeg' => imagejpeg($dst, $outPath, 78),
            'image/png' => imagepng($dst, $outPath, 6),
            'image/webp' => imagewebp($dst, $outPath, 78),
            'image/gif' => imagegif($dst, $outPath),
            default => null,
        };
        imagedestroy($dst);

        if ($outPath !== $path) {
            @unlink($path);
        }
        return $outPath;
    }

    /**
     * True when the image actually uses transparency (not just an all-opaque alpha channel).
     */
    private static function hasAlpha(\GdImage $img): bool
    {
        $w = imagesx($img);
        $h = imagesy($img);
        // Sample a grid — full scan is too slow for large images
        $stepX = max(1, (int)($w / 50));
        $stepY = max(1, (int)($h / 50));
        for ($y = 0; $y < $h; $y += $stepY) {
            for ($x = 0; $x < $w; $x += $stepX) {
                $rgba = imagecolorat($img, $x, $y);
                // GD alpha: 0 = opaque, 127 = fully transparent
                if ((($rgba >> 24) & 0x7F) > 0) {
                    return true;
                }
            }
        }
        return false;
    }
}
