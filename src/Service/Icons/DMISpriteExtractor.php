<?php

namespace App\Service\Icons;

use App\Service\Icons\GifCreator;
use App\Service\Icons\PNGMetadataExtractor;

class DMISpriteExtractor extends PNGMetadataExtractor
{
    // public function getSprites($filename)
    // {
    //     $data = $this->getSpriteData($filename);
    // }

    public function loadImage($filename)
    {
        $data = $this->getMetadata(
            $filename
        )['text']['ImageDescription']['x-default'];
        $data = explode("\n", trim($data));
        $width = 32;
        $height = 32;
        $version = 0;

        $state = '';
        $dirs = '';
        $frames = '';
        $delay = '';
        $loop = '';
        $hotspot = '';
        $rewind = '';
        $movement = '';
        $first = true;

        $sprites = [];

        foreach ($data as $i => $line) {
            $line = trim($line);
            $dataPair = explode('=', $line);
            if (count($dataPair) != 2) {
                continue;
            }
            //print "<br>$key - $value";

            $key = trim($dataPair[0]);
            $value = trim($dataPair[1]);

            if ($key == 'version') {
                $version = intval($value);
                continue;
            }
            if ($key == 'width') {
                $width = intval($value);
                continue;
            }
            if ($key == 'height') {
                $height = intval($value);
                continue;
            }
            if ($key == 'state') {
                if ($first) {
                    $first = false;
                } else {
                    $sprites[] = array(
                        'state' => $state,
                        'dirs' => $dirs,
                        'frames' => $frames,
                        'delay' => $delay,
                        'loop' => $loop,
                        'hotspot' => $hotspot,
                        'movement' => $movement,
                        'rewind' => $rewind
                    );
                }
                $state = '';
                $dirs = '';
                $frames = '';
                $delay = '';
                $loop = '';
                $rewind = '';
                $hotspot = '';
                $movement = '';
                if ($this->startsWith($value, '"')) {
                    $value = substr($value, 1);
                }
                if ($this->endsWith($value, '"')) {
                    $value = substr($value, 0, strlen($value) - 1);
                }
                $state = $value;
                continue;
            }
            if ($key == 'dirs') {
                $dirs = $value;
                continue;
            }
            if ($key == 'frames') {
                $frames = $value;
                continue;
            }
            if ($key == 'loop') {
                $loop = $value;
                continue;
            }
            if ($key == 'delay') {
                $delay = $value;
                continue;
            }
            if ($key == 'hotspot') {
                $hotspot = $value;
                continue;
            }
            if ($key == 'movement') {
                $movement = $value;
                continue;
            }
            if ($key == 'rewind') {
                $rewind = $value;
                continue;
            }
        }

        if (!$first) {
            $sprites[] = array(
                'state' => $state,
                'dirs' => $dirs,
                'frames' => $frames,
                'delay' => $delay,
                'loop' => $loop,
                'hotspot' => $hotspot,
                'movement' => $movement,
                'rewind' => $rewind,
                'width' => $width,
                'height' => $height
            );
        }
        $image = imagecreatefrompng($filename);
        imagesavealpha($image, true);
        $trans_colour = imagecolorallocatealpha($image, 0, 0, 0, 127);
        imagefill($image, 0, 0, $trans_colour);
        $imageWidth = imagesx($image);
        $imageHeight = imagesy($image);
        $spritesX = $imageWidth / $width;
        $spritesY = $imageHeight / $height;

        $spriteNumber = 0;
        foreach ($sprites as $i => &$spriteData) {
            $spriteName = $spriteData['state'];
            $spriteName = preg_replace('/[^A-Za-z0-9 _ .-]/', '', $spriteName);
            //$spriteName = preg_replace("([^\w\s\d\-_~,;:\[\]\(\).])", '', $spriteName);
            //$spriteName = preg_replace("([\.]{2,})", '', $spriteName);

            $frames = intval($spriteData['frames']);
            $dirs = intval($spriteData['dirs']);
            $rewind = intval($spriteData['rewind']);
            if (!$frames) {
                $frames = 1;
            }
            if (!$dirs) {
                $dirs = 1;
            }
            if (!$rewind) {
                $rewind = 0;
            }

            // print "<h2>Sprite: $spriteName</h2>";
            for ($dir = 0; $dir < $dirs; $dir++) {
                // print "<h3>Dir: $dir (spriteNum = $spriteNumber)</h3>";
                if ($frames == 1) {
                    $sprite = imagecreatetruecolor($width, $height);
                    imagesavealpha($sprite, true);
                    $trans_colour = imagecolorallocatealpha(
                        $sprite,
                        0,
                        0,
                        0,
                        127
                    );
                    imagefill($sprite, 0, 0, $trans_colour);

                    $posX = ($spriteNumber + $dir) % $spritesX;
                    $posY = floor(($spriteNumber + $dir) / $spritesX);
                    // print "<br>posX = $posX; posY = $posY<br>";

                    imagecopy(
                        $sprite,
                        $image,
                        0,
                        0,
                        $posX * $width,
                        $posY * $height,
                        $width,
                        $height
                    );
                    ob_start();
                    imagepng($sprite, null, 0);
                    $spriteData['base64'] = base64_encode(ob_get_contents());
                    $spriteData['X'] = (int) $posX;
                    $spriteData['Y'] = (int) $posY;
                    $spriteData['width'] = $width;
                    $spriteData['height'] = $height;
                    $spriteData['dir'][] = base64_encode(ob_get_contents());
                    ob_end_clean();
                } else {
                    $gifFrameList = array();
                    for ($frameNum = 0; $frameNum < $frames; $frameNum++) {
                        $sprite = imagecreatetruecolor($width, $height);
                        imagesavealpha($sprite, true);
                        $trans_colour = imagecolortransparent(
                            $sprite,
                            127 << 24
                        );
                        imagefill($sprite, 0, 0, $trans_colour);

                        $posX =
                            ($spriteNumber + ($dirs * $frameNum) + $dir) %
                            $spritesX;
                        $posY = floor((
                                $spriteNumber + ($dirs * $frameNum) + $dir
                            ) /
                            $spritesX);
                        //print "<br>posX = $posX; posY = $posY<br>";
                        imagecopy(
                            $sprite,
                            $image,
                            0,
                            0,
                            $posX * $width,
                            $posY * $height,
                            $width,
                            $height
                        );
                        // if(!file_exists("$folderName/$spriteName"."_$dir")){
                        //   mkdir("$folderName/$spriteName"."_$dir");
                        // }
                        ob_start();
                        imagepng($sprite);
                        $spriteData['base64'] = base64_encode(ob_get_contents());
                        $spriteData['dir'][] = base64_encode(ob_get_contents());
                        ob_end_clean();
                        $gifFrameList[] = $sprite;
                    }

                    $delayList = array();
                    $delay = $spriteData['delay'];
                    $delays = explode(',', $delay);
                    foreach ($delays as $i => $dl) {
                        $dl = intval(trim($dl));
                        $delayList[] = $dl * 10;
                    }

                    if ($rewind) {
                        //print "REWIND";
                        for ($i = count($gifFrameList) - 2; $i > 0; $i--) {
                            $gifFrameList[] = $gifFrameList[$i];
                        }
                        for ($i = count($delayList) - 2; $i > 0; $i--) {
                            $delayList[] = $delayList[$i];
                        }

                        //print_r($gifFrameList);
                    }

                    $gc = new GifCreator();
                    $gc->create($gifFrameList, $delayList, 0);
                    $gifBinary = $gc->getGif();
                    $spriteData['base64'] = base64_encode($gifBinary);
                }
            }
            $spriteNumber += $frames * $dirs;
        }
        return $sprites;
    }

    private function startsWith($haystack, $needle)
    {
        // search backwards starting from haystack length characters from the end
        return (
            $needle === '' ||
            strrpos($haystack, $needle, -strlen($haystack)) !== false
        );
    }

    private function endsWith($haystack, $needle)
    {
        // search forward starting from end minus needle length characters
        return (
            $needle === '' ||
            ($temp = strlen($haystack) - strlen($needle)) >= 0 &&
                strpos($haystack, $needle, $temp) !== false
        );
    }
}
