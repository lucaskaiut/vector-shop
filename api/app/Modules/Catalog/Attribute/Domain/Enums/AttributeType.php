<?php

namespace App\Modules\Catalog\Attribute\Domain\Enums;

enum AttributeType: string
{
    case SELECT = 'select';
    case MULTIPLE_SELECT = 'multiple_select';
    case TEXT = 'text';
    case DATE = 'date';

    public static function values(): array
    {
        return array_map(static fn (self $type) => $type->value, self::cases());
    }

    public static function optionable(): array
    {
        return [
            self::SELECT->value,
            self::MULTIPLE_SELECT->value,
        ];
    }

    public function label(): string
    {
        return match ($this) {
            self::SELECT => 'Seleção',
            self::MULTIPLE_SELECT => 'Seleção múltipla',
            self::TEXT => 'Texto',
            self::DATE => 'Data',
        };
    }
}


