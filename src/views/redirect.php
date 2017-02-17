<?php
/**
 * @author Alexey Samoylov <alexey.samoylov@gmail.com>
 *
 * @var \yii\web\View $this
 * @var \yarcode\payeer\Merchant $merchant
 * @var integer $invoiceId
 * @var float $amount
 * @var string $currency
 * @var string $description
 * @var string $sign
 */
?>
<div class="payeer-checkout">
    <p><?= \Yii::t('Payeer', 'Now you will be redirected to the payment system.') ?></p>
    <form id="payeer-checkout-form" method="GET" action="//payeer.com/merchant/">
        <input type="hidden" name="m_shop" value="<?= $merchant->shopId ?>">
        <input type="hidden" name="m_orderid" value="<?= $invoiceId ?>">
        <input type="hidden" name="m_amount" value="<?= $amount ?>">
        <input type="hidden" name="m_curr" value="<?= $currency ?>">
        <input type="hidden" name="m_desc" value="<?= $description ?>">
        <input type="hidden" name="m_sign" value="<?= $sign ?>">
        <input type="submit" name="m_process" value="send" />
    </form>
</div>

<?php
$js = <<<JS
    document.getElementById('payeer-checkout-form').submit()
JS;
$this->registerJs($js, \yii\web\View::POS_READY);
?>