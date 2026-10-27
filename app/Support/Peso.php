<?php

namespace App\Support;

/**
 * Peso amounts as whole centavos, so splitting a fund never meets float rounding.
 *
 * Decimal columns and form input carry pesos to two places; everything that
 * adds, divides or compares amounts works in centavos.
 */
class Peso
{
    /**
     * Centavos in an amount of pesos, such as "1500.50" from a decimal column.
     */
    public static function toCentavos(string|int|float $pesos): int
    {
        return (int) round((float) $pesos * 100);
    }

    /**
     * Pesos to two decimal places, as a decimal column stores them.
     */
    public static function fromCentavos(int $centavos): string
    {
        return number_format($centavos / 100, 2, '.', '');
    }

    /**
     * The amount for display, such as "₱1,500.50".
     */
    public static function format(int $centavos): string
    {
        return ($centavos < 0 ? '-' : '').'₱'.number_format(abs($centavos) / 100, 2);
    }
}
