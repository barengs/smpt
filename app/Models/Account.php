<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;

    protected $primaryKey = 'account_number';
    public $incrementing = false;
    protected $keyType = 'string';
    // protected $guarded = ['account_number'];
    protected $fillable = [
        'account_number',
        'customer_id',
        'product_id',
        'balance',
        'status',
        'open_date',
        'close_date',
    ];

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
