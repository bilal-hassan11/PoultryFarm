<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\StockTrait;

class ChickInvoice extends Model
{
    use HasFactory, SoftDeletes, StockTrait;
    protected $table = "chick_invoices";
    protected $fillable = [
        'date',
        'invoice_no',
        'shade_id',
        'account_id',
        'ref_no',
        'description',
        'item_id',
        'unit',
        'purchase_price',
        'sale_price',
        'quantity',
        'amount',
        'discount_in_rs',
        'discount_in_percentage',
        'net_amount',
        'expiry_date',
        'type',
        'stock_type',
        'is_draft',
        'whatsapp_status',
        'remarks',
    ];

    /**
     * Get the account associated with the feed invoice.
     */
    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Get the item associated with the feed invoice.
     */
    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function shade()
    {
        return $this->belongsTo(shade::class);
    }

    public function expiryStocks()
    {
        return $this->hasMany(ExpiryStock::class);
    }
}
