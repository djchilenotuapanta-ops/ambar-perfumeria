<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Cliente = 'cliente';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrador General',
            self::Cliente => 'Cliente',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
