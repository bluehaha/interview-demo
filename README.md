# 2nd Interveiw

## 設計邏輯

### 物件職責

![Cashier Service.drawio (1).png](https://hackmd.io/_uploads/BJbTh6Vmp.png)

上圖的 UML 列出了各物件間的依賴，此設計使用工廠、策略模式的方式保留擴產點，預留未來擴充其他第三方金流的可能

- CashierService: 上層物件，負責商業邏輯，呼叫 checkout 的同時，可以用來建立訂單等邏輯
- CashierDriverFactory: 工廠模式，負責建立對應的金流供應商物件
- AbstractCashierDriver: 策略模式，定義出金流的抽象邏輯（通用邏輯），讓後續的 Driver 遵守
- StripeDriver: Stripe 金流依照 AbstractCashierDriver 的實作
- OtherDriver: 未來其他金流商的實作

### ERM

![Cashier service ERM.drawio.png](https://hackmd.io/_uploads/BJlIfCV7p.png)

此處僅作為示範用，對欄位做了一些簡化，現實中欄位會有更多內容

- Product: 商品資料
    - name: 商品名稱
- Price: 價格資料
    - product_id: product id
    - amount: 金額，使用 decimal 避免小數點不準確問題
    - currency: 幣別
    - stripe_id: 專門用來儲存 Stripe price id，使用 Stripe 付款時會用到，依照單一職責來看，可以將此欄位獨立出一張表，這邊就簡單處理，共用在 prices 表
- TradeLog: 交易紀錄
    - vendor: 金流供應商
    - type: 付款類型，可以是信用卡、虛擬 ATM、Apple Pay 等
    - status: 狀態，可以是 init, paid, fail 等
    - trade_no: 第三方交易序號，接收 webhook 或是對帳時會用到
    - amount: 交易金額，使用 decimal，理由同上
    - paid_at: 付款時間，經營多國業務的話，建議是用 timestamp
    - first_return_info: 金流商第一次請求的結果，以 stripe 來說就是建立 session 的回傳資料
    - return_info: 金流商第二次回傳的結果，以 stripe 來說就是交易成功或失敗的 webook 資料

### 使用方式

```php
use App\Models\Product;
use App\Services\Cashier\DTO\CheckoutData;
use App\Services\Cashier\CashierService;

$product = Product::first();
$checkoutData = new CheckoutData([
    'items' => [
        ['product' => $product, 'quantity' => 2],
    ],
    'mode' => 'payment',
    'successUrl' => 'https://example.com',
]);
$service = app(CashierService::class);
$resp = $service->setType('stripe')->checkout($checkoutData);

```

- 此處有一個假設，就是商品資訊都事先同步到 stripe 上，並將 stripe id 儲存在 DB，所以在結帳時，會直接使用 Product model
- 結帳時，使用 Product model 有另一個好處是，後端會根據資料重新計算一次金額，而不是仰賴前端傳送金額，避免被篡改金額的風險


### 其他小細節

- 在傳遞跟接收資料時，使用 `CheckoutData`, `CheckoutResponseData` DTO 物件，可以讓後續的使用者更輕易理解有哪些參數
- 多國金流條件下，會涉及到小數點，計算機在儲存小數點會有不準確問題，所以要使用 Decimal，PHP 解法如下
    - 安裝 Decimal 插件，並在取得 Model 資料時，將該欄位轉換成 Decimal 物件，本專案使用此做法
    - 儲存數字的時候，直接將單位最小化，用 cent 為單位就不會有小數點問題，只是在畫面呈現上，要注意在轉換一次單位，stripe 就是此做法
- 多國情境下，每個國家的時區都不相同，所以建議用 timestamp 儲存時間，前後端溝通時，轉換成 ISO 8601 標準溝通即可

### Webhook 擴展

在 `app/Http/Controllers/Webhook/StripeController` 展示了如何接收 webhook，並搭配 job 的方式，使回應速度加快，必且在流量巔峰時可以達到削峰填谷的效果

如果為了追求穩定性，會建議將 webhook 獨立出一個服務，單獨的 repo 以及伺服器，都可以增加其穩定性、擴充性

如果未來訊息量真的很大，可以藉由 pubsub 作為緩衝，gateway 會建立在 swoole/cloud function 上，將資料往 pubsub 送，後續再從 pubsub 取得訊息，處理商業邏輯

![即時收單 - 留言時序圖 (1).png](https://hackmd.io/_uploads/SJXcW1H76.png)


