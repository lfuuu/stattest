<?php

namespace app\classes\documents;

use app\models\Currency;

class DocNoticeMCMTelekom extends DocumentReport
{
    const BILL_DOC_TYPE = 'notice_mcm_telekom';

    public function getLanguage()
    {
        return 'ru-RU';
    }

    public function getCurrency()
    {
        return Currency::RUB;
    }

    public function getDocType()
    {
        return self::BILL_DOC_TYPE;
    }

    public function getName()
    {
        return 'Уведомление о передачи прав';
    }

}
