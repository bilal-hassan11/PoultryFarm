<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\StockTrait;

class MurghiInvoice extends Model
{
    use HasFactory, SoftDeletes, StockTrait;
    protected $table = "murghi_invoices";
    protected $fillable = [
        'date',
        'invoice_no',
        'shade_id',
        'account_id',
        'ref_no',
        'description',
        'item_id',
        'purchase_price',
        'sale_price',
        'weight',
        'weight_detection',
        'quantity', // Quantity is final weight
        'amount',
        'net_amount',
        'expiry_date',
        'type',
        'stock_type',
        'is_draft',
        'whatsapp_status',
        'remarks',
    ];

    /**
     * Get the account associated with the murghi invoice.
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
     * Get the item associated with the murghi invoice.
     */
    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
