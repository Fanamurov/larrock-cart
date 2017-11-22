<?php

namespace Larrock\ComponentCart\Models;

use Illuminate\Database\Eloquent\Model;
use Larrock\ComponentCatalog\Facades\LarrockCatalog;
use Larrock\ComponentUsers\Facades\LarrockUsers;
use Nicolaslopezj\Searchable\SearchableTrait;
use Larrock\ComponentCart\Facades\LarrockCart;

/**
 * App\Models\Cart
 *
 * @property integer $id
 * @property integer $order_id
 * @property integer $user
 * @property string $items
 * @property float $cost
 * @property float $cost_discount
 * @property string $kupon
 * @property string $status_order
 * @property string $status_pay
 * @property string $method_pay
 * @property string $method_delivery
 * @property string $comment
 * @property integer $position
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\Larrock\ComponentCart\Models\Cart whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\Larrock\ComponentCart\Models\Cart whereOrderId($value)
 * @method static \Illuminate\Database\Query\Builder|\Larrock\ComponentCart\Models\Cart whereUser($value)
 * @method static \Illuminate\Database\Query\Builder|\Larrock\ComponentCart\Models\Cart whereItems($value)
 * @method static \Illuminate\Database\Query\Builder|\Larrock\ComponentCart\Models\Cart whereCost($value)
 * @method static \Illuminate\Database\Query\Builder|\Larrock\ComponentCart\Models\Cart whereCostDiscount($value)
 * @method static \Illuminate\Database\Query\Builder|\Larrock\ComponentCart\Models\Cart whereKupon($value)
 * @method static \Illuminate\Database\Query\Builder|\Larrock\ComponentCart\Models\Cart whereStatusOrder($value)
 * @method static \Illuminate\Database\Query\Builder|\Larrock\ComponentCart\Models\Cart whereStatusPay($value)
 * @method static \Illuminate\Database\Query\Builder|\Larrock\ComponentCart\Models\Cart whereMethodPay($value)
 * @method static \Illuminate\Database\Query\Builder|\Larrock\ComponentCart\Models\Cart whereMethodDelivery($value)
 * @method static \Illuminate\Database\Query\Builder|\Larrock\ComponentCart\Models\Cart whereComment($value)
 * @method static \Illuminate\Database\Query\Builder|\Larrock\ComponentCart\Models\Cart wherePosition($value)
 * @method static \Illuminate\Database\Query\Builder|\Larrock\ComponentCart\Models\Cart whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\Larrock\ComponentCart\Models\Cart whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property string $address
 * @property string $fio
 * @property string $tel
 * @property string $email
 * @property string $comment_admin
 * @property string $pay_at
 * @property integer $invoiceId
 * @property integer $discount_id
 * @method static \Illuminate\Database\Query\Builder|\Larrock\ComponentCart\Models\Cart whereAddress($value)
 * @method static \Illuminate\Database\Query\Builder|\Larrock\ComponentCart\Models\Cart whereFio($value)
 * @method static \Illuminate\Database\Query\Builder|\Larrock\ComponentCart\Models\Cart whereTel($value)
 * @method static \Illuminate\Database\Query\Builder|\Larrock\ComponentCart\Models\Cart whereEmail($value)
 * @method static \Illuminate\Database\Query\Builder|\Larrock\ComponentCart\Models\Cart whereCommentAdmin($value)
 * @method static \Illuminate\Database\Query\Builder|\Larrock\ComponentCart\Models\Cart wherePayAt($value)
 * @method static \Illuminate\Database\Query\Builder|\Larrock\ComponentCart\Models\Cart whereInvoiceId($value)
 * @method static \Illuminate\Database\Query\Builder|\Larrock\ComponentCart\Models\Cart whereDiscountId($value)
 * @property \Illuminate\Support\Collection $discount
 * @method static \Illuminate\Database\Query\Builder|\Larrock\ComponentCart\Models\Cart whereDiscount($value)
 */
class Cart extends Model
{
    use SearchableTrait;

    public function __construct(array $attributes = [])
    {
        $this->fillable(LarrockCart::getFillableRows());
        $this->table = LarrockCart::getConfig()->table;
        parent::__construct($attributes);
    }

    protected $searchable = [
        'columns' => [
            'cart.status_order' => 10,
            'cart.status_pay' => 10,
            'cart.tel' => 20,
            'cart.email' => 20,
            'cart.fio' => 30,
        ]
    ];

	protected $casts = [
		'order_id' => 'integer',
		'cost' => 'float',
		'cost_discount' => 'float',
        'discount' => 'collection'
	];

	public function getItemsAttribute($value)
	{
		$items = json_decode($value);
		if(is_array($items) || is_object($items)){
			foreach($items as $item_key => $item_value){
				$items->{$item_key}->catalog = LarrockCatalog::getModel()->whereId($item_value->id)->with(['getImages'])->first();
			}
			return $items;
		}
        return [];
	}

	public function get_user()
	{
		return $this->hasOne(LarrockUsers::getModelName(), 'id', 'user');
	}
}
