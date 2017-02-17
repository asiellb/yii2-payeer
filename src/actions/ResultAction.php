<?php
/**
 * @author Alexey Samoylov <alexey.samoylov@gmail.com>
 */

namespace yarcode\payeer\actions;

use yarcode\payeer\Merchant;
use yii\base\Action;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;

class ResultAction extends Action
{
    /** @var string Component name */
    public $componentName;

    /** @var Merchant */
    protected $merchant;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->merchant = \Yii::$app->get($this->componentName);

        if(!$this->merchant instanceof Merchant) {
            throw new InvalidConfigException('Invalid Payeer component name');
        }

        parent::init();
    }

    /**
     * @throws BadRequestHttpException
     */
    public function run()
    {
        $post = \Yii::$app->request->post();
        $orderId = ArrayHelper::getValue($post, 'm_orderid', false);

        if (false === $orderId) {
            throw new BadRequestHttpException('Missing m_orderid');
        }

        $result = $this->merchant->processResult($post);

        if ($result) {
            echo $orderId . '|success';
        } else {
            echo $orderId . '|error';
        }
    }
}