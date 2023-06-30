<?php

namespace app\classes\contragent\importer\lk\typeFactory;

use app\models\ClientContragent;

class CoreLkContragentTypeLegal extends CoreLkContragentTypeDefault
{
    public static $orgType = self::ORG_TYPE_LEGAL;

    public function getStatLegalType()
    {
        return ClientContragent::LEGAL_TYPE;
    }

    protected function makeStatModel()
    {
        $r = $this->coreLkContragent->getDataResponse();
        $data = $r['data'] ?? [];

        parent::makeStatModel();

        if (!$data) {
            return true;
        }

        $contragent = $this->contragent;

        $contragent->name = $data['name']['short_with_opf'] ?? $r['value'] ?? '???';
        $contragent->name_full = $data['name']['full_with_opf'] ?? $r['value'] ?? '???';

        $contragent->inn = $data['inn'] ?? null;
        $contragent->kpp = $data['kpp'] ?? null;
        $contragent->ogrn = $data['ogrn'] ?? null;
        $contragent->okpo = $data['okpo'] ?? null;
        $contragent->address_jur = $data['address']['unrestricted_value'] ?? null;
        $contragent->fio = $contragent->fioV = $data['management']['name'] ?? null;

        $position = $data['management']['post'] ?? '';

        if ($position) {
            $contragent->position = $contragent->positionV = strtr($position, [
                'ГЕНЕРАЛЬНЫЙ ДИРЕКТОР' => 'Генеральный директор',
                'ДИРЕКТОР' => 'Директор',
                'ИСПОЛНЯЮЩИЙ ОБЯЗАННОСТИ ГЕНЕРАЛЬНОГО ДИРЕКТОРА' => 'Исполняющий обязанности генерального директора',
                'ИСПОЛНИТЕЛЬНЫЙ ДИРЕКТОР' => 'Исполнительный директор',
                'ИНДИВИДУЛЬНЫЙ ПРЕДПРИНИМАТЕЛЬ' => 'Индивидульный предприниматель',
                'УПРАВЛЯЮЩИЙ' => 'Управляющий',
                'ПРЕЗИДЕНТ' => 'Президент',
                'ПРЕДСТАВИТЕЛЬ' => 'Представитель',
                'НАЧАЛЬНИК УПРАВЛЕНИЯ' => 'Начальник управления',
            ]);
        }

        return true;
    }
}
