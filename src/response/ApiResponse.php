<?php
/**
 * @author Valentin Konusov <rlng-krsk@yandex.ru>
 */

namespace yacode\payeer\response;

use yarcode\payeer\exceptions\ApiException;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\helpers\VarDumper;

/**
 * Class ApiResponse
 * @package yacode\payeer
 */
class ApiResponse
{
    private $authError;
    private $errors = [];

    /** @var array */
    public $data;

    /**
     * @inheritDoc
     */
    public function __construct($response)
    {
        $response = is_array($response) ?: Json::decode($response);

        $this->parseResponse($response);
    }


    /**
     * @param $response
     * @throws ApiException
     */
    private function parseResponse($response)
    {
        if(!array_key_exists('auth_error', $response) || !array_key_exists('errors', $response)) {
            throw new ApiException("Bad response " . VarDumper::dumpAsString($response));
        }

        $this->authError = ArrayHelper::remove($response, 'auth_error');
        $this->errors = ArrayHelper::remove($response, 'errors');
        $this->data = $response;
    }

    /**
     * @return bool
     */
    public function hasErrors()
    {
        return !empty($this->errors);
    }

    /**
     * @param bool $asJson
     * @return array|string
     */
    public function getErrors($asJson = false)
    {
        return $asJson
            ? Json::encode($asJson)
            : $this->errors;
    }

}