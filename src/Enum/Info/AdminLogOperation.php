<?php

namespace App\Enum\Info;

enum AdminLogOperation: string
{
    case REMOVE_ADMIN = 'remove admin';
    case ADD_ADMIN = 'add admin';
    case CHANGE_RANK = 'change admin rank';
    case ADD_RANK = 'add rank';
    case REMOVE_RANK = 'remove rank';
    case CHANGE_FLAGS = 'change rank flags';

    public function getCssClass(): string
    {
        return match ($this) {
            AdminLogOperation::ADD_RANK,
            AdminLogOperation::CHANGE_FLAGS,
            AdminLogOperation::REMOVE_RANK,
            AdminLogOperation::CHANGE_RANK
                => 'info',
            AdminLogOperation::REMOVE_ADMIN => 'danger',
            AdminLogOperation::ADD_ADMIN => 'success'
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            AdminLogOperation::REMOVE_ADMIN => 'fa-solid fa-user-xmark',
            AdminLogOperation::ADD_ADMIN => 'fa-solid fa-user-plus',
            AdminLogOperation::CHANGE_RANK => 'fa-solid fa-user-pen',
            AdminLogOperation::ADD_RANK => 'fa-solid fa-id-card-clip',
            AdminLogOperation::REMOVE_RANK => 'fa-solid fa-rectangle-xmark',
            AdminLogOperation::CHANGE_FLAGS => 'fa-solid fa-flag'
        };
    }

    public function getShort(): string
    {
        return match ($this) {
            AdminLogOperation::REMOVE_ADMIN => $this->value,
            AdminLogOperation::ADD_ADMIN => $this->value,
            AdminLogOperation::CHANGE_RANK => 'Change Rank',
            AdminLogOperation::ADD_RANK => $this->value,
            AdminLogOperation::REMOVE_RANK => $this->value,
            AdminLogOperation::CHANGE_FLAGS => 'Change Flags'
        };
    }
}
