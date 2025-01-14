<?php

namespace App\Enum\Message;

enum TypeEnum: string
{
    case MEMO = 'memo';
    case MESSAGE = 'message';
    case MESSAGE_SENT = 'message sent';
    case NOTE = 'note';
    case WATCHLIST = 'watchlist entry';

    public function getIcon(): string
    {
        return match ($this) {
            TypeEnum::MEMO => 'fa-solid fa-scroll',
            TypeEnum::MESSAGE => 'fa-solid fa-message',
            TypeEnum::MESSAGE_SENT => 'fa-regular fa-message',
            TypeEnum::NOTE => 'fa-solid fa-note-sticky',
            TypeEnum::WATCHLIST => 'fa-solid fa-binoculars'
        };
    }
}
