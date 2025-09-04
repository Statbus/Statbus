<?php

namespace App\Service\Icons;

use Exception;
use RuntimeException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;

class RenderDMI
{
    public string $mobIconDir;

    public function __construct(
        private DMISpriteExtractor $dmisprite,
        private Filesystem $fs,
        private readonly string $iconDir,
        private readonly string $outputDir
    ) {
        $this->mobIconDir = Path::join($this->iconDir, '/mob/');
    }

    private function verifyPath(string $path): string
    {
        if (!Path::isBasePath($this->iconDir, $path)) {
            throw new RuntimeException('Path traversal attempt detected');
        }
        if (!realpath($path)) {
            throw new RuntimeException('File not found');
        }
        return $path;
    }

    public function display(string $path)
    {
        $path = $this->verifyPath($path);
        return $this->dmisprite->loadImage($path);
    }

    public function render(string $path): string
    {
        $path = $this->verifyPath($path);
        $sprites = $this->dmisprite->loadImage($path);
        $info = pathinfo($path);
        $outDir = sprintf(
            '%s/%s',
            str_replace($this->iconDir, $this->outputDir, $info['dirname']),
            $info['filename']
        );
        $jsonFile = $outDir . '/' . $info['filename'] . '.json';
        $this->fs->remove($outDir);
        $this->fs->mkdir($outDir);
        $this->fs->touch($jsonFile);
        foreach ($sprites as $sprite) {
            $icons[] = $sprite['state'];
            foreach ($sprite['dir'] as $d => $i) {
                file_put_contents(
                    $outDir . '/' . $sprite['state'] . '-' . $d . '.png',
                    base64_decode($i)
                );
            }
        }
        $jf = fopen($jsonFile, 'w');
        fwrite($jf, json_encode($icons));
        fclose($jf);
        return count($icons);
    }

    public function getIconDir(): string
    {
        return $this->iconDir;
    }

    public function getMobIconDir(): string
    {
        return $this->mobIconDir;
    }

    public function getOutputDir(): string
    {
        return $this->outputDir;
    }
}
