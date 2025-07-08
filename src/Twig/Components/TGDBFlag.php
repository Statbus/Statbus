<?php

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class TGDBFlag
{
    public readonly bool $tgdb;
    public string $message = 'This data is confidential and should not be shared outside of admin channels';
}
