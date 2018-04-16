<?php

namespace app\classes\documents;

use app\models\Currency;
use app\models\Language;

class BillOperator extends DocumentReport
{
    public $isAllLanguages = true;
    public $isMultiCurrencyDocument = true;

    public function getLanguage()
    {
        return Language::LANGUAGE_ENGLISH;
    }

    public function getDocType()
    {
        return self::DOC_TYPE_BILL_OPERATOR;
    }

    public function getName()
    {
        return 'Счет (предоплата, операторка)';
    }

    public function getTemplateFile()
    {
        return SELF::TEMPLATE_PATH . $this->getLanguage() . '/' . self::DOC_TYPE_BILL . '_usd';
    }

}