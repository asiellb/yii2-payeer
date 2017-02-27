# Payeer component for Yii2

Payment gateway and api client for [Payeer](https://payeer.com) service.

Package consists of 2 main components: 
- `Api` to perform various API calls. For instance: get balance, send money, get account history, etc.  
- `Merchant` to connect Merchant API and receipt payments


## Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```bash
php composer.phar require --prefer-dist yarcode/yii2-payeer "~1.0"
```

or add
    
    "yarcode/yii2-payeer": "~1.0"

to the `require` section of your composer.json.

## API

### Configuration

Configure `payeerApi` component in the `components` section of your application.

```php
'payeerApi' => [
    'class' => \yarcode\payeer\Api::class,
    'accountNumber' => '<account number>',
    'apiId' => '<api ID>',
    'apiSecret' => '<your secret>'
],
```

### Usage

You shall wrap API calls using `try {} catch() {}` to handle any errors.

```php
/** @var \yarcode\payeer\Api $api */
$api = Yii::$app->get('payeerApi');

try {
    $result = $api->balance();    
} catch(ApiException $e) {
    // handle API errors here, for instance:
     $error = $e->getMessage();
     $result = null;
}

```

### Available Methods

```php
$api->isAuth();
$api->balance();
$api->transfer($to, $sum, $curIn, $curOut = null, array $restParams = [])
$api->checkUser('P1234567')
$api->getExchangeRate();
$api->initOutput($psId, $sumIn, $accountNumber, $curIn = self::CURRENCY_USD, $curOut = null);
$api->output($psId, $sumIn, $accountNumber, $curIn = self::CURRENCY_USD, $curOut = null);
$api->getPaySystems();
$api->historyInfo($historyId);
$api->shopOrderInfo($shopId, $orderId);
$api->history(array $params = []);
$api->merchant($shop, $ps, $form, array $restParams = []);

```

## Merchant

### Configuration

Configure `payeer` component in the `components` section of your application.

```php
'payeer' => [
    'class' => \yarcode\payeer\Merchant::class,
    'shopId' => '<shop ID>',
    'secret' => '<merchant secret>',
    'currency' => '<default shop currency>' // by default Merchant::CURRENCY_USD
],
```

### Redirecting to the payment system ###

To redirect user to Payeer site you need to create the page with RedirectForm widget.
User will redirected right after page load.

```php
<?= \yarcode\payeer\RedirectForm::widget([
    'merchant' => Yii::$app->get('payeer'),
    'invoiceId' => $invoiceId,
    'amount' => $amount,
    'description' => $description,
    'currency' => \yarcode\payeer\Merchant::CURRENCY_USD // By default Merchant component currency
]); ?>
```
### Gateway controller ###

You will need to create controller that will handle result requests from PerfectMoney service.
Sample controller code:

    // TODO: Finish gateway controller example

## Licence ##

MIT
    
## Links ##

* [Source code on GitHub](https://github.com/yarcode/yii2-payeer)
* [Composer package on Packagist](https://packagist.org/packages/yarcode/yii2-payeer)
* [Payeer service](https://payeer.com)
