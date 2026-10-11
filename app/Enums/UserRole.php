<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Cashier = 'cashier';
    case Bar = 'bar';
    case Waiter = 'waiter';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::Cashier => 'Kasir',
            self::Bar => 'Barista',
            self::Waiter => 'Waiter',
        };
    }
}
