<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCartTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('cart', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('order_id')->unique();
			$table->integer('user')->unsigned()->index('cart_user_foreign');
			$table->text('items')->nullable();
			$table->text('address')->nullable();
			$table->text('fio')->nullable();
			$table->char('tel')->nullable();
			$table->char('email')->nullable();
			$table->float('cost', 10)->default(0.00);
			$table->float('cost_discount', 10);
			$table->text('discount');
			$table->char('kupon')->nullable();
			$table->char('status_order')->default('');
			$table->char('status_pay')->default('');
			$table->char('method_pay')->default('');
			$table->char('method_delivery')->default('');
			$table->text('comment')->nullable();
			$table->text('comment_admin')->nullable();
			$table->integer('position')->default(0);
			$table->dateTime('pay_at')->nullable();
			$table->integer('invoiceId')->nullable();
			$table->timestamps();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('cart');
	}

}
