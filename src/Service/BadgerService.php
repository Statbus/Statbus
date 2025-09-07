<?php

namespace App\Service;

use App\Entity\Badger\BadgerRequest;
use App\Entity\Badger\BadgerResult;
use App\Entity\Badger\Species\Species;
use App\Enum\Badger\Directions;
use App\Enum\Badger\IDCards;
use App\Repository\CharacterImageRepository;
use App\Security\User;
use App\Service\Icons\RenderDMI;
use Exception;
use GdImage;
use Symfony\Component\Filesystem\Path;

class BadgerService
{
    public const TITLE_SIZE = 15;

    public string $consolas;

    public function __construct(
        private RenderDMI $renderDMI,
        private CharacterImageRepository $characterImageRepository,
        private readonly string $outputDir,
        private readonly string $badgerResources
    ) {
        $this->consolas = $this->badgerResources . '/cascadia.otf';
    }

    public function generate(BadgerRequest $request): BadgerResult
    {
        $payload = new BadgerResult();
        $request->processExtras();

        $canvas = $this->getBaseImage();
        $mob = $this->getBaseImage();

        if ($request->extraKeys['behindFront']) {
            foreach ($request->extraKeys['behindFront'] as $k) {
                $this->drawMobBehindExtra(
                    img: $mob,
                    key: $k,
                    request: $request
                );
            }
        }

        //Base Body
        $this->getBaseBody(
            $mob,
            $request->species,
            $request->gender,
            $request->direction,
            $request->skinTone,
            $request->eyeColor
        );

        if ($request->extraKeys['behindFront']) {
            foreach ($request->extraKeys['behindFront'] as $k) {
                $this->drawMobFrontExtra(
                    img: $mob,
                    key: $k,
                    request: $request
                );
            }
        }

        if ($request->extraKeys['body']) {
            foreach ($request->extraKeys['body'] as $k) {
                $this->drawMobExtra(
                    img: $mob,
                    key: $k,
                    request: $request
                );
            }
        }

        //Hair
        $this->drawClothingItem(
            $mob,
            '/mob/human/human_face/',
            $request->hair,
            $request->direction,
            $request->hairColor
        );

        //Facial Hair
        $this->drawClothingItem(
            $mob,
            '/mob/human/human_face/',
            $request->facial,
            $request->direction,
            $request->facialColor
        );

        //Augmentations
        $this->drawClothingItem(
            $mob,
            '/mob/augmentation/',
            $request->augment,
            $request->direction
        );

        //Underwear
        $this->drawClothingItem(
            $mob,
            '/mob/clothing/underwear/',
            $request->underwear,
            $request->direction
        );

        //Jumpsuit
        $this->drawClothingItem(
            $mob,
            '/mob/clothing/under/',
            $request->undersuit,
            $request->direction
        );

        //Ear equipment
        $this->drawClothingItem(
            $mob,
            '/mob/clothing/ears/',
            $request->ears,
            $request->direction
        );

        //Mask
        $this->drawClothingItem(
            $mob,
            '/mob/clothing/mask/',
            $request->mask,
            $request->direction
        );

        //Helmet
        $this->drawClothingItem(
            $mob,
            '/mob/clothing/head/',
            $request->helmet,
            $request->direction
        );

        //Exosuit/Jackets/Armor
        $this->drawClothingItem(
            $mob,
            '/mob/clothing/suits/',
            $request->suit,
            $request->direction
        );

        //Belt
        $this->drawClothingItem(
            $mob,
            '/mob/clothing/belt/',
            $request->belt,
            $request->direction
        );

        //Eye wear
        $this->drawClothingItem(
            $mob,
            '/mob/clothing/eyes/',
            $request->eye,
            $request->direction
        );

        //Gloves
        $this->drawClothingItem(
            $mob,
            '/mob/clothing/hands/',
            $request->glove,
            $request->direction
        );

        //Footwear
        $this->drawClothingItem(
            $mob,
            '/mob/clothing/feet/',
            $request->foot,
            $request->direction
        );

        //Items worn on back
        $this->drawClothingItem(
            $mob,
            '/mob/clothing/back/',
            $request->back,
            $request->direction
        );

        //Neckties, etc
        $this->drawClothingItem(
            $mob,
            '/mob/clothing/neck/',
            $request->neck,
            $request->direction
        );

        //Items being held
        $this->drawClothingItem(
            $mob,
            '/mob/inhands/',
            $request->holding,
            $request->direction
        );

        $corp = $this->getCorpID($request);
        $mughostbgcolor = imagecolorallocate($corp, 0xB0, 0xB0, 0xB0);
        $mugshot_offset_x = 10;
        $mugshot_offset_y = 13;

        $pixelxoffset = 0;
        $pixelyoffset = 0;
        imagefilledrectangle(
            $corp,
            $mugshot_offset_x + $pixelxoffset,
            ($mugshot_offset_y + $pixelyoffset) - 3,
            ($mugshot_offset_x + $pixelxoffset + 45) - 1,
            ($mugshot_offset_y + $pixelyoffset + 42) - 1,
            $mughostbgcolor
        );
        imagecopyresized($corp, $mob, 10, 13, 8, 0, 45, 42, 15, 14);

        $card = imagecreatefrompng($this->getStationID($request->stationId));
        imagecopy($canvas, $card, 0, 0, 0, 0, 32, 32);
        imagedestroy($card);

        ob_start();
        imagepng($canvas, null, 9);
        $payload->stationId = base64_encode(ob_get_contents());
        ob_end_clean();

        ob_start();
        imagepng($corp, null, 9);
        $payload->corpId = base64_encode(ob_get_contents());
        ob_end_clean();

        //HUD icons here so they dont show up on the corporate ID
        $this->drawClothingItem($mob, '/mob/huds/', $request->hud, null);

        ob_start();
        imagepng($mob, null, 9);
        $payload->mob = base64_encode(ob_get_contents());
        ob_end_clean();

        return $payload;
    }

    private function getBaseImage(): GdImage
    {
        $img = imagecreatetruecolor(32, 32);
        imagesavealpha($img, true);
        $alpha = imagecolorallocatealpha($img, 0, 0, 0, 127);
        imagefill($img, 0, 0, $alpha);
        return $img;
    }

    private function getStationID(IDCards $card): string
    {
        return $this->outputDir . '/obj/card/' . $card->value . '-0.png';
    }

    private function getCorpID(BadgerRequest $request): GdImage
    {
        $bg =
            $this->badgerResources .
            '/cards/' .
            $request->cardBackground->value .
            '.png';
        $corporate = imagecreatefrompng($bg);
        $colors = $request->cardBackground->colorMap();
        $this->drawTextWithBorder(
            $corporate,
            $request->name,
            183,
            20,
            $colors->title,
            $colors->title_b,
            $colors->useborder,
            11
        );
        $this->drawTextWithBorder(
            $corporate,
            $request->job,
            183,
            38,
            $colors->job,
            $colors->job_b,
            $colors->useborder,
            12
        );
        $this->drawTextWithBorder(
            $corporate,
            $request->bottomText,
            183,
            54,
            $colors->bottom,
            $colors->bottom_b,
            $colors->useborder,
            8
        );
        return $corporate;
    }

    private function drawTextWithBorder(
        \GdImage $image,
        string $text,
        int $xCenter,
        int $yBaseline,
        string $colorHex,
        ?string $borderColorHex = null,
        bool $useBorder = false,
        int $fontSize = 15,
        int $maxWidth = 200
    ): void {
        do {
            $bbox = imagettfbbox($fontSize, 0, $this->consolas, $text);
            $textWidth = $bbox[2] - $bbox[0];
            if ($textWidth > $maxWidth) {
                $fontSize--;
            }
        } while ($textWidth > $maxWidth && $fontSize > 6);

        $textX = $xCenter - ((int) floor($textWidth / 2));

        if ($useBorder && $borderColorHex !== null) {
            $borderColor = self::allocateHexColor($image, $borderColorHex);
            foreach ([[-1, -1], [-1, 1], [1, -1], [1, 1]] as [$dx, $dy]) {
                imagettftext(
                    $image,
                    $fontSize,
                    0,
                    $textX + $dx,
                    $yBaseline + $dy,
                    $borderColor,
                    $this->consolas,
                    $text
                );
            }
        }

        $color = self::allocateHexColor($image, $colorHex);
        imagettftext(
            $image,
            $fontSize,
            0,
            $textX,
            $yBaseline,
            $color,
            $this->consolas,
            $text
        );
    }

    private function getBaseBody(
        GdImage $image,
        Species $species,
        string $gender,
        Directions $dir,
        string $skintone,
        string $eyeColor
    ): void {
        dump($species);
        $sprites = $species->getBodySprites(
            gender: $gender,
            dir: $dir
        );
        foreach ($sprites as $position => $sprite) {
            $img = imagecreatefrompng(Path::join($this->outputDir, $sprite));
            imagecopy($image, $img, 0, 0, 0, 0, 32, 32);
            imagedestroy($img);
        }
        $skinTone = str_replace('#', '', $skintone);
        $skinTone = str_split($skinTone, 2);
        foreach ($skinTone as &$c) {
            $c = 255 - hexdec($c);
        }
        imagefilter($image, IMG_FILTER_NEGATE);
        if ($species->canColor) {
            imagefilter(
                $image,
                IMG_FILTER_COLORIZE,
                $skinTone[0],
                $skinTone[1],
                $skinTone[2],
                25
            );
        }
        imagefilter($image, IMG_FILTER_NEGATE);
        $eyeColor = self::allocateHexColor($image, $eyeColor);
        switch ($dir) {
            case Directions::SOUTH:
            default:
                imagefilledrectangle($image, 14, 6, 14, 6, $eyeColor); //Left
                imagefilledrectangle($image, 16, 6, 16, 6, $eyeColor); //Right
                break;

            case Directions::NORTH:
                // imagefilledrectangle($body, 14, 6, 14, 6, $eyeColor);//Left
                // imagefilledrectangle($body, 16, 6, 16, 6, $eyeColor);//Right
                break;

            case Directions::EAST:
                imagefilledrectangle($image, 18, 6, 18, 6, $eyeColor);
                break;

            case Directions::WEST:
                imagefilledrectangle($image, 13, 6, 13, 6, $eyeColor);
                break;
        }
    }

    private function drawClothingItem(
        GdImage $img,
        string $path,
        mixed $icon = null,
        ?Directions $dir = Directions::SOUTH,
        ?string $color = null
    ): void {
        if (!$icon) {
            return;
        }
        if (!$dir) {
            $dir = 0;
        } else {
            $dir = $dir->value;
        }
        if (is_array($icon)) {
            foreach ($icon as $i) {
                $icon = Path::join($this->outputDir, $path, $i);
                $icon = $icon . '-' . $dir . '.png';
                $clothing = imagecreatefrompng($icon);
                if ($color) {
                    self::overlayColor($clothing, $color);
                }
                imagecopy($img, $clothing, 0, 0, 0, 0, 32, 32);
            }
            return;
        }
        $icon = Path::join($this->outputDir, $path, $icon);
        $icon = $icon . '-' . $dir . '.png';
        $clothing = imagecreatefrompng($icon);
        if ($color) {
            self::overlayColor($clothing, $color);
        }
        imagecopy($img, $clothing, 0, 0, 0, 0, 32, 32);
        return;
    }

    public function drawMobBehindExtra(
        GdImage $img,
        string $key,
        BadgerRequest $request
    ): void {
        $path = Path::join(
            $this->outputDir,
            $request->species->extraPaths['behindFront'][$key]
        );
        foreach ($request->behind[$key] as $i) {
            $icon =
                Path::join($path, $i) .
                '-' .
                $request->direction->value .
                '.png';
            try {
                $extra = imagecreatefrompng($icon);
                imagecopy($img, $extra, 0, 0, 0, 0, 32, 32);
            } catch (Exception $e) {
                dump($e->getMessage());
            }
        }
    }

    public function drawMobFrontExtra(
        GdImage $img,
        string $key,
        BadgerRequest $request
    ): void {
        $path = Path::join(
            $this->outputDir,
            $request->species->extraPaths['behindFront'][$key]
        );
        foreach ($request->front[$key] as $i) {
            $icon =
                Path::join($path, $i) .
                '-' .
                $request->direction->value .
                '.png';
            try {
                $extra = imagecreatefrompng($icon);
                imagecopy($img, $extra, 0, 0, 0, 0, 32, 32);
            } catch (Exception $e) {
                dump($e->getMessage());
            }
        }
    }

    public function drawMobExtra(
        GdImage $img,
        string $key,
        BadgerRequest $request
    ): void {
        $path = Path::join(
            $this->outputDir,
            $request->species->extraPaths['body'][$key]
        );
        foreach ($request->mobExtra[$key] as $i) {
            $icon =
                Path::join($path, $i) .
                '-' .
                $request->direction->value .
                '.png';
            $extra = imagecreatefrompng($icon);
            imagecopy($img, $extra, 0, 0, 0, 0, 32, 32);
        }
    }

    private static function allocateHexColor(GdImage $img, string $hex): int
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        return imagecolorallocate($img, $r, $g, $b);
    }

    public static function overlayColor(GdImage $image, string $color): void
    {
        $color = str_replace('#', '', $color);
        $color = str_split($color, 2);
        foreach ($color as &$c) {
            $c = 255 - hexdec($c);
        }
        imagefilter($image, IMG_FILTER_NEGATE);
        imagefilter(
            $image,
            IMG_FILTER_COLORIZE,
            $color[0],
            $color[1],
            $color[2],
            25
        );
        imagefilter($image, IMG_FILTER_NEGATE);
    }

    public function assignImage(
        User $user,
        string $character,
        string $image
    ): void {
        $images = $this->characterImageRepository->fetchImagesForUser($user);
        dump($images);
        if (in_array($character, array_keys($images))) {
            $this->characterImageRepository->updateEntry(
                $user,
                $character,
                $image
            );
            return;
        }
        $this->characterImageRepository->insertNewEntry(
            $user,
            $character,
            $image
        );
        return;
    }

    public function getImagesForCkey(string $ckey): array
    {
        return $this->characterImageRepository->fetchImagesForCkey($ckey);
    }
}
