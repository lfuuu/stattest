<?php

namespace app\classes\documents;

use app\models\Currency;

class DocNoticeMCMTelekom extends DocumentReport
{
    const DOC_TYPE_BILL = 'notice_mcm_telekom';

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
        return self::DOC_TYPE_BILL;
    }

    public function getName()
    {
        return 'Уведомление о передачи прав';
    }

}
