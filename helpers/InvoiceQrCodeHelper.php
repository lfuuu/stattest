<?php

namespace app\helpers;

use app\classes\BillQRCode;
use app\classes\QRcode\QRcode;
use app\models\Invoice;

class InvoiceQrCodeHelper
{
    /**
     * Resolve QR document type based on template document type and invoice data.
     */
    public static function resolveDocType(?string $docType, ?Invoice $invoice = null): ?string
    {
        $docType = $docType ? trim($docType) : '';

        if ($docType === '') {
            return null;
        }

        if ($docType === 'upd') {
            if (!$invoice) {
                return null;
            }

            return $invoice->type_id == Invoice::TYPE_1 ? 'upd-1' : 'upd-2';
        }

        return $docType;
    }

    /**
     * Build QR payload string for the given bill/document combination.
     */
    public static function getData(?string $docType, ?string $billNo, ?Invoice $invoice = null): ?string
    {
        if (!$billNo) {
            return null;
        }

        $resolvedType = self::resolveDocType($docType, $invoice);

        if (!$resolvedType) {
            return null;
        }

        return BillQRCode::encode($resolvedType, $billNo) ?: null;
    }

    /**
     * Get QR image source (URL or inline data URI).
     */
    public static function getImageSrc(
        ?string $docType,
        ?string $billNo,
        ?Invoice $invoice = null,
        bool $inline = false
    ): string {
        $qrData = self::getData($docType, $billNo, $invoice);

        if (!$qrData) {
            return '';
        }

        if ($inline) {
            $inlineImage = self::generateInlineImage($qrData);

            return $inlineImage ?: '';
        }

        return '/utils/qr-code/get?data=' . $qrData;
    }

    /**
     * Render an <img> tag with QR content.
     */
    public static function renderImage(
        ?string $docType,
        ?string $billNo,
        ?Invoice $invoice = null,
        bool $inline = false
    ): string {
        $src = self::getImageSrc($docType, $billNo, $invoice, $inline);

        if (!$src) {
            return '';
        }

        return '<img src="' . $src . '" border="0"/>';
    }

    /**
     * Extract bill number from either a model or a scalar value.
     */
    public static function extractBillNo($bill): ?string
    {
        if (is_string($bill)) {
            return $bill;
        }

        if (is_object($bill) && isset($bill->bill_no)) {
            return $bill->bill_no;
        }

        return null;
    }

    private static function generateInlineImage(string $qrData): string
    {
        ob_start();
        QRcode::gif(trim($qrData), false, 'H', 4, 2);
        $imageContent = ob_get_clean();

        if (!$imageContent) {
            return '';
        }

        return 'data:image/gif;base64,' . base64_encode($imageContent);
    }
}
