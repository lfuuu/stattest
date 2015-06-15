<?php

namespace app\classes\documents;

use Yii;
use app\classes\Assert;
use app\models\Bill;

class DocumentsFactory
{

    private $bill = null;
    private $document = null;

    private static function getDocTypes()
    {
        return [
            BillDocRepRuRUB::me(),
            BillDocRepHuRUB::me()
        ];
    }

    public function availableDocuments(Bill $bill, $country_lang = null, $currency = null, $docType = null)
    {
        $result = [];

        foreach (self::getDocTypes() as $document) {
            if (!is_null($docType) && $document->getDocType() !== $docType)
                continue;
            if (!is_null($country_lang) && $document->getCountryLang() !== $country_lang)
                continue;
            if (!is_null($currency) && $document->getCurrency() !== $currency)
                continue;

            if (
                $bill->currency == $document->getCurrency() &&
                $bill->clientAccount->contragent->country->lang == $document->getCountryLang()
            )
            $result[] = $document;
        }

        return $result;
    }

    public function getReport(Bill $bill, $docType)
    {
        $this->bill = $bill;

        foreach (self::getDocTypes() as $document)
            if (
                $document->getDocType() == $docType &&
                $document->getCurrency() == $bill->currency &&
                $document->getCountryLang() == $bill->clientAccount->contragent->country->lang
            )
                $this->document = $document->setBill($bill)->prepare();

        if (is_null($this->document))
            Assert::isUnreachable('Document type not found');

        return $this;
    }

    public function render()
    {
        return Yii::$app->view->renderFile($this->document->getTemplateFile() . '.php', [
            'document' => $this->document
        ]);
    }

    /*wkhtmltopdf*/
    public function renderAsPDF()
    {
        $options = ' --quiet -L 10 -R 10 -T 10 -B 10';
        switch ($this->document->getDocType()) {
            case 'upd':
                $options .= ' --orientation Landscape ';
                break;
            case 'invoice':
                $options .= ' --orientation Landscape ';
                break;
        }

        ob_start();
        echo $this->render();
        $content = ob_get_contents();
        ob_end_clean();

        $file_name = '/tmp/' . time() . Yii::$app->user->id;
        $file_html = $file_name . '.html';
        $file_pdf = $file_name . '.pdf';

        file_put_contents($file_name . '.html', $content);

        passthru("/usr/bin/wkhtmltopdf $options $file_html $file_pdf");
        $pdf = file_get_contents($file_pdf);
        unlink($file_html);unlink($file_pdf);

        header('Content-Type: application/pdf');
        ob_clean();
        flush();
        echo $pdf;
        exit;
    }

}