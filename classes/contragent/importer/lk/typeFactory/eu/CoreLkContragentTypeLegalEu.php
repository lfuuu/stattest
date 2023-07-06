<?php

namespace app\classes\contragent\importer\lk\typeFactory\eu;

use app\classes\contragent\importer\lk\typeFactory\CoreLkContragentTypeDefault;
use app\models\ClientContragent;

class CoreLkContragentTypeLegalEu extends CoreLkContragentTypeDefault
{
    public static $orgType = self::ORG_TYPE_LEGAL;

    public function getStatLegalType()
    {
        return ClientContragent::LEGAL_TYPE;
    }

    protected function makeStatModel()
    {
        $row = $this->coreLkContragent->row;

        if (!$row) {
            return true;
        }

        if (!parent::makeStatModel()) {
            return true;
        }

        $contragent = $this->contragent;

        if ($row['name']) {
            $contragent->name = $contragent->name_full = $row['name'];
        }

        if ($row['lang_code']) {
            $contragent->lang_code = $row['lang_code'];
        }

        if ($row['country_id']) {
            $contragent->country_id = $row['country_id'];
        }

        if ($row['address']) {
            $contragent->address_jur = $row['address'];
        }

        if ($row['tax_id']) {
            $contragent->inn = $row['tax_id'];
        }

        if ($row['reg_id']) {
            $contragent->ogrn = $row['reg_id'];
        }

        $data = $this->coreLkContragent->getDataResponse();
        if ($data && isset($data['CC']) && $data['CC'] && isset($data['Number']) && $data['Number']) {
            $contragent->inn_euro = mb_strtoupper($data['CC']) .  $data['Number'];
        }

        $contragent->legal_type = $this->getStatLegalType();

        return true;
    }
}
