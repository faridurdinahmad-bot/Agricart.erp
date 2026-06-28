<?php

namespace App\Modules\Catalog\Services;

use App\Core\Numbering\EntityCodeGenerator;

final class BrandCodeGenerator
{
    public static function next(): string
    {
        return EntityCodeGenerator::next(EntityCodeGenerator::prefixFor('brand'));
    }
}
