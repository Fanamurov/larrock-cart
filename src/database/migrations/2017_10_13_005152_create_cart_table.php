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
			$table->integer('order_id')->unique()->unsigned();
			$table->integer('user')->unsigned()->index('cart_user_foreign')->nullable();
			$table->text('items')->nullable();
			$table->text('address')->nullable();
			$table->text('fio')->nullable();
			$table->char('tel')->nullable();
			$table->char('email')->nullable();
			$table->float('cost', 10)->default(0.00);
			$table->float('cost_discount', 10)->nullable();
			$table->text('discount')->nullable();
			$table->char('kupon')->nullable();
			$table->char('status_order')->nullable();
			$table->char('status_pay')->nullable();
			$table->char('method_pay')->nullable();
			$table->char('method_delivery')->nullable();
			$table->text('comment')->nullable();
			$table->text('comment_admin')->nullable();
			$table->integer('position')->default(0);
			$table->dateTime('pay_at')->nullable();
			$table->integer('invoiceId')->nullable();
			$table->timestamps();

            $table->index(['user']);
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
