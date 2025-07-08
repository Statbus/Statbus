<?php

namespace App\Service\Roles;

use App\Enum\Roles\Jobs;

class RoleDataService
{
    public ?string $name = 'A Job!';
    public string $backColor = '#CCC';
    public string $foreColor = '#FFF';
    public ?string $style = null;
    public ?string $icon = null;

    public function __construct(
        private ?Jobs $job,
        private ?string $role = null
    ) {
        if ($job) {
            $this->name = $job->value;
            $this->backColor = $job->getColor();
            $this->foreColor = $job->getForeColor();
            $this->icon = $job->getIcon();
        } else {
            $this->name = $role;
            $this->backColor = '#CCC';
            $this->foreColor = '#000';
        }
        $this->setStyle();
    }

    private function setStyle(): self
    {
        $this->style = sprintf(
            'background: %s; color: %s;',
            $this->backColor,
            $this->foreColor
        );
        return $this;
    }
}
