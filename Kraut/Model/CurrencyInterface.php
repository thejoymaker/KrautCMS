<?php

// Kraut/Model/LocaleInterface.php

declare(strict_types=1);

namespace Kraut\Model;

interface CurrencyInterface
{
    /**
     * Get the currency symbol or the short name.
     *
     * This method returns the currency symbol or the short name.
     *
     * @return string The currency symbol or the short name.
     */
    public function getShortName(): string;

    public function getName(): string;

    // public function getNamePlural(): string;

    public function getMinorUnitName(): string;

    // public function getMinorUnitNamePlural(): string;

    /**
     * e.g. BTC has 100'000'000 minor units (satoshis)
     * 
     * TODO: Rename to getMinorUnitsInOneMajorUnit
     * 
     * TODO: check if the feature is provided by `brick/money` 
     * 
     * @return int The number of minor units in one major unit.
     */
    public function getOneMajorInMinorUnits(): int;

    /**
     * e.g. CHF has a minimum minor unit of 5 Rappen
     */
    // public function getMinimumMinorUnit(): int;

    // public function getFormatPattern(): string;

    // public function getFormatPatternRounded(int $decimals=2): string;



    /**
     * Get the currency format.
     *
     * This method returns the currency format as a string.
     *
     * @return string The currency format.
     */
    // public function getFormat(): string;

    /**
     * Format a currency.
     *
     * This method formats a currency using the currency format.
     *
     * @param float $number The number.
     * @param int $decimals The number of decimals.
     *
     * @return string The formatted currency.
     */
    public function format(float $number, int $decimals): string;
}