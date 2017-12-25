<?php

namespace app\classes\documents;

use app\models\Currency;
use app\models\Language;

class CreditNoteDocument extends DocumentReport
{
    const DOC_TYPE_BILL = 'credit_note';

    /**
     * Валюта документа
     *
     * @return string
     */
    public function getCurrency()
    {
        return Currency::RUB;
    }

    /**
     * Тип документа
     *
     * @return string
     */
    public function getDocType()
    {
        return self::DOC_TYPE_CREDIT_NOTE;
    }

    /**
     * Название документа
     *
     * @return string
     */
    public function getName()
    {
        return 'Credit note';
    }

    /**
     * Язык документа
     *
     * @return string
     */
    public function getLanguage()
    {
        return Language::LANGUAGE_RUSSIAN;
    }
}
