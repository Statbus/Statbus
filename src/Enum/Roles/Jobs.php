<?php

namespace App\Enum\Roles;

use App\Enum\Roles\Departments;
use App\Service\LuminosityContrast;

enum Jobs: string
{
    case AI = 'AI';
    case ASSISTANT = 'Assistant';
    case ATMOS_TECH = 'Atmospheric Technician';
    case BARTENDER = 'Bartender';
    case BITRUNNER = 'Bitrunner';
    case BOTANIST = 'Botanist';
    case CAPTAIN = 'Captain';
    case CARGO_TECH = 'Cargo Technician';
    case CHAPLAIN = 'Chaplain';
    case CHEMIST = 'Chemist';
    case CHIEF_ENGIE = 'Chief Engineer';
    case CMO = 'Chief Medical Officer';
    case CLOWN = 'Clown';
    case COOK = 'Cook';
    case CORONER = 'Coroner';
    case CURATOR = 'Curator';
    case CYBORG = 'Cyborg';
    case DETECTIVE = 'Detective';
    case GENETICIST = 'Geneticist';
    case HOP = 'Head of Personnel';
    case HOS = 'Head of Security';
    case JANITOR = 'Janitor';
    case LAWYER = 'Lawyer';
    case LIBRARIAN = 'Librarian';
    case DOCTOR = 'Medical Doctor';
    case MIME = 'Mime';
    case PARAMEDIC = 'Paramedic';
    case PRISONER = 'Prisoner';
    case PSYCHOLOGIST = 'Psychologist';
    case QM = 'Quartermaster';
    case RD = 'Research Director';
    case ROBOTICIST = 'Roboticist';
    case SCIENTIST = 'Scientist';
    case SECURITY = 'Security Officer';
    case MINER = 'Shaft Miner';
    case ENGINEER = 'Station Engineer';
    case VIROLOGIST = 'Virologist';
    case WARDEN = 'Warden';

    //Special roles for timekeeping
    case LIVING = 'Living';
    case GHOST = 'Ghost';
    case ADMIN = 'Admin';
    case UNKNOWN = 'Unknown';

    //Baddies
    case ABDUCTOR = 'Abductor';
    case XENOMORPH = 'Xenomorph';
    case BLOB = 'Blob';
    case BLOOD_BROTHER = 'Blood Brother';
    case CHANGELING = 'Changeling';
    case CULTIST = 'Cultist';
    case INTERNAL_AFFAIRS_AGENT = 'Internal Affairs Agent';
    case MALF = 'Malf AI';
    case MONKEY = 'Monkey';
    case SPACE_NINJA = 'Space Ninja';
    case OPERATIVE = 'Operative';
    case SYNDICATE_MUTINEER = 'Syndicate Mutineer';
    case REVOLUTIONARY = 'Revolutionary';
    case REVENANT = 'Revenant';
    case HEAD_REVOLUTIONARY = 'Head Revolutionary';
    case SYNDICATE = 'Syndicate';
    case TRAITOR = 'Traitor';
    case WIZARD = 'Wizard';
    case HIVEMIND_HOST = 'Hivemind Host';
    case HERETIC = 'Heretic';
    case NIGHTMARE = 'Nightmare';
    case NUCLEAR_OPERATIVE = 'Nuclear Operative';
    case SPACE_DRAGON = 'Space dragon';

    //Ghost roles
    case PAI = 'Pai';

    //Statbus Specific
    case OBSERVER = 'Observer';

    public function getColor(): string
    {
        return match ($this) {
            default => '#6E6E6E', //Jobs::ASSISTANT
            Jobs::AI, Jobs::CYBORG => '#000',
            Jobs::ATMOS_TECH, Jobs::ENGINEER => '#FFA62B',
            Jobs::BITRUNNER, Jobs::MINER, Jobs::CARGO_TECH => '#B18644',
            Jobs::BARTENDER,
            Jobs::BOTANIST,
            Jobs::CHAPLAIN,
            Jobs::CLOWN,
            Jobs::COOK,
            Jobs::CURATOR,
            Jobs::JANITOR,
            Jobs::LAWYER,
            Jobs::LIBRARIAN,
            Jobs::MIME,
            Jobs::PSYCHOLOGIST
                => '#58C800',
            Jobs::CHEMIST,
            Jobs::CORONER,
            Jobs::PARAMEDIC,
            Jobs::DOCTOR,
            Jobs::VIROLOGIST
                => '#5B97BC',
            Jobs::GENETICIST, Jobs::SCIENTIST, Jobs::ROBOTICIST => '#C96DBF',
            Jobs::SECURITY, Jobs::DETECTIVE, Jobs::WARDEN => '#CB0000',
            Jobs::CAPTAIN,
            Jobs::CHIEF_ENGIE,
            Jobs::CMO,
            Jobs::RD,
            Jobs::HOP,
            Jobs::QM,
            Jobs::HOS
                => '#1B67A5',
            Jobs::ADMIN => '#df0afb',
            Jobs::LIVING => '#AAA',
            Jobs::GHOST => '#000',
            Jobs::PRISONER => '#FF9900',
            Jobs::ABDUCTOR,
            Jobs::XENOMORPH,
            Jobs::BLOB,
            Jobs::BLOOD_BROTHER,
            Jobs::CHANGELING,
            Jobs::CULTIST,
            Jobs::INTERNAL_AFFAIRS_AGENT,
            Jobs::MALF,
            Jobs::MONKEY,
            Jobs::SPACE_NINJA,
            Jobs::OPERATIVE,
            Jobs::SYNDICATE_MUTINEER,
            Jobs::REVOLUTIONARY,
            Jobs::REVENANT,
            Jobs::HEAD_REVOLUTIONARY,
            Jobs::SYNDICATE,
            Jobs::TRAITOR,
            Jobs::WIZARD,
            Jobs::HIVEMIND_HOST,
            Jobs::HERETIC,
            Jobs::NIGHTMARE,
            Jobs::NUCLEAR_OPERATIVE
                => '#830000',
            Jobs::OBSERVER => '#fff'
        };
    }

    public function getDepartment(): ?Departments
    {
        return match ($this) {
            default => null,
            Jobs::ASSISTANT => Departments::SERVICE,
            Jobs::LIVING,
            Jobs::GHOST,
            Jobs::ADMIN,
            Jobs::UNKNOWN,
            Jobs::OBSERVER
                => Departments::SPECIAL,
            Jobs::AI, Jobs::CYBORG => Departments::SILICON,
            Jobs::ATMOS_TECH, Jobs::ENGINEER => Departments::ENGINEERING,
            Jobs::BITRUNNER,
            Jobs::MINER,
            Jobs::CARGO_TECH
                => Departments::CARGO,
            Jobs::BARTENDER,
            Jobs::BOTANIST,
            Jobs::CHAPLAIN,
            Jobs::CLOWN,
            Jobs::COOK,
            Jobs::CURATOR,
            Jobs::JANITOR,
            Jobs::LAWYER,
            Jobs::LIBRARIAN,
            Jobs::MIME,
            Jobs::PSYCHOLOGIST
                => Departments::SERVICE,
            Jobs::CHEMIST,
            Jobs::CORONER,
            Jobs::PARAMEDIC,
            Jobs::DOCTOR,
            Jobs::VIROLOGIST
                => Departments::MEDICAL,
            Jobs::GENETICIST,
            Jobs::SCIENTIST,
            Jobs::ROBOTICIST
                => Departments::SCIENCE,
            Jobs::SECURITY,
            Jobs::DETECTIVE,
            Jobs::WARDEN
                => Departments::SECURITY,
            Jobs::CAPTAIN,
            Jobs::CHIEF_ENGIE,
            Jobs::CMO,
            Jobs::RD,
            Jobs::HOP,
            Jobs::QM,
            Jobs::HOS
                => Departments::COMMAND,
            Jobs::ABDUCTOR,
            Jobs::XENOMORPH,
            Jobs::BLOB,
            Jobs::BLOOD_BROTHER,
            Jobs::CHANGELING,
            Jobs::CULTIST,
            Jobs::INTERNAL_AFFAIRS_AGENT,
            Jobs::MALF,
            Jobs::MONKEY,
            Jobs::SPACE_NINJA,
            Jobs::OPERATIVE,
            Jobs::SYNDICATE_MUTINEER,
            Jobs::REVOLUTIONARY,
            Jobs::REVENANT,
            Jobs::HEAD_REVOLUTIONARY,
            Jobs::SYNDICATE,
            Jobs::TRAITOR,
            Jobs::WIZARD,
            Jobs::HIVEMIND_HOST,
            Jobs::HERETIC,
            Jobs::NIGHTMARE,
            Jobs::NUCLEAR_OPERATIVE
                => Departments::ANTAG
        };
    }

    public function getIcon(): ?string
    {
        if ($this->getDepartment()) {
            $icon = $this->getDepartment()->getIcon();
        }
        $icon = match ($this) {
            default => $icon,
            Jobs::ADMIN => 'fa-solid fa-shield-halved',
            Jobs::ASSISTANT => 'fa-solid fa-toolbox',
            Jobs::ATMOS_TECH => 'fa-spin fa-solid fa-fan',
            Jobs::BLOB => 'fa-solid fa-person-rays',
            Jobs::BLOOD_BROTHER => 'fa-solid fa-people-arrows',
            Jobs::BOTANIST => 'fa-solid fa-seedling',
            Jobs::CAPTAIN => 'fa-solid fa-crown',
            Jobs::CARGO_TECH => 'fa-solid fa-people-carry-box',
            Jobs::CHANGELING => 'fa-solid fa-people-arrows',
            Jobs::CHAPLAIN => 'fa-solid fa-dove',
            Jobs::CHEMIST => 'fa-solid fa-prescription-bottle',
            Jobs::CHIEF_ENGIE => 'fa-solid fa-atom',
            Jobs::CLOWN => 'fa-solid fa-face-grin-tears',
            Jobs::CMO => 'fa-solid fa-staff-snake',
            Jobs::COOK => 'fa-solid fa-utensils',
            Jobs::CORONER => 'fa-solid fa-skull',
            Jobs::CULTIST => 'fa-solid fa-users-line',
            Jobs::CURATOR => 'fa-solid fa-book',
            Jobs::DETECTIVE => 'fa-solid fa-user-secret',
            Jobs::DOCTOR => 'fa-solid fa-user-doctor',
            Jobs::GENETICIST => 'fa-solid fa-dna',
            Jobs::GHOST => 'fa-solid fa-ghost',
            Jobs::HOP => 'fa-solid fa-dog',
            Jobs::JANITOR => 'fa-solid fa-broom',
            Jobs::LAWYER => 'fa-solid fa-scale-balanced',
            Jobs::LIVING => 'fa-solid fa-person-running',
            Jobs::MIME => 'fa-solid fa-comment-slash',
            Jobs::MINER => 'fa-solid fa-person-digging',
            Jobs::NUCLEAR_OPERATIVE => 'fa-solid fa-user-astronaut',
            Jobs::PRISONER => 'fa-solid fa-lock',
            Jobs::PSYCHOLOGIST => 'fa-solid fa-brain',
            Jobs::QM => 'fa-solid fa-sack-dollar',
            Jobs::REVOLUTIONARY => 'fa-solid fa-people-group',
            Jobs::RD => 'fa-solid fa-user-graduate',
            Jobs::ROBOTICIST => 'fa-solid fa-battery-half',
            Jobs::SPACE_NINJA => 'fa-solid fa-user-ninja',
            Jobs::TRAITOR => 'fa-solid fa-person-falling-burst',
            Jobs::VIROLOGIST => 'fa-solid fa-biohazard',
            Jobs::WARDEN => 'fa-solid fa-handcuffs',
            Jobs::HEAD_REVOLUTIONARY => 'fa-solid fa-hand-fist',
            Jobs::WIZARD => 'fa-solid fa-wand-magic-sparkles',
            Jobs::REVENANT => 'fa-solid fa-person-dots-from-line',
            Jobs::NIGHTMARE => 'fa-solid fa-skull-crossbones',
            Jobs::ABDUCTOR => 'fa-solid fa-street-view',
            Jobs::OBSERVER => 'fa-solid fa-ghost'
        };
        return $icon;
    }

    public function getForeColor(): string
    {
        return match ($this) {
            default => LuminosityContrast::getContrastColor($this->getColor()),
            Jobs::AI, Jobs::CYBORG => '#00FF00',
            Jobs::LIVING, Jobs::OBSERVER => '#000'
        };
    }

    public function getStyleString(): string
    {
        return sprintf(
            'background: %s; color: %s',
            $this->getColor(),
            $this->getForeColor()
        );
    }

    public function includeInGraph(): bool
    {
        return match ($this) {
            default => true,
            Jobs::LIVING,
            Jobs::ADMIN,
            Jobs::GHOST,
            Jobs::ABDUCTOR,
            Jobs::XENOMORPH,
            Jobs::BLOB,
            Jobs::BLOOD_BROTHER,
            Jobs::CHANGELING,
            Jobs::CULTIST,
            Jobs::INTERNAL_AFFAIRS_AGENT,
            Jobs::MALF,
            Jobs::MONKEY,
            Jobs::SPACE_NINJA,
            Jobs::OPERATIVE,
            Jobs::SYNDICATE_MUTINEER,
            Jobs::REVOLUTIONARY,
            Jobs::REVENANT,
            Jobs::HEAD_REVOLUTIONARY,
            Jobs::SYNDICATE,
            Jobs::TRAITOR,
            Jobs::WIZARD,
            Jobs::HIVEMIND_HOST,
            Jobs::HERETIC,
            Jobs::NIGHTMARE,
            Jobs::NUCLEAR_OPERATIVE,
            Jobs::OBSERVER
                => false
        };
    }
}
