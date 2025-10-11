<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $primaryKey = 'account_number';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = ['account_number'];

    public function customer()
    {
        return $this->belongsTo(Student::class, 'customer_id', 'id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function movements()
    {
        return $this->hasMany(AccountMovement::class, 'account_number', 'account_number');
    }
}
