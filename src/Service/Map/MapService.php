<?php

namespace App\Service\Map;

use App\Entity\Map\Map;
use App\Entity\Map\Render;
use SebastianBergmann\CodeCoverage\Report\Html\Renderer;
use stdClass;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Finder\Finder;
use Symfony\Component\String\Slugger\SluggerInterface;

class MapService
{
    public string $mapDir;
    public string $outDir;

    public function __construct(
        private SluggerInterface $slugger,
        private Filesystem $fs,
        private readonly string $iconDir,
        private readonly string $outputDir
    ) {
        $this->mapDir = Path::join($iconDir, '/../_maps');
        $this->outDir = Path::join($outputDir, '/../maps');
        $this->fs->mkdir($this->outDir);
    }

    public function buildMaplist(): void
    {
        $finder = new Finder();
        $finder
            ->files()
            ->in(Path::join($this->iconDir, '/../_maps'))
            ->name('*.json');
        $maps = [];
        if ($finder->hasResults()) {
            foreach ($finder as $file) {
                $levels = [2 => null];
                $rawMap = json_decode(file_get_contents($file));
                if (
                    property_exists($rawMap, 'traits') &&
                        is_iterable($rawMap->traits)
                ) { //We've got z levels
                    foreach ($rawMap->traits as $k => $v) {
                        $levels[$k + 2] = $v; //Stations start on z=2
                    }
                }
                $outDir = Path::join(
                    'maps/',
                    $this->slugger->slug($rawMap->map_name)->lower()
                );
                $slug = $this->slugger->slug($rawMap->map_name)->lower();
                $dmmPath = Path::join($rawMap->map_path, $rawMap->map_file);
                $map = new Map(
                    $rawMap->map_name,
                    $slug,
                    $dmmPath,
                    $outDir,
                    $levels
                );
                $maps[(string) $slug] = $map;
                unset($map);
            }
        }
        $mapFilePath = Path::join($this->outDir, '/maps.json');
        file_put_contents($mapFilePath, json_encode($maps));
    }

    public function parseMaps(string $json = 'maps.json'): void
    {
        $maps = json_decode(file_get_contents(Path::join(
            $this->outDir,
            $json
        )));
        foreach ($maps as $map) {
            $parsedMap = MapParserService::parseMapFile(Path::join(
                $this->mapDir,
                $map->dmmPath
            ));
            $name = $this->slugger->slug($map->name)->lower();
            $path = Path::join($this->outDir, $name);

            $this->fs->mkdir($path);
            file_put_contents(
                $path . '/symbols.json',
                json_encode($parsedMap['symbols'])
            );
            file_put_contents(
                $path . '/grid.json',
                json_encode($parsedMap['grid'])
            );

            foreach (array_keys((array) $map->levels) as $z) {
                $render = Render::renderZLevel(
                    $parsedMap['symbols'],
                    $parsedMap['grid'][$z]
                );
                $mapPng = Path::join($path, $name) . '-' . $z . '.png';
                imagepng($render->getImage(), $mapPng, 9);

                $render = Render::renderZLevel(
                    $parsedMap['symbols'],
                    $parsedMap['grid'][$z],
                    ['wall']
                );
                $mapPng = Path::join($path, $name) . '-' . $z . '-walls.png';
                imagepng($render->getImage(), $mapPng, 9);
            }
        }
    }

    public function getAvailableMaps(): array
    {
        return json_decode(
            file_get_contents(Path::join($this->outputDir, '/../maps') .
                '/maps.json'),
            true
        );
    }

    public function getMap(string $map): array
    {
        return $this->getAvailableMaps()[$map];
    }
}
