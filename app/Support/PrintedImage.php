<?php

namespace App\Support;

/**
 * An uploaded image as it should print in the applications report: its size in
 * pixels the right way up, and its real-world size when the file records one
 * that can be trusted.
 *
 * Read from the file's own headers, without GD: JPEG's JFIF density and EXIF
 * resolution and orientation, and PNG's pHYs chunk. Phones stamp 72 dpi on
 * every photo and many programs stamp 96, which say nothing about the paper,
 * so only a density between MIN_TRUSTED_DPI and MAX_TRUSTED_DPI, the same
 * across and down, counts as the document's real size. A scanner records one.
 *
 * Placement keeps a document with a trusted size at exactly that size: on an
 * A4 page with a caption when it fits inside the margins, edge to edge on A4
 * when it is A4 itself, and on a page cut to its own size when it is larger.
 * Anything else is scaled to fit an A4 page and says so. All lengths are in
 * PDF points, 72 to the inch.
 */
final class PrintedImage
{
    public const MIN_TRUSTED_DPI = 100;

    public const MAX_TRUSTED_DPI = 1200;

    /**
     * The density a placed image is resampled to: sharp on paper, a fraction
     * of a phone photo's size in the PDF.
     */
    public const TARGET_DPI = 200;

    public const A4 = [595.28, 841.89];

    /**
     * A3, the largest real size a document is believed to have. A larger
     * "real" size is a wrong density stamp, so the image is fitted instead.
     */
    public const A3 = [841.89, 1190.55];

    public const MARGIN = 36.0;

    public const CAPTION_BAND = 30.0;

    public function __construct(
        public readonly string $type,
        public readonly int $width,
        public readonly int $height,
        public readonly int $orientation,
        public readonly ?float $dpi,
    ) {}

    /**
     * Read a JPEG or PNG; null for anything else or a file that cannot be read.
     */
    public static function fromBytes(string $bytes): ?self
    {
        $size = @getimagesizefromstring($bytes, $segments);

        if ($size === false || $size[0] < 1 || $size[1] < 1) {
            return null;
        }

        [$width, $height, $imageType] = $size;

        if ($imageType === IMAGETYPE_JPEG) {
            $exif = self::readExif($segments['APP1'] ?? '');
            $density = self::readJfifDensity($segments['APP0'] ?? '') ?? $exif['density'];
            $orientation = $exif['orientation'];
            $type = 'jpeg';
        } elseif ($imageType === IMAGETYPE_PNG) {
            $density = self::readPngDensity($bytes);
            $orientation = 1;
            $type = 'png';
        } else {
            return null;
        }

        // Orientations 5 to 8 turn the picture a quarter turn.
        if ($orientation >= 5) {
            [$width, $height] = [$height, $width];
        }

        return new self($type, $width, $height, $orientation, self::trustedDpi($density));
    }

    /**
     * Where the image goes on its page, and how its size is described.
     *
     * @return array{paper: array{0: float, 1: float}, x: float, y: float, width: float, height: float, actualSize: bool, caption: bool}
     */
    public function placement(): array
    {
        if ($this->dpi !== null) {
            $width = $this->width / $this->dpi * 72;
            $height = $this->height / $this->dpi * 72;

            if (min($width, $height) <= self::A3[0] && max($width, $height) <= self::A3[1]) {
                [$pageWidth, $pageHeight] = self::a4For($width, $height);

                if ($width <= $pageWidth - 2 * self::MARGIN && $height <= $pageHeight - 2 * self::MARGIN - self::CAPTION_BAND) {
                    return $this->box([$pageWidth, $pageHeight], ($pageWidth - $width) / 2, self::MARGIN + self::CAPTION_BAND, $width, $height, true, true);
                }

                // A full-page scan: A4 edge to edge, with no room for a caption.
                if ($width <= $pageWidth * 1.01 && $height <= $pageHeight * 1.01) {
                    return $this->box([$pageWidth, $pageHeight], ($pageWidth - $width) / 2, ($pageHeight - $height) / 2, $width, $height, true, false);
                }

                return $this->box([$width, $height], 0, 0, $width, $height, true, false);
            }
        }

        [$pageWidth, $pageHeight] = self::a4For($this->width, $this->height);
        $scale = min(
            ($pageWidth - 2 * self::MARGIN) / $this->width,
            ($pageHeight - 2 * self::MARGIN - self::CAPTION_BAND) / $this->height,
        );

        return $this->box(
            [$pageWidth, $pageHeight],
            ($pageWidth - $this->width * $scale) / 2,
            self::MARGIN + self::CAPTION_BAND,
            $this->width * $scale,
            $this->height * $scale,
            false,
            true,
        );
    }

    /**
     * How the report describes the image's size, beside it and on the cover.
     */
    public function sizeNote(): string
    {
        $placement = $this->placement();

        if (! $placement['actualSize']) {
            return __('Scaled to fit the page: the file does not record its real-world size');
        }

        return __('Actual size, :width × :height in', [
            'width' => number_format($placement['width'] / 72, 2),
            'height' => number_format($placement['height'] / 72, 2),
        ]);
    }

    /**
     * The image as a JPEG the right way up, resampled to TARGET_DPI at its
     * printed size, or null when it cannot be prepared.
     *
     * Needs GD to turn and resample. Without it a JPEG goes in as uploaded,
     * which dompdf can embed unaided, and a PNG cannot be shown at all.
     */
    public function toJpeg(string $bytes): ?string
    {
        if (! function_exists('imagecreatefromstring')) {
            return $this->type === 'jpeg' ? $bytes : null;
        }

        $image = @imagecreatefromstring($bytes);

        if ($image === false) {
            return null;
        }

        $image = match ($this->orientation) {
            2 => self::flipped($image),
            3 => imagerotate($image, 180, 0),
            4 => self::flipped($image, IMG_FLIP_VERTICAL),
            5 => self::flipped(imagerotate($image, 270, 0)),
            6 => imagerotate($image, 270, 0),
            7 => self::flipped(imagerotate($image, 90, 0)),
            8 => imagerotate($image, 90, 0),
            default => $image,
        };

        $placement = $this->placement();
        $targetWidth = (int) ceil($placement['width'] / 72 * self::TARGET_DPI);

        if (imagesx($image) > $targetWidth * 1.1) {
            $image = imagescale($image, $targetWidth, -1, IMG_BICUBIC);
        }

        // A transparent PNG lands on white paper, not on JPEG's black.
        $sheet = imagecreatetruecolor(imagesx($image), imagesy($image));
        imagefill($sheet, 0, 0, imagecolorallocate($sheet, 255, 255, 255));
        imagecopy($sheet, $image, 0, 0, 0, 0, imagesx($image), imagesy($image));

        ob_start();
        imagejpeg($sheet, null, 82);

        return ob_get_clean() ?: null;
    }

    /**
     * @param  array{0: float, 1: float}  $paper
     * @return array{paper: array{0: float, 1: float}, x: float, y: float, width: float, height: float, actualSize: bool, caption: bool}
     */
    private function box(array $paper, float $x, float $y, float $width, float $height, bool $actualSize, bool $caption): array
    {
        return ['paper' => $paper, 'x' => $x, 'y' => $y, 'width' => $width, 'height' => $height, 'actualSize' => $actualSize, 'caption' => $caption];
    }

    /**
     * A4, turned to landscape for a wider-than-tall image.
     *
     * @return array{0: float, 1: float}
     */
    private static function a4For(float $width, float $height): array
    {
        return $width > $height ? [self::A4[1], self::A4[0]] : self::A4;
    }

    /**
     * @param  array{0: float, 1: float}|null  $density
     */
    private static function trustedDpi(?array $density): ?float
    {
        if ($density === null) {
            return null;
        }

        [$across, $down] = $density;

        if ($across < self::MIN_TRUSTED_DPI || $across > self::MAX_TRUSTED_DPI || abs($across - $down) > $across * 0.02) {
            return null;
        }

        return $across;
    }

    /**
     * JFIF's density in dots per inch, when its units say inches or centimetres.
     *
     * @return array{0: float, 1: float}|null
     */
    private static function readJfifDensity(string $segment): ?array
    {
        if (strlen($segment) < 12 || ! str_starts_with($segment, "JFIF\0")) {
            return null;
        }

        ['units' => $units, 'x' => $x, 'y' => $y] = unpack('Cunits/nx/ny', $segment, 7);

        return match ($units) {
            1 => [(float) $x, (float) $y],
            2 => [$x * 2.54, $y * 2.54],
            default => null,
        };
    }

    /**
     * EXIF's orientation (1 when absent) and resolution in dots per inch.
     *
     * @return array{orientation: int, density: array{0: float, 1: float}|null}
     */
    private static function readExif(string $segment): array
    {
        $found = ['orientation' => 1, 'density' => null];

        if (strlen($segment) < 14 || ! str_starts_with($segment, "Exif\0\0")) {
            return $found;
        }

        $tiff = substr($segment, 6);
        $little = str_starts_with($tiff, 'II');
        $short = fn (int $at): int => unpack($little ? 'v' : 'n', $tiff, $at)[1];
        $long = fn (int $at): int => unpack($little ? 'V' : 'N', $tiff, $at)[1];
        $rational = function (int $at) use ($long, $tiff): ?float {
            if ($at + 8 > strlen($tiff) || $long($at + 4) === 0) {
                return null;
            }

            return $long($at) / $long($at + 4);
        };

        $directory = $long(4);

        if ($directory + 2 > strlen($tiff)) {
            return $found;
        }

        $resolution = ['x' => null, 'y' => null, 'unit' => 2];

        for ($entry = 0, $count = $short($directory); $entry < $count; $entry++) {
            $at = $directory + 2 + $entry * 12;

            if ($at + 12 > strlen($tiff)) {
                break;
            }

            match ($short($at)) {
                0x0112 => $found['orientation'] = max(1, min(8, $short($at + 8))),
                0x011A => $resolution['x'] = $rational($long($at + 8)),
                0x011B => $resolution['y'] = $rational($long($at + 8)),
                0x0128 => $resolution['unit'] = $short($at + 8),
                default => null,
            };
        }

        if ($resolution['x'] !== null && $resolution['y'] !== null && in_array($resolution['unit'], [2, 3], true)) {
            $perInch = $resolution['unit'] === 3 ? 2.54 : 1;
            $found['density'] = [$resolution['x'] * $perInch, $resolution['y'] * $perInch];
        }

        return $found;
    }

    /**
     * PNG's pHYs density in dots per inch, when its unit is the metre.
     *
     * @return array{0: float, 1: float}|null
     */
    private static function readPngDensity(string $bytes): ?array
    {
        for ($at = 8; $at + 8 <= strlen($bytes);) {
            ['length' => $length, 'type' => $type] = unpack('Nlength/a4type', $bytes, $at);

            if ($type === 'pHYs' && $length >= 9) {
                ['x' => $x, 'y' => $y, 'unit' => $unit] = unpack('Nx/Ny/Cunit', $bytes, $at + 8);

                return $unit === 1 ? [$x * 0.0254, $y * 0.0254] : null;
            }

            if ($type === 'IDAT' || $type === 'IEND') {
                return null;
            }

            $at += 12 + $length;
        }

        return null;
    }

    private static function flipped(\GdImage $image, int $mode = IMG_FLIP_HORIZONTAL): \GdImage
    {
        imageflip($image, $mode);

        return $image;
    }
}
