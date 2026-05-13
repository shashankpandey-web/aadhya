<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerWallet extends Model
{
    use HasFactory;

    protected $table = 'customer_wallet';


    public static function getTotalAmount($customerId)
    {
        $getCustomers = Customers::where('id', $customerId)->first();
        if ($getCustomers->type == 'Advisor') {
            return self::where('customer_id', $customerId)
                ->selectRaw("
                    SUM(CASE 
                        WHEN type = 'Credit' AND is_withdrawal IS NULL THEN amount 
                        ELSE 0 
                    END) - 
                    SUM(CASE 
                        WHEN type = 'Debit' AND is_withdrawal = 1 THEN amount 
                        ELSE 0 
                    END) AS total_amount
                ")
                ->value('total_amount');
        }

        if ($getCustomers->type == 'Customer') {
            return self::where('customer_id', $customerId)
                ->selectRaw("
                    SUM(CASE 
                        WHEN type = 'Credit' AND is_withdrawal IS NULL AND status = 'SUCCESS' THEN amount 
                        ELSE 0 
                    END) - 
                    SUM(CASE 
                        WHEN type = 'Debit' AND is_withdrawal IS NULL THEN amount 
                        ELSE 0 
                    END) AS total_amount
                ")
                ->value('total_amount');
        }
    }
}
