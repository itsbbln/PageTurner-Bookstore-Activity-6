<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = ['order_id', 'book_id', 'quantity', 'unit_price'];
    protected $appends = ['subtotal'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * Get the subtotal for this order item
     */
    public function getSubtotalAttribute()
    {
        return $this->quantity * $this->unit_price;
    }
}