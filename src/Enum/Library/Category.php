<?php

namespace App\Enum\Library;

enum Category: string
{
    case ANY = 'Any';
    case FICTION = 'Fiction';
    case NONFICTION = 'Non-Fiction';
    case ADULT = 'Adult';
    case REFERENCE = 'Reference';
    case RELIGION = 'Religion';
}

//'Any','Fiction','Non-Fiction','Adult','Reference','Religion'
