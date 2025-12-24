<?php

namespace app\classes;

use Yii;
use app\helpers\InvoiceQrCodeHelper;

class Smarty
{
    private static $_smarty = null;

    /**
     * @return \Smarty
     */
    public static function init()
    {
        if (self::$_smarty == null) {
            $smarty = new \Smarty;
            $smarty->setCompileDir(Yii::$app->params['SMARTY_COMPILE_DIR']);
            $smarty->setTemplateDir(Yii::$app->params['SMARTY_TEMPLATE_DIR']);

            $smarty->registerPlugin('modifier', 'mdate', [new \app\classes\DateFunction, 'mdate']);
            $smarty->registerPlugin('modifier', 'wordify', [new \app\classes\Wordifier, 'Make']);
            $smarty->registerPlugin(
                'function',
                'qr_code',
                function (array $params) {
                    $docType = $params['docType'] ?? $params['doc_type'] ?? $params['type'] ?? null;
                    $billNo = InvoiceQrCodeHelper::extractBillNo(
                        $params['bill'] ?? ($params['billNo'] ?? ($params['bill_no'] ?? null))
                    );
                    $invoice = $params['invoice'] ?? null;
                    $inline = filter_var($params['inline'] ?? false, FILTER_VALIDATE_BOOLEAN);
                    $asTag = !array_key_exists('as_tag', $params) || filter_var($params['as_tag'], FILTER_VALIDATE_BOOLEAN);

                    $src = InvoiceQrCodeHelper::getImageSrc($docType, $billNo, $invoice, $inline);

                    if (!$src) {
                        return '';
                    }

                    if (!$asTag) {
                        return $src;
                    }

                    return '<img src="' . $src . '" border="0"/>';
                }
            );
            $smarty->registerPlugin(
                'function',
                'qr_code_data',
                function (array $params) {
                    $docType = $params['docType'] ?? $params['doc_type'] ?? $params['type'] ?? null;
                    $billNo = InvoiceQrCodeHelper::extractBillNo(
                        $params['bill'] ?? ($params['billNo'] ?? ($params['bill_no'] ?? null))
                    );
                    $invoice = $params['invoice'] ?? null;

                    return InvoiceQrCodeHelper::getData($docType, $billNo, $invoice) ?: '';
                }
            );

            self::$_smarty = $smarty;
        }

        return self::$_smarty;
    }
}
