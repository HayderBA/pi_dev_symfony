<?php

namespace App\Service;

use App\Entity\Reservation;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Logo\Logo;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Symfony\Component\HttpKernel\KernelInterface;

class QrCodeService
{
    public function __construct(private readonly KernelInterface $kernel)
    {
    }

    public function generate(string $data, ?Reservation $reservation = null): string
    {
        $payload = $this->buildPayload($data, $reservation);

        $qrCode = new QrCode(
            data: $payload,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: 300,
            margin: 10,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
            foregroundColor: new Color(25, 25, 25),
            backgroundColor: new Color(255, 255, 255)
        );

        $logo = null;
        $logoPath = $this->resolveLogoPath();
        if ($logoPath !== null) {
            $logo = new Logo(
                path: $logoPath,
                resizeToWidth: 60,
                punchoutBackground: true
            );
        }

        $writer = new PngWriter();
        $result = $writer->write($qrCode, $logo);
        $png = $this->applyGrowMindGradient($result->getString());

        return 'data:image/png;base64,' . base64_encode($png);
    }

    public function isValid(?Reservation $reservation): bool
    {
        if (!$reservation || !$reservation->getEvenement()) {
            return false;
        }

        $eventDate = $reservation->getEvenement()->getDate();
        if (!$eventDate instanceof \DateTimeInterface) {
            return false;
        }

        $expiration = \DateTimeImmutable::createFromInterface($eventDate)->setTime(23, 59, 59);

        return new \DateTimeImmutable() <= $expiration;
    }

    private function buildPayload(string $data, ?Reservation $reservation): string
    {
        $decoded = json_decode($data, true);
        $payload = is_array($decoded) ? $decoded : ['content' => $data];

        if ($reservation && $reservation->getEvenement()) {
            $eventDate = $reservation->getEvenement()->getDate();
            $expiration = \DateTimeImmutable::createFromInterface($eventDate)->setTime(23, 59, 59);

            $payload['expires_at'] = $expiration->format(\DateTimeInterface::ATOM);
            $payload['is_valid'] = $this->isValid($reservation);
            $payload['event_date'] = $eventDate->format('Y-m-d');
            $payload['growmind_theme'] = 'green-blue-gradient';
        }

        if (!isset($payload['expires_at']) && isset($payload['date'])) {
            try {
                $parsedDate = new \DateTimeImmutable((string) $payload['date']);
                $payload['expires_at'] = $parsedDate->setTime(23, 59, 59)->format(\DateTimeInterface::ATOM);
                $payload['is_valid'] = new \DateTimeImmutable() <= $parsedDate->setTime(23, 59, 59);
            } catch (\Throwable) {
            }
        }

        return json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?: $data;
    }

    private function resolveLogoPath(): ?string
    {
        $paths = [
            $this->kernel->getProjectDir() . '/public/assets/img/logoGrowmind.png',
            $this->kernel->getProjectDir() . '/public/assets/img/logo.webp',
        ];

        foreach ($paths as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return null;
    }

    private function applyGrowMindGradient(string $pngBinary): string
    {
        $image = imagecreatefromstring($pngBinary);
        if (!$image instanceof \GdImage) {
            return $pngBinary;
        }

        $width = imagesx($image);
        $height = imagesy($image);

        imagealphablending($image, false);
        imagesavealpha($image, true);

        $start = ['r' => 107, 'g' => 191, 'b' => 89];
        $end = ['r' => 30, 'g' => 91, 'b' => 168];

        for ($x = 0; $x < $width; $x++) {
            for ($y = 0; $y < $height; $y++) {
                $rgba = imagecolorat($image, $x, $y);
                $alpha = ($rgba & 0x7F000000) >> 24;
                $red = ($rgba >> 16) & 0xFF;
                $green = ($rgba >> 8) & 0xFF;
                $blue = $rgba & 0xFF;

                $isQrPixel = $alpha < 120 && ($red < 240 || $green < 240 || $blue < 240);
                if (!$isQrPixel) {
                    continue;
                }

                $ratio = ($x + $y) / max(1, ($width - 1) + ($height - 1));
                $gradientColor = imagecolorallocatealpha(
                    $image,
                    (int) round($start['r'] + (($end['r'] - $start['r']) * $ratio)),
                    (int) round($start['g'] + (($end['g'] - $start['g']) * $ratio)),
                    (int) round($start['b'] + (($end['b'] - $start['b']) * $ratio)),
                    $alpha
                );

                imagesetpixel($image, $x, $y, $gradientColor);
            }
        }

        ob_start();
        imagepng($image);
        $gradientPng = ob_get_clean() ?: $pngBinary;
        imagedestroy($image);

        return $gradientPng;
    }
}
