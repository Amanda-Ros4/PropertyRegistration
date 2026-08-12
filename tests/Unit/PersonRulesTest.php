<?php

namespace Tests\Unit;

use App\Rules\MustBeAdult;
use App\Rules\ValidBrazilianMobile;
use App\Rules\ValidCpf;
use App\Support\BirthDate;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class PersonRulesTest extends TestCase
{
    public function test_valid_cpf_passes(): void
    {
        $validator = Validator::make(
            ['cpf' => '39053344705'],
            ['cpf' => [new ValidCpf]]
        );

        $this->assertFalse($validator->fails());
    }

    public function test_invalid_cpf_fails(): void
    {
        $validator = Validator::make(
            ['cpf' => '11111111111'],
            ['cpf' => [new ValidCpf]]
        );

        $this->assertTrue($validator->fails());
    }

    public function test_brazilian_mobile_accepts_eleven_digits_with_ninth_digit(): void
    {
        $validator = Validator::make(
            ['phone' => '(11) 98765-4321'],
            ['phone' => [new ValidBrazilianMobile]]
        );

        $this->assertFalse($validator->fails());
    }

    public function test_brazilian_mobile_rejects_landline_format(): void
    {
        $validator = Validator::make(
            ['phone' => '(11) 3456-7890'],
            ['phone' => [new ValidBrazilianMobile]]
        );

        $this->assertTrue($validator->fails());
    }

    public function test_adult_birth_date_passes(): void
    {
        $validator = Validator::make(
            ['birth_date' => now()->subYears(18)->toDateString()],
            ['birth_date' => [new MustBeAdult]]
        );

        $this->assertFalse($validator->fails());
    }

    public function test_underage_birth_date_fails(): void
    {
        $validator = Validator::make(
            ['birth_date' => now()->subYears(17)->toDateString()],
            ['birth_date' => [new MustBeAdult]]
        );

        $this->assertTrue($validator->fails());
    }

    public function test_birth_date_normalizes_brazilian_format(): void
    {
        $this->assertSame('1990-08-30', BirthDate::toIso('30/08/1990'));
        $this->assertSame('1990-08-30', BirthDate::toIso('1990-08-30'));
    }
}
