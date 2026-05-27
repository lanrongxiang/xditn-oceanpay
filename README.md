# xditn/oceanpay

Laravel 大洋支付整合包，包含支付网关抽象、Oceanpay 驱动、支付服务商/通道/币种/支付方式公共表和后台配置接口。

## Installation

```bash
composer require xditn/oceanpay
```

发布配置、迁移和宿主 webhook handler 示例：

```bash
php artisan vendor:publish --tag=oceanpay-config
php artisan vendor:publish --tag=oceanpay-migrations
php artisan vendor:publish --tag=oceanpay-stubs
php artisan migrate
```

## Routes

默认路由前缀：

```text
/api/oceanpay
```

后台配置接口：

```text
GET|POST      /api/oceanpay/admin/payment-providers
GET|PUT|DELETE /api/oceanpay/admin/payment-providers/{id}
GET           /api/oceanpay/admin/payment-providers/options

GET|POST      /api/oceanpay/admin/payment-provider-channels
GET|PUT|DELETE /api/oceanpay/admin/payment-provider-channels/{id}

GET|POST      /api/oceanpay/admin/payment-provider-currencies
GET|PUT|DELETE /api/oceanpay/admin/payment-provider-currencies/{id}

GET|POST      /api/oceanpay/admin/payment-methods
GET|PUT|DELETE /api/oceanpay/admin/payment-methods/{id}
PATCH         /api/oceanpay/admin/payment-methods/{id}/status
PUT           /api/oceanpay/admin/payment-methods/{id}/config
PUT           /api/oceanpay/admin/payment-methods/{id}/deposit-options
```

Webhook：

```text
/api/oceanpay/webhook/payment/{driver}/{key}/{type}
```

路由名：

```text
payment-gateway-webhook.handle
```

> 为了兼容原 `laravel-payment-manager` 的接入代码，包内默认保留旧路由名 `payment-gateway-webhook.handle`。

## Webhook handlers

宿主项目需要注册自己的充值/提现回调处理器：

```php
use Xditn\Oceanpay\PaymentGateway;

PaymentGateway::handleDepositWebhooksUsing(App\Actions\PaymentGateway\HandleDepositWebhook::class);
PaymentGateway::handleWithdrawalWebhooksUsing(App\Actions\PaymentGateway\HandleWithdrawalWebhook::class);
```

默认配置解析器会从 `payment_methods` 表根据 webhook route 中的 `{key}` 读取 `config` 和 `secret_config`。

## Payment method config

新增支付方式时，包会自动根据 `payment_provider_id` 和 `type` 合并服务商配置：

- `type=deposit` 使用 `payment_providers.deposit_config` 和 `deposit_secret_config`
- `type=withdrawal` 使用 `payment_providers.withdrawal_config` 和 `withdrawal_secret_config`
- 如果 `extra.channel_code` 存在，会写入 provider config 的 `channel_code`

`secret_config`、`deposit_secret_config`、`withdrawal_secret_config` 会加密保存，并在模型读取时自动解密。
