<?php

namespace App\Services\Cashier\DTO;

use Spatie\DataTransferObject\DataTransferObject;

class CheckoutData extends DataTransferObject
{
    // 結帳商品資訊
    public CheckoutItemCollection $items;

    // 結帳模式，第三方支付不是每一種模式都支援，請先閱讀相關文件
    // - payment: 一次性付款
    // - subscription: 訂閱制服款
    // - setup: 先設定支付訊息，晚點再收款
    public string $mode = 'payment';

    // 消費者完成交易後，要導向的網址
    public ?string $successUrl;

    // 消費者取消交易後，要導向的網址
    public ?string $cancleUrl;

    // 幣別
    public ?string $currency;

    // 消費者在第三方支付的 ID
    public ?string $customerOuterCode;

    // 消費者的電子郵件
    public ?string $customerEmail;
}
