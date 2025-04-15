<?php

namespace Tests\Unit;

use App\Domains\Core\ValueObjects\Country;
use Tests\TestCase;

class CountryTest extends TestCase
{
    public function test_can_create_country_with_valid_name()
    {
        // Arrange
        $validCountry = 'United Kingdom';

        // Act
        $country = new Country($validCountry);

        // Assert
        $this->assertInstanceOf(Country::class, $country);
        $this->assertEquals($validCountry, $country->getValue());
    }

    public function test_throws_exception_for_invalid_country()
    {
        // Arrange
        $invalidCountry = 'Australia';

        // Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid country: {$invalidCountry}");

        // Act
        new Country($invalidCountry);
    }

    public function test_is_equal_returns_true_for_same_country()
    {
        // Arrange
        $country1 = new Country('United States');
        $country2 = new Country('United States');

        // Act
        $result = $country1->isEqual($country2);

        // Assert
        $this->assertTrue($result);
    }

    public function test_is_equal_returns_false_for_different_countries()
    {
        // Arrange
        $country1 = new Country('France');
        $country2 = new Country('Germany');

        // Act
        $result = $country1->isEqual($country2);

        // Assert
        $this->assertFalse($result);
    }
}
