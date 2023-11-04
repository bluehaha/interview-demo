<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTradeLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trade_logs', function (Blueprint $table) {
            $table->id();
            $table->string('vendor', 45)->comment('金流供應商');
            $table->string('type', 30)->comment('付款方式');
            $table->string('status', 10)->default('init')->comment('金流交易狀態');
            $table->string('trade_no')->comment('各金流商的交易編號');
            $table->decimal('amount')->comment('交易費用');
            $table->timestamp('paid_at')->nullable()->comment('付款時間');
            $table->text('first_return_info')->nullable()->comment('初次回傳結果，用於需要兩段式付款');
            $table->text('return_info')->nullable()->comment('最終付款結果');
            $table->timestamps();

            $table->index('trade_no');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('trade_logs');
    }
}
