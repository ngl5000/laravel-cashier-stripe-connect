<?php

namespace Ngl5000\CashierConnect\Models;

use Ngl5000\CashierConnect\Billable;
use Illuminate\Database\Eloquent\Model;

class TestModel extends Model
{

    use Billable;

    public $incrementing = false;
    public $defaultCurrency = 'GBP';

}