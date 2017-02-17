<?php
/**
 * @author Valentin Konusov <rlng-krsk@yandex.ru>
 */

namespace yarcode\payeer;

use GuzzleHttp\Client;
use yarcode\payeer\response\ApiResponse;
use yarcode\payeer\exceptions\ApiException;
use yii\base\Component;
use yii\helpers\ArrayHelper;

/**
 *
 * Class Api
 * @package yarcode\payeer
 */
class Api extends Component
{
    const CURRENCY_USD = 'USD';
    const CURRENCY_RUB = 'RUB';
    const CURRENCY_EUR = 'EUR';

    /** @var string Base API URL */
    public $baseUrl = 'https://payeer.com/ajax/api/api.php';

    /** @var string For instance "P1000000" */
    public $accountNumber;
    /** @var string */
    public $apiId;
    /** @var string */
    public $apiSecret;

    /** @var Client */
    private $httpClient;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        assert($this->accountNumber);
        assert($this->apiId);
        assert($this->apiSecret);
    }

    /**
     * Get account balance
     *
     * @return array
     * Example return: ['balance' => ['EUR' => ['BUDGET' => 0, 'DOSTUPNO' => 0, 'DOSTUPNO_SYST' => 0], 'RUB' => [...], 'USD' => [...]]]
     */
    public function balance()
    {
        return $this->call('balance');
    }

    /**
     * @param $action
     * @param array $params
     * @return array
     * @throws ApiException
     */
    private function call($action, $params = [])
    {
        $result = $this->getHttpClient()->post(null, [
            'form_params' => array_merge($params, [
                'account' => $this->accountNumber,
                'apiId' => $this->apiId,
                'apiPass' => $this->apiSecret,
                'action' => $action
            ])
        ]);

        $response = new ApiResponse($result->getBody()->getContents());

        if ($response->hasErrors()) {
            throw new ApiException($response->getErrors(true));
        }

        return $response->data;
    }

    /**
     * @return Client GuzzleHttp client
     */
    private function getHttpClient()
    {
        if (null === $this->httpClient) {
            $this->httpClient = new Client([
                'base_uri' => $this->baseUrl
            ]);
        }
        return $this->httpClient;
    }

    /**
     * @return bool
     */
    public function isAuth()
    {
        $this->call('');

        return true;
    }

    /**
     * Transfer to another Payeer user via account number or email
     *
     * @param string $to Payeer account number or email
     * @param float $sum
     * @param string $curIn
     * @param string $curOut If not specified, will be used curIn value
     * @param array $restParams Other params, see Payeer Api docs: sumOut, comment, anonim, protect, protectPeriod, protect Code
     * @return array
     * Example return: ['historyId' => 0]
     */
    public function transfer($to, $sum, $curIn, $curOut = null, array $restParams = [])
    {
        $preparedParams = ArrayHelper::merge($restParams, [
            'curIn' => $curIn,
            'sum' => $sum,
            'curOut' => $curOut ?: $curIn,
            'to' => $to
        ]);

        return $this->call('transfer', $preparedParams);
    }

    /**
     * Check if user exist
     *
     * @param $user
     * @return boolean
     */
    public function checkUser($user)
    {
        try {
            $this->call('checkUser', ['user' => $user]);
            $result = true;
        } catch (ApiException $exception) {
            $result = false;
        }

        return $result;
    }

    /**
     * Get exchange rate
     *
     * @param boolean $output true - for replenishment rates, false - for withdrawal rates
     * @return array
     * Example return: ['rate' => ['RUB/USD' => '0.123', 'RUB/RUB' => 1, ...]]
     */
    public function getExchangeRate($output = true)
    {
        return $this->call('getExchangeRate', ['output' => $output ? 'Y' : 'N']);
    }

    /**
     * Check if you can process transfer, WITHOUT real processing
     *
     * @param $psId integer Payment system ID
     * @param $sumIn float Withdrawal Amount
     * @param $accountNumber string Recipient wallet number in specified payment system
     * @param $curIn string Witdrawal currency: USD, EUR, RUB
     * @param $curOut null|string If not specified, will be used curIn value
     * @return array
     * Example return: ['outputParams' => ['sumIn' => 1, 'curIn' => 'USD', 'curOut' => 'RUB', 'ps' => 1136053, 'sumOut' => 61.47]]
     */
    public function initOutput($psId, $sumIn, $accountNumber, $curIn = self::CURRENCY_USD, $curOut = null)
    {
        return $this->call('initOutput', [
            'ps' => $psId,
            'sumIn' => $sumIn,
            'curIn' => $curIn,
            'curOut' => $curOut ?: $curIn,
            'param_ACCOUNT_NUMBER' => $accountNumber
        ]);
    }

    /**
     * Process transfer to specified payment system
     *
     * @param $psId integer Payment system ID
     * @param $sumIn float Withdrawal Amount
     * @param $accountNumber string Recipient wallet number in specified payment system
     * @param $curIn string Witdrawal currency: USD, EUR, RUB
     * @param $curOut null|string If not specified, will be used curIn value
     * @return array
     * Example return: ['historyId' => 1975716, 'outputParams' => ['sumIn' => 1, 'curIn' => 'USD', 'curOut' => 'RUB', 'ps' => 1136053, 'sumOut' => 61.47]]
     */
    public function output($psId, $sumIn, $accountNumber, $curIn = self::CURRENCY_USD, $curOut = null)
    {
        return $this->call('output', [
            'ps' => $psId,
            'curIn' => $curIn,
            'sumIn' => $sumIn,
            'curOut' => $curOut ?: $curIn,
            'param_ACCOUNT_NUMBER' => $accountNumber
        ]);
    }

    /**
     * Get allowed payment systems
     *
     * @return array
     */
    public function getPaySystems()
    {
        return $this->call('getPaySystems');
    }

    /**
     * Get operation info by ID
     *
     * @param $historyId
     * @return array
     * Example return: [
     * "id": "19794139",
     * "dateCreate": "14.01.2015 14:00:00",
     * "type": "transfer",
     * "status": "execute",
     * "from": "P1000000",
     * "sumIn": "1",
     * "curIn": "RUB",
     * "to": "P1000001",
     * "sumOut": "0.99",
     * "curOut": "RUB",
     * "comSite": "0.01",
     * "comGate": null,
     * "exchangeCourse": "0",
     * "protect": "N",
     * "protectCode": null,
     * "protectDay": null,
     * "comment": null,
     * "psId": null,
     * "isApi": "Y",
     * "error": "",
     * "isExchange": "N"
     * ]
     */
    public function historyInfo($historyId)
    {
        return $this->call('historyInfo', ['historyId' => $historyId]);
    }

    /**
     * Find operation info using you shopId and your Invoice ID
     *
     * @param $shopId
     * @param $orderId
     * @return array
     * Example return: [
     * "id": "1810698",
     * "dateCreate": "11.05.2015 18:46:13",
     * "type": "transfer",
     * "status": "execute",
     * "from": "P1000114",
     * "to": "P1000000",
     * "sumOut": "9.9",
     * "curOut": "USD",
     * "protect": "N",
     * "protectDay": null,
     * "comment": "@merchant: test.com [12345]; History ID: 1810696; Pay system: Payeer; Date: 11.05.2015 18:45:56; Pay date: 11.05.2015 18:46:13; Order ID: 12345; Client: test@mail.com [P1000001]; Email: test@mail.com; Desc: Test payment;",
     * "psId": "2609",
     * "error": "",
     * "isExchange": "N"
     * ]
     */
    public function shopOrderInfo($shopId, $orderId)
    {
        return $this->call('shopOrderInfo', ['shopId' => $shopId, 'orderId' => $orderId]);
    }

    /**
     * @param array $params sort(asc,desc), count(10), from(2016-03-02 15:35:17), to(2016-03-02 15:35:17), type(incoming/outgoing), append(197941396)
     * @return array
     */
    public function history(array $params = [])
    {
        return $this->call('history', $params);
    }

    /**
     * Create invoice in specified payment system
     * Pay attention: to activate this method, you shall send request to Payeer Support
     *
     * @return array
     */

    /**
     * Create invoice in specified payment system
     * Pay attention - 1: to activate this method, you shall send request to Payeer Support
     * Pay attention - 2: shop, ps, form is JSON string
     *
     * @param $shop string JSON
     * @param $ps string JSON
     * @param $form string JSON
     * @param array $restParams processUrl, merchantUrl, lang, ip, success_url, fail_url, status_url, reference, submerchant
     * @return array
     * Return example: ['location' => 'REDIRECT URL', 'orderid' => 254431, 'historyid' => 1980775, 'historytm' => 1000919709]
     */
    public function merchant($shop, $ps, $form, array $restParams = [])
    {
        $preparedParams = ArrayHelper::merge($restParams, [
            'shop' => $shop,
            'ps' => $ps,
            'form' => $form
        ]);

        return $this->call('merchant', $preparedParams);
    }
}
