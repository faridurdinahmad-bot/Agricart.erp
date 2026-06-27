<?php

namespace App\Core\Authorization\Enums;

enum UserRegistrationSource: string
{
    case Admin = 'admin';
    case Registration = 'registration';
}
