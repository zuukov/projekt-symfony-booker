<?php

namespace App\Entity;

enum UserRole: string
{
    case ADMIN = 'admin';
    case BUSINESS_OWNER = 'business_owner';
    case USER = 'user';
}
