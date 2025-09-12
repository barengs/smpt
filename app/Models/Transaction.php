<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $table = 'transactions';

    protected $guarded = ['id'];

    public function sourceAccount()
    {
        return $this->belongsTo(Account::class, 'source_account', 'account_number');
    }

    public function destinationAccount()
    {
        return $this->hasMany(Account::class, 'destination_account', 'account_number');
    }

    public function transactionType()
    {
        return $this->belongsTo(TransactionType::class, 'transaction_type', 'id');
    }

    public function ledgerEntries()
    {
        return $this->hasMany(TransactionLedger::class, 'transaction_id', 'id');
    }
}
