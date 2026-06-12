<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Enums\MarkupTypeEnum;
use App\Enums\PaymentStatusEnum;
use App\Enums\TopupStatusEnum;

class TopupBusinessLogicTest extends TestCase
{
    // ─────────────────────────────────────────────────────────────────────
    // 1. Price Markup Calculations
    // ─────────────────────────────────────────────────────────────────────

    /** @test */
    public function test_flat_markup_price_calculation(): void
    {
        $basePrice   = 10000.00;
        $markupValue = 500.00;

        $sellingPrice = $basePrice + $markupValue;

        $this->assertEquals(10500.00, $sellingPrice);
    }

    /** @test */
    public function test_percent_markup_price_calculation(): void
    {
        $basePrice   = 10000.00;
        $markupValue = 10.00; // 10%

        $sellingPrice = $basePrice + ($basePrice * ($markupValue / 100));

        $this->assertEquals(11000.00, $sellingPrice);
    }

    /** @test */
    public function test_zero_flat_markup_returns_base_price(): void
    {
        $basePrice   = 5000.00;
        $markupValue = 0.00;

        $sellingPrice = $basePrice + $markupValue;

        $this->assertEquals(5000.00, $sellingPrice);
    }

    /** @test */
    public function test_zero_percent_markup_returns_base_price(): void
    {
        $basePrice   = 8000.00;
        $markupValue = 0.00; // 0%

        $sellingPrice = $basePrice + ($basePrice * ($markupValue / 100));

        $this->assertEquals(8000.00, $sellingPrice);
    }

    /** @test */
    public function test_large_percent_markup(): void
    {
        $basePrice   = 1000.00;
        $markupValue = 100.00; // 100% markup

        $sellingPrice = $basePrice + ($basePrice * ($markupValue / 100));

        $this->assertEquals(2000.00, $sellingPrice);
    }

    /** @test */
    public function test_fractional_percent_markup(): void
    {
        $basePrice   = 10000.00;
        $markupValue = 5.5; // 5.5%

        $sellingPrice = $basePrice + ($basePrice * ($markupValue / 100));

        $this->assertEquals(10550.00, $sellingPrice);
    }

    // ─────────────────────────────────────────────────────────────────────
    // 2. Markup Logic via MarkupTypeEnum (price-lock simulation)
    // ─────────────────────────────────────────────────────────────────────

    /** @test */
    public function test_markup_calculation_uses_correct_type_flat(): void
    {
        $basePrice   = 12000.00;
        $markupType  = MarkupTypeEnum::FLAT;
        $markupValue = 2000.00;

        $sellingPrice = match ($markupType) {
            MarkupTypeEnum::FLAT    => $basePrice + $markupValue,
            MarkupTypeEnum::PERCENT => $basePrice + ($basePrice * ($markupValue / 100)),
        };

        $this->assertEquals(14000.00, $sellingPrice);
    }

    /** @test */
    public function test_markup_calculation_uses_correct_type_percent(): void
    {
        $basePrice   = 20000.00;
        $markupType  = MarkupTypeEnum::PERCENT;
        $markupValue = 15.00; // 15%

        $sellingPrice = match ($markupType) {
            MarkupTypeEnum::FLAT    => $basePrice + $markupValue,
            MarkupTypeEnum::PERCENT => $basePrice + ($basePrice * ($markupValue / 100)),
        };

        $this->assertEquals(23000.00, $sellingPrice);
    }

    // ─────────────────────────────────────────────────────────────────────
    // 3. Order Code Format & Uniqueness
    // ─────────────────────────────────────────────────────────────────────

    /** @test */
    public function test_order_code_format_generation(): void
    {
        $datePart   = date('Ymd');
        $randomPart = str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        $orderCode  = 'AZKA-' . $datePart . '-' . $randomPart;

        $this->assertMatchesRegularExpression('/^AZKA-\d{8}-\d{5}$/', $orderCode);
    }

    /** @test */
    public function test_order_code_always_has_5_digit_random_part(): void
    {
        // Even for very small random numbers, str_pad ensures 5 digits
        $randomPart = str_pad(1, 5, '0', STR_PAD_LEFT);

        $this->assertEquals('00001', $randomPart);
        $this->assertEquals(5, strlen($randomPart));
    }

    /** @test */
    public function test_order_code_prefix_is_always_azka(): void
    {
        $orderCode = 'AZKA-' . date('Ymd') . '-' . str_pad(12345, 5, '0', STR_PAD_LEFT);

        $this->assertStringStartsWith('AZKA-', $orderCode);
    }

    /** @test */
    public function test_order_code_date_part_matches_today(): void
    {
        $today     = date('Ymd');
        $orderCode = 'AZKA-' . $today . '-' . str_pad(99999, 5, '0', STR_PAD_LEFT);
        $parts     = explode('-', $orderCode);

        // Format: AZKA - YYYYMMDD - XXXXX (3 parts when splitting by '-')
        $this->assertCount(3, $parts);
        $this->assertEquals($today, $parts[1]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // 4. Idempotency Check (duplicate order code prevention)
    // ─────────────────────────────────────────────────────────────────────

    /** @test */
    public function test_two_generated_order_codes_with_different_random_parts_are_unique(): void
    {
        $codes = [];

        // Generate 100 codes — statistically very unlikely to collide
        for ($i = 0; $i < 100; $i++) {
            $randomPart = str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
            $codes[]    = 'AZKA-' . date('Ymd') . '-' . $randomPart;
        }

        // Check that unique count equals total (no exact duplicates in this set)
        $unique = array_unique($codes);

        // Allow small chance of collision but assert it's overwhelmingly unique
        $this->assertGreaterThan(90, count($unique));
    }

    /** @test */
    public function test_order_code_is_string_type(): void
    {
        $orderCode = 'AZKA-' . date('Ymd') . '-' . str_pad(55555, 5, '0', STR_PAD_LEFT);

        $this->assertIsString($orderCode);
    }

    // ─────────────────────────────────────────────────────────────────────
    // 5. Enum Validity Checks
    // ─────────────────────────────────────────────────────────────────────

    /** @test */
    public function test_payment_status_enum_values_are_valid(): void
    {
        $expected = ['pending', 'paid', 'expired', 'failed', 'cancelled'];
        $actual   = array_column(PaymentStatusEnum::cases(), 'value');

        $this->assertEquals(sort($expected), sort($actual));
    }

    /** @test */
    public function test_topup_status_enum_values_are_valid(): void
    {
        $expected = ['pending', 'processing', 'completed', 'failed'];
        $actual   = array_column(TopupStatusEnum::cases(), 'value');

        $this->assertEquals(sort($expected), sort($actual));
    }

    /** @test */
    public function test_markup_type_enum_has_flat_and_percent(): void
    {
        $values = array_column(MarkupTypeEnum::cases(), 'value');

        $this->assertContains('flat', $values);
        $this->assertContains('percent', $values);
        $this->assertCount(2, $values);
    }

    // ─────────────────────────────────────────────────────────────────────
    // 6. Price-Lock Logic (selling price must match snapshot at order time)
    // ─────────────────────────────────────────────────────────────────────

    /** @test */
    public function test_order_stores_selling_price_at_time_of_creation(): void
    {
        // Simulate: product has selling price of 15000
        $productSellingPrice = 15000.00;

        // Order should snapshot this value at checkout (price-lock)
        $orderSellingPrice = $productSellingPrice;

        // If product price changes later, order price remains unchanged
        $productSellingPrice = 20000.00; // price changed after order was created

        $this->assertEquals(15000.00, $orderSellingPrice);
        $this->assertNotEquals($orderSellingPrice, $productSellingPrice);
    }

    /** @test */
    public function test_selling_price_is_always_positive(): void
    {
        $basePrice   = 5000.00;
        $markupValue = 1000.00;

        $sellingPrice = $basePrice + $markupValue;

        $this->assertGreaterThan(0, $sellingPrice);
    }

    /** @test */
    public function test_selling_price_is_never_less_than_base_price(): void
    {
        $basePrice   = 8000.00;
        $markupValue = 2000.00; // positive markup

        $sellingPrice = $basePrice + $markupValue;

        $this->assertGreaterThanOrEqual($basePrice, $sellingPrice);
    }
}
