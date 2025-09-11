<?php

namespace App\Service\Death;

use App\Entity\Round;
use App\Repository\DeathRepository;
use App\Service\Map\MapService;
use Symfony\Component\Filesystem\Path;

class HeatmapService
{
    public function __construct(
        private DeathRepository $deathRepository,
        private MapService $mapService,
        private readonly string $outputDir
    ) {}

    public function generateHeatmap(string $map, int $z, bool $logScale = true)
    {
        $map = $this->mapService->getMap($map);
        $deaths = $this->deathRepository->fetchDeathsForHeatmap(
            $map['name'],
            $z
        );
        $image = imagecreatefrompng(Path::join(
            $this->outputDir,
            '/../',
            $map['outDir'],
            $map['slug'] . '-' . $z . '-walls.png'
        ));
        imagesavealpha($image, true);
        $w = imagesx($image);
        $h = imagesy($image);
        $black = imagecolorallocatealpha($image, 255, 255, 255, 20);
        imagefilledrectangle($image, 0, 0, $w, $h, $black);

        $min = min($deaths);
        $max = max($deaths);

        $start = [255, 255, 0, 0]; // yellow transparent
        $end = [255, 100, 0, 255]; // hot orange opaque

        foreach ($deaths as $coords => $count) {
            [$x, $y] = explode(',', $coords);

            $t = $logScale
                ? $this->normalizeLog($count, $max)
                : $this->normalizeLinear($count, $max);

            [$r, $g, $b, $a255] = $this->lerpColor($start, $end, $t);

            $color = imagecolorallocatealpha(
                $image,
                $r,
                $g,
                $b,
                $this->gdAlpha($a255)
            );
            $y = abs($h - $y);
            imagesetpixel($image, (int) $x, (int) $y, $color);
        }

        // return $deaths;
        ob_start();
        imagepng($image, null, 9);
        $output['image'] = base64_encode(ob_get_contents());
        ob_end_clean();
        $output['count'] = count($deaths) + floor(rand(1, 10));
        $output['max'] = $max + floor(rand(1, 10));
        return $output;
    }

    private function lerpColor(array $start, array $end, float $t): array
    {
        $r = (int) round($start[0] + (($end[0] - $start[0]) * $t));
        $g = (int) round($start[1] + (($end[1] - $start[1]) * $t));
        $b = (int) round($start[2] + (($end[2] - $start[2]) * $t));
        $a = (int) round($start[3] + (($end[3] - $start[3]) * $t));
        return [$r, $g, $b, $a];
    }

    private function gdAlpha(int $a255): int
    {
        return (int) round(((255 - $a255) / 255) * 127);
    }

    private function normalizeLog(int $count, int $max): float
    {
        if ($max <= 0) {
            return 0.0;
        }
        return log(1 + $count) / log(1 + $max); // 0 → 1
    }

    private function normalizeLinear(int $count, int $max): float
    {
        if ($max <= 0) {
            return 0.0;
        }
        return $count / $max; // 0 → 1
    }
}
