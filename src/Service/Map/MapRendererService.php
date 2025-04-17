<?php

namespace App\Service\Map;

use App\Enum\Roles\Departments;
use GdImage;

class MapRendererService
{

    private array $symbols;

    private array $map;

    public function getFromMapFile($file): array
    {

        $content = file_get_contents($file);

        list($symbols, $grid) = preg_split('/\(\d+,\d+,\d+\)\s*=\s*{/', $content, 2, PREG_SPLIT_DELIM_CAPTURE);
        $this->symbols = $this->createSymbolMap($symbols);
        $symbolLength = strlen(array_keys($this->symbols)[0]);
        $this->map = $this->createGridMap($grid, $symbolLength);
        $images = $this->renderImage($this->symbols, $this->map, 2);
        foreach ($images as $z => $image) {
            $minimapFile = fopen("/var/www/html/public/img/minimaps/" . strtolower(pathinfo($file)['filename']) . "-" . $z . ".png", 'w');
            fwrite($minimapFile, base64_decode($image));
            fclose($minimapFile);
        }
        return [
            'symbols' => $this->symbols,
            'map' => $this->map,
            'image' => $images
        ];
    }

    private function createSymbolMap(string $symbols): array
    {
        $symbols = str_replace(["\r", "\n", "\t"], '', $symbols);
        preg_match_all('/"([a-zA-Z]{1,3})"\s*=\s*\((.*?)\)(?=\s*"[a-zA-Z]{1,3}"\s*=|\s*$)/s', $symbols, $defs);
        foreach ($defs[2] as $key => $value) {
            $tmp[$defs[1][$key]] = $value;
        }
        foreach ($tmp as &$def) {
            $def = explode(',/', $def);
            foreach ($def as &$d) {
                $d = trim(rtrim($d));
            }
        }
        $tiles = [];
        foreach ($tmp as $k => &$t) {
            $tiles[$k] = [];
            foreach ($t as $u) {
                $tile = [
                    'path' => null,
                    'props' => null
                ];
                if (!str_starts_with($u, '/')) {
                    $u = '/' . $u;
                }
                $tile['path'] = $u;
                if (str_contains($u, '{')) {
                    $entities = explode("{", $u);
                    $tile['path'] = $entities[0];
                    $entities = $entities[1];
                    $entities = rtrim($entities, '}');
                    $entities = preg_split('/;(?=([^\"]*\"[^\"]*\")*[^\"]*$)/', $entities);
                    $props = [];
                    foreach ($entities as $entity) {
                        $entity = explode(' = ', $entity);
                        $props[$entity[0]] = $entity[1];
                    }

                    $tile['props'] = $props;
                }
                $tiles[$k][] = $tile;
            }
        }
        foreach ($tiles as $key => &$t) {
            $t = $this->symbolToEntity($key, $t);
        }
        return $tiles;
    }

    private function createGridMap(string $grid, int $symbolLength): array
    {
        $tileGrid = [];
        $grid = str_replace("\n", '', $grid);
        $grid = "1,1,1) = {" . $grid;
        $grid = str_replace(') = {"', ',', $grid);
        $grid = rtrim($grid, '"}');
        $grid = explode('"}(', $grid);
        foreach ($grid as &$g) {
            $g = explode(',', $g);
            $z = (int) $g[2] + 1;
            $tileGrid[$z][$g[0]] = str_split($g[3], $symbolLength);
        }
        return $tileGrid;
    }

    private function symbolToEntity(string $key, array $data): Symbol
    {
        $turf = null;
        $area = null;
        foreach ($data as $k => $d) {
            if (str_starts_with($d['path'], "/turf/")) {
                $turf = $d['path'];
                unset($data[$k]);
            }
            if (str_starts_with($d['path'], "/area/")) {
                $area = $d['path'];
                unset($data[$k]);
            }
        }

        if (!$turf || !$area) {
            dump($data);
            throw new \RuntimeException("Missing turf or area for symbol '$key'");
        }
        return new Symbol($key, $turf, $area, $data);
    }

    private function renderImage(array $symbols, array $map)
    {
        $out = [];
        foreach ($map as $z => $m) {
            $image = MapRender::generate($symbols, $m, $z, 3);
            ob_start();
            imagepng($image);
            $imageData = ob_get_clean();
            $out[$z] = base64_encode($imageData);
        }
        return $out;
    }
}

class Symbol
{
    public function __construct(
        public string $key,
        public string $turf,
        public string $area,
        public array $contents
    ) {}

    public function turfHas(string $search): bool
    {
        return str_contains($this->turf, $search);
    }

    public function areaHas(string $search): bool
    {
        return str_contains($this->area, $search);
    }

    public function contentsHas(string $search): bool
    {
        foreach ($this->contents as $c) {
            if (str_contains($c['path'], $search)) {
                return true;
            }
        }
        return false;
    }
}

class MapRender
{
    private GdImage $image;

    public function __construct(
        private array $symbols,
        private array $map,
        private int $z,
        private int $scale
    ) {}

    public static function generate(
        array $symbols,
        array $map,
        int $z,
        int $scale = 1
    ): GdImage {
        $render = new self(
            symbols: $symbols,
            map: $map,
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

        $this
            ->stationPass()
            ->areaPass()
            ->wallPass()
            // ->networkPass()
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
                    imagefilledrectangle($this->image, $x * $this->scale, $y * $this->scale, ($x + 1) * $this->scale - 1, ($y + 1) * $this->scale - 1, $stationFill);
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
        $asteroidOpenFill = imagecolorallocatealpha($this->image, 163, 163, 83, 0);
        foreach ($this->map as $x => $row) {
            foreach ($row as $y => $col) {
                if ($this->symbols[$col]->turfHas('/turf/open/genturf')) {
                    imagefilledrectangle($this->image, $x * $this->scale, $y * $this->scale, ($x + 1) * $this->scale - 1, ($y + 1) * $this->scale - 1, $genTurfFill);
                }
                if ($this->symbols[$col]->turfHas('/turf/open/misc/asteroid/snow/icemoon')) {
                    imagefilledrectangle($this->image, $x * $this->scale, $y * $this->scale, ($x + 1) * $this->scale - 1, ($y + 1) * $this->scale - 1, $iceFill);
                }
                if ($this->symbols[$col]->areaHas('/area/station/asteroid')) {
                    imagefilledrectangle($this->image, $x * $this->scale, $y * $this->scale, ($x + 1) * $this->scale - 1, ($y + 1) * $this->scale - 1, $asteroidFill);
                }
                if ($this->symbols[$col]->turfHas('/turf/open/misc/asteroid')) {
                    imagefilledrectangle($this->image, $x * $this->scale, $y * $this->scale, ($x + 1) * $this->scale - 1, ($y + 1) * $this->scale - 1, $asteroidOpenFill);
                }
                if ($this->symbols[$col]->turfHas('/turf/closed/wall')) {
                    imagefilledrectangle($this->image, $x * $this->scale, $y * $this->scale, ($x + 1) * $this->scale - 1, ($y + 1) * $this->scale - 1, $wallFill);
                }
                if ($this->symbols[$col]->contentsHas('airlock')) {
                    imagefilledrectangle($this->image, $x * $this->scale, $y * $this->scale, ($x + 1) * $this->scale - 1, ($y + 1) * $this->scale - 1, $doorFill);
                }
                if ($this->symbols[$col]->contentsHas('/obj/effect/spawner/structure/window')) {
                    imagefilledrectangle($this->image, $x * $this->scale, $y * $this->scale, ($x + 1) * $this->scale - 1, ($y + 1) * $this->scale - 1, $windowFill);
                }
                if ($this->symbols[$col]->contentsHas('/obj/structure/lattice')) {
                    imagefilledrectangle($this->image, $x * $this->scale, $y * $this->scale, ($x + 1) * $this->scale - 1, ($y + 1) * $this->scale - 1, $latticeFill);
                }
            }
        }
        return $this;
    }

    private function areaPass(): static
    {
        $colors = [];
        foreach (Departments::cases() as $dept) {
            list($r, $g, $b) = sscanf($dept->getBackColor(), "#%02x%02x%02x");
            $colors[$dept->getAreaPathname()] = imagecolorallocatealpha($this->image, $r, $g, $b, 0);
        }
        $colors['maintenance'] = imagecolorallocatealpha($this->image, 50, 50, 50, 0);
        $colors['medbay'] = $colors['medical'];
        foreach ($this->map as $x => $row) {
            foreach ($row as $y => $col) {
                foreach ($colors as $dept => $color) {
                    if ($this->symbols[$col]->areaHas($dept)) {
                        imagefilledrectangle($this->image, $x * $this->scale, $y * $this->scale, ($x + 1) * $this->scale - 1, ($y + 1) * $this->scale - 1, $color);
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
                    imagefilledrectangle($this->image, $x * $this->scale, $y * $this->scale, ($x + 1) * $this->scale - 1, ($y + 1) * $this->scale - 1, $cableFill);
                }
                if ($this->symbols[$col]->contentsHas('/obj/machinery/atmospherics/pipe')) {
                    imagefilledrectangle($this->image, $x * $this->scale, $y * $this->scale, ($x + 1) * $this->scale - 1, ($y + 1) * $this->scale - 1, $pipeFill);
                }
            }
        }
        return $this;
    }
}
