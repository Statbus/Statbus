<?php

namespace App\Enum\Ticket;

use JsonSerializable;

enum Action: string implements JsonSerializable
{
    case CLOSED = 'Closed';
    case DISCONNECTED = 'Disconnected';
    case IC = 'IC Issue';
    case INTERACTION = 'Interaction';
    case RECONNECTED = 'Reconnected';
    case REJECTED = 'Rejected';
    case REOPENED = 'Reopened';
    case REPLY = 'Reply';
    case RESOLVED = 'Resolved';
    case SKILL = 'Skill Issue';
    case OPENED = 'Ticket Opened';

    public function getVerb(): string
    {
        return match ($this) {
            Action::REPLY => 'from',
            default => 'by'
        };
    }

    public function isAction(): bool
    {
        return match ($this) {
            Action::REPLY, Action::OPENED, Action::INTERACTION => false,
            default => true
        };
    }

    public function getCssClass(): string
    {
        return match ($this) {
            Action::CLOSED => 'info',
            Action::REJECTED => 'danger',
            Action::IC, Action::RESOLVED => 'success',
            Action::INTERACTION => 'secondary',
            Action::OPENED => 'info',
            default => 'primary'
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            Action::CLOSED => 'fa-solid fa-circle-xmark',
            Action::OPENED => 'fa-solid fa-circle-question',
            Action::REJECTED => 'fa-solid fa-trash',
            Action::RESOLVED => 'fa-solid fa-circle-check',
            Action::REPLY => 'fa-solid fa-reply',
            default => 'fa-solid fa-circle-exclamation'
        };
    }

    public function isResolved(): bool
    {
        return match ($this) {
            Action::CLOSED, Action::REJECTED, Action::RESOLVED, Action::IC, Action::SKILL, => true,
            default => false
        };
    }

    public function isConnectAction(): bool
    {
        return match ($this) {
            Action::DISCONNECTED, Action::RECONNECTED => true,
            default => false
        };
    }

    public function jsonSerialize(): mixed
    {
        return [
            'action' => $this->value,
            'icon' => $this->getIcon(),
            'verb' => $this->getVerb(),
            'cssClass' => $this->getCssClass(),
            'isResolved' => $this->isResolved(),
            'isAction' => $this->isAction(),
            'isConnectAction' => $this->isConnectAction()
        ];
    }
}
