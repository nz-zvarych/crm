<?php

namespace App\Security;

final class UserRoles
{
    public const ADMIN = 'ROLE_ADMIN';
    public const DISTRIBUTOR = 'ROLE_DISTRIBUTOR';
    public const CUSTOMER = 'ROLE_CUSTOMER';

    public static function all(): array
    {
        return [
            self::ADMIN,
            self::DISTRIBUTOR,
            self::CUSTOMER,
        ];
    }

    public static function labels(): array|string
    {
        return [
            'Administrator' => self::ADMIN,
            'Distributor' => self::DISTRIBUTOR,
            'Customer' => self::CUSTOMER
        ];
    }
}
