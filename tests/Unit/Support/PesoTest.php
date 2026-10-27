<?php

namespace Tests\Unit\Support;

use App\Support\Peso;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

class PesoTest extends TestCase
{
    #[TestWith(['1500.50', 150050])]
    #[TestWith(['0.29', 29])]
    #[TestWith(['9999999999.99', 999999999999])]
    public function test_converts_pesos_to_whole_centavos(string $pesos, int $centavos): void
    {
        $this->assertSame($centavos, Peso::toCentavos($pesos));
    }

    public function test_converts_centavos_back_to_pesos_to_two_places(): void
    {
        $this->assertSame('33333.33', Peso::fromCentavos(3333333));
    }

    #[TestWith([150050, '₱1,500.50'])]
    #[TestWith([-750000, '-₱7,500.00'])]
    public function test_formats_centavos_for_display(int $centavos, string $formatted): void
    {
        $this->assertSame($formatted, Peso::format($centavos));
    }
}
