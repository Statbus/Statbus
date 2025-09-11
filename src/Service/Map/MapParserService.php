<?php

namespace App\Service\Map;

use App\Entity\Map\Symbol;

class MapParserService
{
    private array $symbols = [];
    private array $map = [];

    public static function parseMapFile(string $file): array
    {
        $content = file_get_contents($file);
        list($symbols, $grid) = preg_split(
            '/\(\d+,\d+,\d+\)\s*=\s*{/',
            $content,
            2,
            PREG_SPLIT_DELIM_CAPTURE
        );
        $parser = new self($symbols, $grid);
        return [
            'symbols' => $parser->getSymbols(),
            'grid' => $parser->getMap()
        ];
    }

    public function __construct(
        private string $rawSymbols,
        private string $rawMap
    ) {
        $this->symbols = $this->createSymbolMap($rawSymbols);
        $symbolLength = strlen(array_keys($this->symbols)[0]);
        $this->map = $this->createGridMap($rawMap, $symbolLength);
    }

    public function getSymbols(): array
    {
        return $this->symbols;
    }

    public function getMap(): array
    {
        return $this->map;
    }

    private function createSymbolMap(string $symbols): array
    {
        $symbols = str_replace(["\r", "\n", "\t"], '', $symbols);
        preg_match_all(
            '/"([a-zA-Z]{1,3})"\s*=\s*\((.*?)\)(?=\s*"[a-zA-Z]{1,3}"\s*=|\s*$)/s',
            $symbols,
            $defs
        );
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
                    $entities = explode('{', $u);
                    $tile['path'] = $entities[0];
                    $entities = $entities[1];
                    $entities = rtrim($entities, '}');
                    $entities = preg_split(
                        '/;(?=([^\"]*\"[^\"]*\")*[^\"]*$)/',
                        $entities
                    );
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
        $grid = '1,1,1) = {' . $grid;
        $grid = str_replace(') = {"', ',', $grid);
        $grid = rtrim($grid, '"}');
        $grid = explode('"}(', $grid);
        foreach ($grid as &$g) {
            $g = explode(',', $g);
            $z = ((int) $g[2]) + 1;
            $tileGrid[$z][$g[0]] = str_split($g[3], $symbolLength);
        }
        return $tileGrid;
    }

    private function symbolToEntity(string $key, array $data): Symbol
    {
        $turf = null;
        $area = null;
        foreach ($data as $k => $d) {
            if (str_starts_with($d['path'], '/turf/')) {
                $turf = $d['path'];
                unset($data[$k]);
            }
            if (str_starts_with($d['path'], '/area/')) {
                $area = $d['path'];
                unset($data[$k]);
            }
        }

        if (!$turf || !$area) {
            throw new \RuntimeException(
                "Missing turf or area for symbol '$key'"
            );
        }
        return new Symbol($key, $turf, $area, $data);
    }
}
