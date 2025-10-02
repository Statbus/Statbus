<?php

namespace App\Service\Icons;

use Symfony\Component\Filesystem\Path;
use Symfony\Component\Finder\Finder;

class IconListService
{
    public function __construct(
        private RenderDMI $renderDMI
    ) {}

    public function listIcons(string $dir, ?array $filter = null): array
    {
        $files = (new Finder())
            ->files()
            ->in(Path::join($this->renderDMI->getOutputDir(), $dir))
            ->name('*.json');
        $icons = [];
        if ($files->hasResults()) {
            foreach ($files as $f) {
                $iconPath = str_replace(
                    Path::join($this->renderDMI->getOutputDir(), $dir),
                    '',
                    $f->getPath()
                );
                $i = json_decode(file_get_contents($f->getRealPath()), true);
                if (null === $i) {
                    continue;
                }
                $i = array_filter($i);
                if ($filter) {
                    $i = array_filter($i, function ($i) use ($filter) {
                        foreach ($filter as $fi) {
                            if (str_contains($i, $fi)) { // PHP 8+
                                return true;
                            }
                        }
                        return false;
                    });
                }
                $i = array_map(function ($v) use ($iconPath) {
                    return $iconPath . '/' . $v;
                }, $i);
                $keys = $i;
                $values = array_map(fn($v) => $v, $i);
                $icons += array_combine($keys, $values);
            }
        }
        return $icons;
    }
}
