<?php

namespace App\Enums;

enum NoteViewLevel: string
{
    case Private = 'private';
    case Internal = 'internal';
    case Vendor = 'vendor';
    case Customer = 'customer';
    case Public = 'public';
}

