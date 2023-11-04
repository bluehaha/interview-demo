<?php

namespace App\Services\Cashier\DTO;

use Spatie\DataTransferObject\DataTransferObject;
use Stripe\Checkout\Session;

class CheckoutResponseData extends DataTransferObject
{
    // 請求是否成功
    public bool $isSuccess;

    // 交易失敗的錯誤訊息
    public ?string $errorMessage;

    // 前端收到回傳後，要執行何種行為
    // - redirect: 直接根據 url 跳轉頁面，有以下幾種情況
    //   - 後端直接付款完成，前端頁面可以直接跳轉到付款完成頁面，Ex: tappay
    //   - 後端取得第三方支付轉帳、超商代碼等資訊，讓前端跳轉到特定頁面呈現，Ex: 蘭新、綠界等
    //   - 需要跳轉到第三方支付網頁，之後第三方透過 webhook 通知後端付款完成，Ex: stripe
    // - form_post: 前端收到回傳後，要將資料放在 form 裡面，並且 submit form，Ex: 蘭新、綠界信用卡支付等
    public string $type;

    // 當 type=redirect 時使用，讓前端頁面跳轉
    public ?string $url;

    // 當 type=form_post 時使用，讓前端頁面透過 submit form 跳轉
    public ?array $formParams;

    // 第三方支付回傳的原始資料
    public array $payload;
}
