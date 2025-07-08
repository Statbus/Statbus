<?php

namespace App\Entity\Map;

use App\Enum\Roles\Departments;
use GdImage;

class Render
{
    private GdImage $image;
    private array $symbols;
    private array $map;

    public function __construct(
        private Map $parsedMap,
        private int $z,
        private int $scale
    ) {
        $this->symbols = $parsedMap->getSymbols();
        $this->map = $parsedMap->getMap();
    }

    public static function generate(Map $map, int $scale = 1): GdImage
    {
        $render = new self(
            parsedMap: $map,
            z: $z,
            scale: $scale
        );
        return $render->render();
    }

    public function render(): GdImage
    {
        $this->image = imagecreatetruecolor(
            count($this->map) * $this->scale,
            count($this->map) * $this->scale
        );
        imagesavealpha($this->image, true);
        $alpha = imagecolorallocatealpha($this->image, 0, 0, 0, 127);
        imagefill($this->image, 0, 0, $alpha);

        $this->stationPass()->areaPass()->wallPass()// ->networkPass()
        ;
        return $this->image;
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
                        $x * $this->scale,
                        $y * $this->scale,
                        (($x + 1) * $this->scale) - 1,
                        (($y + 1) * $this->scale) - 1,
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
        foreach ($this->map as $x => $row) {
            foreach ($row as $y => $col) {
                if ($this->symbols[$col]->turfHas('/turf/open/genturf')) {
                    imagefilledrectangle(
                        $this->image,
                        $x * $this->scale,
                        $y * $this->scale,
                        (($x + 1) * $this->scale) - 1,
                        (($y + 1) * $this->scale) - 1,
                        $genTurfFill
                    );
                }
                if (
                    $this->symbols[$col]->turfHas(
                        '/turf/open/misc/asteroid/snow/icemoon'
                    )
                ) {
                    imagefilledrectangle(
                        $this->image,
                        $x * $this->scale,
                        $y * $this->scale,
                        (($x + 1) * $this->scale) - 1,
                        (($y + 1) * $this->scale) - 1,
                        $iceFill
                    );
                }
                if ($this->symbols[$col]->areaHas('/area/station/asteroid')) {
                    imagefilledrectangle(
                        $this->image,
                        $x * $this->scale,
                        $y * $this->scale,
                        (($x + 1) * $this->scale) - 1,
                        (($y + 1) * $this->scale) - 1,
                        $asteroidFill
                    );
                }
                if ($this->symbols[$col]->turfHas('/turf/open/misc/asteroid')) {
                    imagefilledrectangle(
                        $this->image,
                        $x * $this->scale,
                        $y * $this->scale,
                        (($x + 1) * $this->scale) - 1,
                        (($y + 1) * $this->scale) - 1,
                        $asteroidOpenFill
                    );
                }
                if ($this->symbols[$col]->turfHas('/turf/closed/wall')) {
                    imagefilledrectangle(
                        $this->image,
                        $x * $this->scale,
                        $y * $this->scale,
                        (($x + 1) * $this->scale) - 1,
                        (($y + 1) * $this->scale) - 1,
                        $wallFill
                    );
                }
                if ($this->symbols[$col]->contentsHas('airlock')) {
                    imagefilledrectangle(
                        $this->image,
                        $x * $this->scale,
                        $y * $this->scale,
                        (($x + 1) * $this->scale) - 1,
                        (($y + 1) * $this->scale) - 1,
                        $doorFill
                    );
                }
                if (
                    $this->symbols[$col]->contentsHas(
                        '/obj/effect/spawner/structure/window'
                    )
                ) {
                    imagefilledrectangle(
                        $this->image,
                        $x * $this->scale,
                        $y * $this->scale,
                        (($x + 1) * $this->scale) - 1,
                        (($y + 1) * $this->scale) - 1,
                        $windowFill
                    );
                }
                if (
                    $this->symbols[$col]->contentsHas('/obj/structure/lattice')
                ) {
                    imagefilledrectangle(
                        $this->image,
                        $x * $this->scale,
                        $y * $this->scale,
                        (($x + 1) * $this->scale) - 1,
                        (($y + 1) * $this->scale) - 1,
                        $latticeFill
                    );
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
                            $x * $this->scale,
                            $y * $this->scale,
                            (($x + 1) * $this->scale) - 1,
                            (($y + 1) * $this->scale) - 1,
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
                if ($this->symbols[$col]->contentsHas('/obj/structure/cable')) {
                    imagefilledrectangle(
                        $this->image,
                        $x * $this->scale,
                        $y * $this->scale,
                        (($x + 1) * $this->scale) - 1,
                        (($y + 1) * $this->scale) - 1,
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
                        $x * $this->scale,
                        $y * $this->scale,
                        (($x + 1) * $this->scale) - 1,
                        (($y + 1) * $this->scale) - 1,
                        $pipeFill
                    );
                }
            }
        }
        return $this;
    }
}
