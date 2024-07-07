<?php


namespace Ngl5000\CashierConnect\Concerns;

use Ngl5000\CashierConnect\Exceptions\AccountNotFoundException;
use Stripe\Balance;
use Stripe\Exception\ApiErrorException;

/**
 * Manages balance for the Stripe connected account.
 *
 * @package Ngl5000\CashierConnect\Concerns
 */
trait ManagesBalance
{

    /**
     * Retrieve a Stripe Connect account's balance.
     *
     * @return Balance
     * @throws AccountNotFoundException|ApiErrorException
     */
    public function retrieveAccountBalance(): Balance
    {
        $this->assertAccountExists();

        // Create the payload for retrieving balance.
        $options = array_merge([
            'stripe_account' => $this->stripeAccountId(),
        ], $this->stripeAccountOptions());

        return Balance::retrieve($options);
    }

}
