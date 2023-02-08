<?php

declare(strict_types=1);

namespace App\Services\CommissionCalculator;

use App\DTOs\Transaction;
use App\Enums\EuCountryCodes;
use App\Services\CountryCodeProvider\CountryCodeProviderInterface;
use App\Services\CurrencyRateProvider\CurrencyRateProviderInterface;

class CommissionCalculator implements CommissionCalculatorInterface
{
    private const BASE_CURRENCY = 'EUR';

    private const EU_COMMISSION = 0.01;

    private const NON_EU_COMMISSION = 0.02;

    public function __construct(
        private readonly CountryCodeProviderInterface $binCountryProvider,
        private readonly CurrencyRateProviderInterface $currencyRateProvider
    ) {
    }

    public function calculate(Transaction $transaction): float
    {
        $rates = $this->currencyRateProvider->getAllRates();

        $rate = $transaction->getCurrency() === self::BASE_CURRENCY ? 1 : $rates[$transaction->getCurrency()];

        $countryCode = $this->binCountryProvider->getCountryCodeByBin($transaction->getBin());

        $baseAmount = $transaction->getAmount() / $rate;

        return round(
            EuCountryCodes::getByName($countryCode) !== null
                ? $baseAmount * self::EU_COMMISSION
                : $baseAmount * self::NON_EU_COMMISSION,
            2
        );
    }
}
