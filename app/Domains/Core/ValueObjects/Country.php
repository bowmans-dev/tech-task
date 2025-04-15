<?php

namespace App\Domains\Core\ValueObjects;

class Country
{
    private string $country;

    private const VALID_COUNTRIES = [
        'United Kingdom',
        'United States',
        'Canada',
        'France',
        'Germany',
    ];

    public function __construct(string $country)
    {
        if (! in_array($country, self::VALID_COUNTRIES, true)) {
            throw new \InvalidArgumentException("Invalid country: {$country}");
        }

        $this->country = $country;
    }

    public function getValue(): string
    {
        return $this->country;
    }

    public function isEqual(Country $otherCountry): bool
    {
        return $this->country === $otherCountry->getValue();
    }
}
