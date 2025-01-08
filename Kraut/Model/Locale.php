<?php

// Kraut/Model/LocaleInterface.php
 
declare(strict_types=1);

namespace Kraut\Model;

use DateTime;

class Locale implements LocaleInterface
{
    
    public function __construct(
        private string $languageCode = 'en',
        private string $countryCode = 'US',
        private string $timezone = 'America/New_York',
        private int $firstDayOfWeek = 0,
        private string $numberFormatDecimal = '.',
        private string $numberFormatThousand = ','
    )
    {
    }

    public function getLanguageCode(): string
    {
        return $this->languageCode;
    }

    public function getLanguageName(): string
    {
        // Implement logic to return the language name
        return "NIY";
    }

    public function getEnglishLanguageName(): string
    {
        // Implement logic to return the English language name
        return "NIY";
    }

    public function getCountryCode(): string
    {
        return $this->countryCode;
    }

    public function getCountryName(): string
    {
        // Implement logic to return the country name
        return "NIY";
    }

    public function getEnglishCountryName(): string
    {
        // Implement logic to return the English country name
        return "NIY";
    }

    public function getTimezone(): string
    {
        return $this->timezone;
    }

    public function getFirstDayOfWeek(): int
    {
        return $this->firstDayOfWeek;
    }

    public function formatDate(int $date): string
    {
        // Implement logic to format the date
        return "NIY";
    }

    public function formatTime(int $time): string
    {
        // Implement logic to format the time
        return "NIY";
    }

    public function formatDateTime(int $dateTime): string
    {
        // Implement logic to format the date and time
        return "NIY";
    }

    public function formatNumber(float $number, int $decimals = 2): string
    {
        return number_format($number, $decimals, $this->numberFormatDecimal, $this->numberFormatThousand);
    }
}