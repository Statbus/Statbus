<?php

namespace App\Entity\Map;

use App\Enum\Roles\Departments;
use GdImage;

class Render
{
    private GdImage $image;

    public array $availablePasses = [
        'station' => 'stationPass',
        'wall' => 'wallPass',
        'area' => 'areaPass'
    ];

    public function __construct(
        private array $symbols,
        private array $map
    ) {
        $this->image = imagecreatetruecolor(count($map), count($map));
        imagesavealpha($this->image, true);
        $alpha = imagecolorallocatealpha($this->image, 0, 0, 0, 127);
        imagefill($this->image, 0, 0, $alpha);
    }

    public function getImage(): GdImage
    {
        return $this->image;
    }

    public static function renderZLevel(
        array $symbols,
        array $map,
        array $passes = ['station', 'wall', 'area']
    ): static {
        $renderer = new self($symbols, $map);
        foreach ($passes as $pass) {
            if (isset($renderer->availablePasses[$pass])) {
                $renderer->{$renderer->availablePasses[$pass]}();
            }
        }
        return $renderer;
    }

    private function stationPass(): static
    {
        $stationFill = imagecolorallocatealpha($this->image, 175, 175, 175, 0);
        foreach ($this->map as $x => $row) {
            foreach ($row as $y => $col) {
                if (
                    $this->symbols[$col]->areaHas('/area/station') ||
                        $this->symbols[$col]->areaHas('/area/mine')
                ) {
                    imagefilledrectangle(
                        $this->image,
                        $x,
                        $y,
                        ($x + 1) - 1,
                        ($y + 1) - 1,
                        $stationFill
                    );
                }
            }
        }
        return $this;
    }

    private function wallPass(): static
    {
        $wallFill = imagecolorallocatealpha($this->image, 100, 100, 100, 0);
        $asteroidFill = imagecolorallocatealpha($this->image, 114, 55, 49, 0);
        $doorFill = imagecolorallocatealpha($this->image, 0, 0, 0, 0);
        $windowFill = imagecolorallocatealpha($this->image, 9, 168, 247, 0);
        $latticeFill = imagecolorallocatealpha($this->image, 0, 0, 0, .5);
        $genTurfFill = imagecolorallocatealpha($this->image, 50, 50, 50, 0);
        $iceFill = imagecolorallocatealpha($this->image, 255, 255, 255, 0);
        $asteroidOpenFill = imagecolorallocatealpha(
            $this->image,
            163,
            163,
            83,
            0
        );
        $rules = [
            'turfHas' => [
                '/turf/open/genturf' => $genTurfFill,
                '/turf/open/misc/asteroid/snow/icemoon' => $iceFill,
                '/turf/open/misc/asteroid' => $asteroidOpenFill,
                '/turf/closed/wall' => $wallFill
            ],
            'areaHas' => [
                '/area/station/asteroid' => $asteroidFill
            ],
            'contentsHas' => [
                'airlock' => $doorFill,
                '/obj/effect/spawner/structure/window' => $windowFill,
                '/obj/structure/lattice' => $latticeFill
            ]
        ];

        foreach ($this->map as $x => $row) {
            foreach ($row as $y => $c) {
                foreach ($rules['turfHas'] as $needle => $fill) {
                    if ($this->symbols[$c]->turfHas($needle)) {
                        $this->drawCell($x, $y, $fill);
                    }
                }

                foreach ($rules['areaHas'] as $needle => $fill) {
                    if ($this->symbols[$c]->areaHas($needle)) {
                        $this->drawCell($x, $y, $fill);
                    }
                }

                foreach ($rules['contentsHas'] as $needle => $fill) {
                    if ($this->symbols[$c]->contentsHas($needle)) {
                        $this->drawCell($x, $y, $fill);
                    }
                }
            }
        }

        return $this;
    }

    private function areaPass(): static
    {
        $colors = [];
        foreach (Departments::cases() as $dept) {
            list($r, $g, $b) = sscanf($dept->getBackColor(), '#%02x%02x%02x');
            $colors[$dept->getAreaPathname()] = imagecolorallocatealpha(
                $this->image,
                $r,
                $g,
                $b,
                0
            );
        }
        $colors['maintenance'] = imagecolorallocatealpha(
            $this->image,
            50,
            50,
            50,
            0
        );
        $colors['medbay'] = $colors['medical'];
        foreach ($this->map as $x => $row) {
            foreach ($row as $y => $col) {
                foreach ($colors as $dept => $color) {
                    if ($this->symbols[$col]->areaHas($dept)) {
                        imagefilledrectangle(
                            $this->image,
                            $x,
                            $y,
                            ($x + 1) - 1,
                            ($y + 1) - 1,
                            $color
                        );
                    }
                }
            }
        }
        return $this;
    }

    private function networkPass(): static
    {
        $cableFill = imagecolorallocatealpha($this->image, 255, 255, 0, 0);
        $pipeFill = imagecolorallocatealpha($this->image, 0, 255, 255, 0);
        foreach ($this->map as $x => $row) {
            foreach ($row as $y => $col) {
                foreach ($col as $c) {
                    if (
                        $this->symbols[$c]->contentsHas('/obj/structure/cable')
                    ) {
                        imagefilledrectangle(
                            $this->image,
                            $x,
                            $y,
                            ($x + 1) - 1,
                            ($y + 1) - 1,
                            $cableFill
                        );
                    }
                    if (
                        $this->symbols[$col]->contentsHas(
                            '/obj/machinery/atmospherics/pipe'
                        )
                    ) {
                        imagefilledrectangle(
                            $this->image,
                            $x,
                            $y,
                            ($x + 1) - 1,
                            ($y + 1) - 1,
                            $pipeFill
                        );
                    }
                }
            }
        }
        return $this;
    }

    private function drawCell(int $x, int $y, int $fill): void
    {
        imagefilledrectangle($this->image, $x, $y, $x, $y, $fill);
    }
}
