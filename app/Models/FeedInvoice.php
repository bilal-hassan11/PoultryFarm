<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\StockTrait;

class FeedInvoice extends Model
{
    use HasFactory, SoftDeletes, StockTrait;
    protected $table = "feed_invoices";
    protected $fillable = [
        'date',
        'invoice_no',
        'account_id',
        'shade_id',
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

    public function shade()
    {
        return $this->belongsTo(shade::class);
    }
    
    /**
     * Get the item associated with the feed invoice.
     */
    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
