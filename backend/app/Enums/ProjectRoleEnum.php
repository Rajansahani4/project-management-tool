<?php

namespace App\Enums;

enum ProjectRoleEnum: string
{
    case Owner  = 'owner';
    case Admin  = 'admin';
    case Member = 'member';
}
