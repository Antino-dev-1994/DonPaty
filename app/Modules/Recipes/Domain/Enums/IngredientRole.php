<?php

namespace App\Modules\Recipes\Domain\Enums;

enum IngredientRole: string
{
    case Flour = 'flour'; case Liquid = 'liquid'; case Fat = 'fat'; case Sweetener = 'sweetener'; case Leavening = 'leavening'; case Salt = 'salt'; case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Flour => 'Harina de referencia', self::Liquid => 'Líquido', self::Fat => 'Grasa',
            self::Sweetener => 'Endulzante', self::Leavening => 'Leudante', self::Salt => 'Sal', self::Other => 'Otro',
        };
    }
}
