<?php

namespace Ngl5000\CashierConnect;

use Ngl5000\CashierConnect\Concerns\CanCharge;
use Ngl5000\CashierConnect\Concerns\ManagesAccount;
use Ngl5000\CashierConnect\Concerns\ManagesAccountLink;
use Ngl5000\CashierConnect\Concerns\ManagesApplePayDomain;
use Ngl5000\CashierConnect\Concerns\ManagesBalance;
use Ngl5000\CashierConnect\Concerns\ManagesConnectCustomer;
use Ngl5000\CashierConnect\Concerns\ManagesConnectProducts;
use Ngl5000\CashierConnect\Concerns\ManagesConnectSubscriptions;
use Ngl5000\CashierConnect\Concerns\ManagesPaymentLinks;
use Ngl5000\CashierConnect\Concerns\ManagesPerson;
use Ngl5000\CashierConnect\Concerns\ManagesPayout;
use Ngl5000\CashierConnect\Concerns\ManagesTerminals;
use Ngl5000\CashierConnect\Concerns\ManagesTransfer;
use Laravel\Cashier\Cashier;

/**
 * Added to models for Stripe Connect functionality.
 *
 * @package Ngl5000\CashierConnect
 */
trait Billable
{

    use ManagesAccount;
    use ManagesAccountLink;
    use ManagesPerson;
    use ManagesBalance;
    use ManagesTransfer;
    use ManagesPaymentLinks;
    use ManagesConnectCustomer;
    use ManagesConnectSubscriptions;
    use ManagesConnectProducts;
    use CanCharge;
    use ManagesPayout;
    use ManagesApplePayDomain;
    use ManagesTerminals;

    /**
     * The default Stripe API options for the current Billable model.
     *
     * @param array $options
     * @param bool $sendAsAccount
     * @return array
     */
    public function stripeAccountOptions(array $options = [], bool $sendAsAccount = false): array
    {
        // Include Stripe Account id if present. This is so we can make requests on the behalf of the account.
        // Read more: https://stripe.com/docs/api/connected_accounts?lang=php.
        if ($sendAsAccount && $this->hasStripeAccount()) {
            $options['stripe_account'] = $this->stripeAccountId();
        }
        
        // Workaround for Cashier 12.x 
        if (version_compare(Cashier::VERSION, '12.15.0', '<=')) {
            return array_merge(Cashier::stripeOptions($options));
        }

        $stripeOptions = Cashier::stripe($options);
        
        return array_merge($options, [
            'api_key' => $stripeOptions->getApiKey()
        ]);
    }

    /**
     * @param $providedCurrency
     * @return mixed|string
     */
    public function establishTransferCurrency($providedCurrency = null){

        if($providedCurrency){
            return $providedCurrency;
        }

        if($this->defaultCurrency){
            return $this->defaultCurrency;
        }

        return config('cashierconnect.currency');

    }

}
