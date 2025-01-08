<?php

// Kraut/Model/LocaleInterface.php
 
declare(strict_types=1);

namespace Kraut\Model;


interface LocaleInterface
{
    /**
     * Get the language code.
     *
     * This method returns the language code in ISO 639-1 format.
     *
     * @return string The language code.
     */
    public function getLanguageCode(): string;

    /**
     * Get the language name in the specific language.
     */
    public function getLanguageName(): string;

    /**
     * Get the language name in English.
     */
    public function getEnglishLanguageName(): string;

    /**
     * Get the country code in ISO 3166-1 alpha-2 format.
     *
     * @return string The country code.
     */
    public function getCountryCode(): string;

    /**
     * Get the country name in the specific language.
     */
    public function getCountryName(): string;

    /**
     * Get the country name in English.
     */
    public function getEnglishCountryName(): string;

    /**
     * Get the timezone in IANA format.
     *
     * @return string The timezone.
     */
    public function getTimezone(): string;

    /**
     * Get the first day of the week.
     * 
     * This method returns the first day of the week (0-6).
     * 
     * 0 = Sunday
     * 1 = Monday
     * 2 = Tuesday
     * 3 = Wednesday
     * 4 = Thursday
     * 5 = Friday
     * 6 = Saturday
     *
     * @return int The first day of the week (0-6).
     */
    public function getFirstDayOfWeek(): int;

    /**
     * Get the date format.
     *
     * This method returns the date format as a string.
     *
     * @return string The date format.
     */
    // public function getDateFormat(): string;

    /**
     * Get the time format.
     *
     * This method returns the time format as a string.
     *
     * @return string The time format.
     */
    // public function getTimeFormat(): string;

    /**
     * Get the date and time format.
     *
     * This method returns the date and time format as a string.
     *
     * @return string The date and time format.
     */
    // public function getDateTimeFormat(): string;

    /**
     * Format a date.
     *
     * This method formats a date using the date format.
     *
     * @param int $timestamp The timestamp.
     *
     * @return string The formatted date.
     */
    public function formatDate(int $timestamp): string;

    /**
     * Format a time.
     *
     * This method formats a time using the time format.
     *
     * @param int $timestamp The timestamp.
     *
     * @return string The formatted time.
     */
    public function formatTime(int $timestamp): string;

    /**
     * Format a date and time.
     *
     * This method formats a date and time using the date and time format.
     *
     * @param int $timestamp The timestamp.
     *
     * @return string The formatted date and time.
     */
    public function formatDateTime(int $timestamp): string;

    /**
     * Get the number format.
     *
     * This method returns the number format as a string.
     *
     * @return string The number format.
     */
    // public function getNumberFormat(): string;

    public function formatNumber(float $number, int $decimals = 2): string;

    /**
     * Get the currency code.
     *
     * This method returns the currency code in ISO 4217 format.
     *
     * @return string The currency code.
     */
    // public function getCurrency(): CurrencyInterface;
}