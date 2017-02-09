<?php
/**
 * @author Alexey Samoylov <alexey.samoylov@gmail.com>
 */

namespace yarcode\payeer;

use yii\base\Widget;

/**
 * Class RedirectForm
 * @package yarcode\payeer
 */
class RedirectForm extends Widget
{
    /** @var Merchant */
    public $merchant;
    public $invoiceId;
    public $amount;
    public $description = '';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        assert(isset($this->merchant));
        assert(isset($this->invoiceId));
        assert(isset($this->amount));
    }

    /**
     * @return string
     */
    public function run()
    {
        $amount = number_format($this->amount, 2, '.', '');
        $description = base64_encode($this->description);

        $parts = array(
            $this->merchant->merchantId,
            $this->invoiceId,
            $amount,
            $this->merchant->merchantCurrency,
            $description,
            $this->merchant->merchantSecret,
        );

        $sign = strtoupper(hash('sha256', implode(':', $parts)));

        return $this->render('redirect', [
            'merchant' => $this->merchant,
            'invoiceId' => $this->invoiceId,
            'amount' => $amount,
            'description' => $description,
            'sign' => $sign,
        ]);
    }
}