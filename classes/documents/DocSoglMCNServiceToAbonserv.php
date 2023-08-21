<?php

namespace app\classes\documents;

use app\models\Currency;

class DocSoglMCNServiceToAbonserv extends DocumentReport
{
    const DOC_TYPE_BILL = 'sogl_mcn_service_to_abonservice';

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
        return 'Соглашение о передачи прав (МСН Телеком Сервис => АбонентСервис)';
    }

}
