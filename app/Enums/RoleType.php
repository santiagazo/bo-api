<?php

namespace App\Enums;

enum RoleType: string
{
    case Global = 'global';
    case System = 'system';
    case Organization = 'organization';

    public function isGlobalScope(): bool
    {
        return in_array($this, [self::Global, self::System], true);
    }
}
