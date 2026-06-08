<?php

namespace Tests\Unit\Rules;

use App\Rules\DomainName;
use App\Rules\PersonName;
use App\Rules\SiteName;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ValidationRulesTest extends TestCase
{
    /** @param  array<string, mixed>  $data */
    private function validate(array $data, object $rule, string $field = 'value'): bool
    {
        return Validator::make($data, [$field => ['required', $rule]])->passes();
    }

    public function test_person_name_accepts_unicode_and_apostrophe(): void
    {
        $rule = new PersonName;

        $this->assertTrue($this->validate(['value' => 'José García'], $rule));
        $this->assertTrue($this->validate(['value' => "O'Brien Smith"], $rule));
        $this->assertTrue($this->validate(['value' => 'Mary-Jane Watson'], $rule));
        $this->assertTrue($this->validate(['value' => 'Test User'], $rule));
    }

    public function test_person_name_rejects_invalid_values(): void
    {
        $rule = new PersonName;

        $this->assertTrue($this->validate(['value' => 'John'], $rule));
        $this->assertFalse($this->validate(['value' => 'John <script> Doe'], $rule));
        $this->assertFalse($this->validate(['value' => '123 456'], $rule));
        $this->assertFalse($this->validate(['value' => 'John@Doe'], $rule));
    }

    public function test_site_name_accepts_common_punctuation(): void
    {
        $rule = new SiteName;

        $this->assertTrue($this->validate(['value' => "Tom's Blog"], $rule));
        $this->assertTrue($this->validate(['value' => 'My Site v2'], $rule));
    }

    public function test_site_name_rejects_markup(): void
    {
        $rule = new SiteName;

        $this->assertFalse($this->validate(['value' => '<script>alert(1)</script>'], $rule));
    }

    public function test_domain_name_accepts_valid_hosts(): void
    {
        $rule = new DomainName;

        $this->assertTrue($this->validate(['value' => 'example.com'], $rule));
        $this->assertTrue($this->validate(['value' => 'blog.example.co.uk'], $rule));
        $this->assertTrue($this->validate(['value' => 'localhost'], $rule));
    }

    public function test_domain_name_rejects_invalid_hosts(): void
    {
        $rule = new DomainName;

        $this->assertFalse($this->validate(['value' => 'not a domain'], $rule));
        $this->assertFalse($this->validate(['value' => 'example.com/path'], $rule));
        $this->assertFalse($this->validate(['value' => 'user@example.com'], $rule));
    }
}
