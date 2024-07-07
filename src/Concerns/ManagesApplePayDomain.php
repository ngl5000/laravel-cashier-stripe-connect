<?php


namespace Ngl5000\CashierConnect\Concerns;

use Ngl5000\CashierConnect\Exceptions\AccountAlreadyExistsException;
use Ngl5000\CashierConnect\Exceptions\AccountNotFoundException;
use Ngl5000\CashierConnect\Models\ConnectMapping;
use Stripe\Account;
use Stripe\ApplePayDomain;
use Stripe\Collection;
use Stripe\Exception\ApiErrorException;

/**
 * Manages a Stripe account for the model.
 *
 * @package Ngl5000\CashierConnect\Concerns
 */
trait ManagesApplePayDomain
{

    /**
     * @param $domain
     * @return ApplePayDomain
     * @throws AccountNotFoundException
     * @throws ApiErrorException
     */
    public function addApplePayDomain($domain): ApplePayDomain
    {
        $this->assertAccountExists();
        return ApplePayDomain::create(['domain_name' => $domain], $this->stripeAccountOptions([], true));

    }

    /**
     * @return Collection
     * @throws AccountNotFoundException
     * @throws ApiErrorException
     */
    public function getApplePayDomains(): Collection
    {
        $this->assertAccountExists();
        return ApplePayDomain::all([], $this->stripeAccountOptions([], true));
    }

}
