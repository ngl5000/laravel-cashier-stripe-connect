<?php

namespace Ngl5000\CashierConnect\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Ngl5000\CashierConnect\Models\ConnectCustomer;
use Ngl5000\CashierConnect\Exceptions\AccountAlreadyExistsException;
use Ngl5000\CashierConnect\Exceptions\AccountNotFoundException;
use Ngl5000\CashierConnect\Models\ConnectMapping;
use Stripe\Customer;
use Stripe\Exception\ApiErrorException;

trait ManageCustomer
{
    /**
     * Load the customer mapping coming from our customer model
     */
    public function stripeCustomerMapping(Model $connectedAccountBillableModel): BelongsTo
    {
        return $this->belongsTo(ConnectCustomer::class, $this->primaryKey, $this->getLocalIDField())
                    ->where('model', '=', get_class($this))
                    ->where('stripe_account_id', '=', $connectedAccountBillableModel->stripeAccountId());
    }

    /**
     * Retrieve the Stripe account ID.
     * From an already loaded stripe customer mapping
     * @return string|null
     */
    public function stripeAccountId(): ?string
    {
        return $this->stripeCustomerMapping->stripe_account_id;
    }

    /**
     * Retrieve the Stripe customer ID.
     * From an already loaded stripe customer mapping
     * @return string|null
     */
    public function stripeCustomerId(): ?string
    {
        return $this->stripeCustomerMapping->stripe_customer_id;
    }

    /**
     * Checks if the model exists as a stripe customer
     * @return mixed
     */
    public function hasCustomerRecord(?Model $connectedAccountBillableModel = null): bool
    {
        $query = $this->stripeCustomerMapping();

        if ($connectedAccountBillableModel) {
            $query->where('stripe_account_id', $connectedAccountBillableModel->stripeAccountId());
        }
    
        return $query->exists();
    }

    /**
     * Creates a customer against a connected account, the first parameter must be a model that has
     * the billable trait and also exists as a stripe connected account
     * @param $connectedAccount
     * @param array $customerData
     * @return Customer
     * @throws AccountAlreadyExistsException
     * @throws AccountNotFoundException
     */
    public function createStripeCustomer(Model $connectedAccountBillableModel, array $customerData = []): Customer
    {
        // Check if model already has a connected Stripe account.
        if ($this->hasCustomerRecord($connectedAccountBillableModel)) {
            throw new AccountAlreadyExistsException('Customer account already exists.');
        }

        $customer = Customer::create($customerData, $this->stripeAccountOptions($connectedAccountBillableModel));

        // Save the id.
        $this->stripeCustomerMapping()->create([
            "stripe_customer_id" => $customer->id,
            "stripe_account_id" => $connectedAccountBillableModel->stripeAccountId(),
            "model" => get_class($this),
            $this->getLocalIDField() => $this->{$this->primaryKey}
        ]);

        $this->save();

        return $customer;
    }

    /**
     * Deletes the Stripe customer for the model.
     *
     * @return Customer
     * @throws AccountNotFoundException|ApiErrorException
     */
    public function deleteStripeCustomer(): Customer
    {
        $this->assetCustomerExists();

        // Process account delete.
        $customer = Customer::retrieve($this->stripeCustomerId(), $this->stripeAccountOptions($this->retrieveHostConnectedAccount()));
        $customer->delete();

        // Wipe account id reference from model.
        $this->stripeCustomerMapping()->delete();

        return $customer;
    }

    /**
     * Provides support for UUID based models
     * @return string
     */
    private function getLocalIDField(): string
    {
        if($this->incrementing){
            return 'model_id';
        }else{
            return 'model_uuid';
        }

    }

    /**
     * Provides support for UUID based models
     * @return string
     */
    private function getHostIDField(ConnectMapping $connectedAccount): string
    {
        if($connectedAccount->model_id){
            return 'model_id';
        }else{
            return 'model_uuid';
        }
    }
}
