<?php
/**
 * @author Alexey Samoylov <alexey.samoylov@gmail.com>
 */

namespace yarcode\payeer;

use yii\base\Component;
use yii\base\InvalidConfigException;

class Api extends Component
{
    /** @var string */
    public $accountNumber;
    /** @var string */
    public $apiId;
    /** @var string */
    public $apiSecret;

    /** @var \CPayeer */
    public $payeer;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        assert($this->accountNumber);
        assert($this->apiId);
        assert($this->apiSecret);

        $this->payeer = new \CPayeer($this->accountNumber, $this->apiId, $this->apiSecret);

        if (!$this->payeer->isAuth()) {
            throw new InvalidConfigException('Invalid payeer credentials');
        }
    }
}
