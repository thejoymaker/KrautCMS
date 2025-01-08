<?php

// Kraut/Model/Currency.php

declare(strict_types=1);

namespace Kraut\Model;

class Currency implements CurrencyInterface
{
    public function __construct(
        private string $shortName = 'USD',
        private string $name = 'US Dollar',
        private string $minorUnitName = 'Cent',
        private int $oneMajorInMinorUnits = 100
        // number format interface
    )
    {
    }

	public function getShortName(): string
	{
        return $this->shortName;
	}

	public function getName(): string
	{
        return $this->name;
	}

	public function getMinorUnitName(): string
	{
        return $this->minorUnitName;
	}

	public function getOneMajorInMinorUnits(): int
	{
        return $this->oneMajorInMinorUnits;
	}

	public function format(float $amount, int $decimals = 2): string
	{
        return $this->shortName . ' ' . number_format($amount, 2, '.', '\'');
	}
}