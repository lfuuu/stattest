<?php
namespace app\dao;

use app\classes\Singleton;
use app\models\ClientContragent;
use app\models\ClientBPStatuses;
use app\models\LkWizardState;

class ClientContragentDao extends Singleton
{

    public function saveToAccount($contragent)
    {
        $accounts = $contragent->accounts;

        if (!isset($accounts[0]))
            return ["errors" => ["error" => "Account not found"]];

        $account = $accounts[0];

        // данное действие разрешено, если ЛС в стадии подключения
        if (!LkWizardState::isBPStatusAllow($account->business_process_status_id, $account->id))
            return ["errors" => ["error" => "Account not allowed to save from wizard"]];

        //legal || ip
        $account->company = $contragent->name;
        $account->company_full = $contragent->name_full;
        $account->address_jur = $contragent->address_jur;
        $account->address_post =$contragent->address_post;
        $account->inn = $contragent->inn;
        $account->kpp = $contragent->kpp;
        $account->signer_position = $account->signer_positionV = $contragent->position;
        $account->signer_name = $account->signer_nameV = $contragent->fio;
        $account->okpo = $contragent->okpo;
        $account->type = $contragent->legal_type == "person" ? "priv" : "org";

        if ($contragent->legal_type == "person")
        {
            $p = &$contragent->person;
            $passport_date = strtotime($p->passport_date_issued);

            $account->bank_properties = "Паспорт серия ".$p->passport_serial." номер ".$p->passport_number." Выдан ".$p->passport_issued." Дата выдачи:".date("d.m.Y", $passport_date);
            $account->address_connect = $account->address_jur = $account->address_post = $p->address;
        }
        return $account->save();
    }

}
