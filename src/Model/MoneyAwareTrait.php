<?php

declare(strict_types=1);

namespace Groshy\Model;

use Money\Currencies\ISOCurrencies;
use Money\Currency;
use Money\Formatter\DecimalMoneyFormatter;
use Money\Money;
use Money\Parser\DecimalMoneyParser;

trait MoneyAwareTrait
{
    protected function formatMoney(?Money $amount): ?array
    {
        if (is_null($amount)) {
            return null;
        }

        return [
            'base' => (new DecimalMoneyFormatter(new ISOCurrencies()))->format($amount),
            'minor' => $amount->getAmount(),
            'currency' => $amount->getCurrency()->getCode(),
        ];
    }

    protected function parseMoney(string|float|null $amount): ?Money
    {
        if (is_null($amount)) {
            return null;
        }

        return (new DecimalMoneyParser(new ISOCurrencies()))->parse($amount, $this->defaultCurrency());
    }

    protected function createMoney(?int $amount): ?Money
    {
        if (is_null($amount)) {
            return null;
        }

        return new Money($amount, $this->defaultCurrency());
    }

    protected function defaultCurrency(): Currency
    {
        return new Currency('USD');
    }

    protected function calculateRatio(Money $amount1, Money $amount2): float
    {
        return floatval($amount1->getAmount() / $amount2->getAmount());
    }

    protected function subAndFormatBase(Money $money, int $sub): string
    {
        return $this->formatBase($money->subtract(new Money($sub, $this->defaultCurrency())));
    }

    protected function addAndFormatBase(Money $money, int $sub): string
    {
        return $this->formatBase($money->add(new Money($sub, $this->defaultCurrency())));
    }

    protected function formatBase(Money $money): string
    {
        return (new DecimalMoneyFormatter(new ISOCurrencies()))->format($money);
    }

    protected function fromMinor(int $amount): Money
    {
        return new Money($amount, $this->defaultCurrency());
    }

    protected function fromBase(float|string $amount, ?Currency $currency = null): Money
    {
        if (is_null($currency)) {
            $currency = $this->defaultCurrency();
        }

        return (new DecimalMoneyParser(new ISOCurrencies()))->parse(strval($amount), $currency);
    }

    protected function fromMinorToBase(int $amount): string
    {
        return $this->formatBase($this->fromMinor($amount));
    }
}
