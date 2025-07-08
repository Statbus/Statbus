<?php

namespace App\Service\Map;

use App\Entity\Map\Render;

class MapRendererService
{
    public function renderMap(string $file): array
    {
        $parsedMap = MapParserService::parseMapFromFile($file);

        $render = Render::generate($parsedMap);

        foreach ($images as $z => $image) {
            $minimapFile = fopen(
                '/var/www/html/public/img/minimaps/' .
                    $mapname .
                    '-' .
                    $z .
                    '.png',
                'w'
            );
            fwrite($minimapFile, base64_decode($image));
            fclose($minimapFile);
        }
        $mapSymbolFile = fopen(
            '/var/www/html/public/img/minimaps/' . $mapname . '.symbols.json',
            'w'
        );
        $mapGridFile = fopen(
            '/var/www/html/public/img/minimaps/' . $mapname . '.grid.json',
            'w'
        );
        fwrite($mapSymbolFile, json_encode($this->symbols, JSON_PRETTY_PRINT));
        fclose($mapSymbolFile);
        fwrite($mapGridFile, json_encode($this->map, JSON_PRETTY_PRINT));
        fclose($mapGridFile);
        return [
            'symbols' => $this->symbols,
            'map' => $this->map,
            'image' => $images
        ];
    }
}
