<?php

namespace app\controllers\api;

use Yii;
use app\classes\validators\AccountIdValidator;
use app\exceptions\FormValidationException;
use app\classes\ApiController;
use app\classes\DynamicModel;
use app\models\ClientDocument;
use app\models\ClientAccount;

class LkDocsController extends ApiController
{
    private $accountId = 0;

    private function validateAccountId()
    {
        $form = DynamicModel::validateData(
            Yii::$app->request->bodyParams, 
            [
                ['account_id', AccountIdValidator::className()],
            ]
        );

        if (!$form->hasErrors()) {
            $this->accountId = $form->account_id;
            return true;
        } else {
            throw new \Exception("Account not found");
        }
    }

    public function actionSections()
    {
        $this->validateAccountId();

        $data = [["type" => "client_card", "id" => 0]];

        $contractId = ClientAccount::findOne($this->accountId)->contract_id;
        $contract = ClientDocument::find()->contractId($contractId)->active()->contract()->last();

        if ($contract)
        {
            $data[] = [
                "type" => "contract",
                "id"   => $contract->id, 
                "no"   => $contract->contract_no, 
                "date" => strtotime($contract->contract_date)
                ];

            if ($contract->blank)
            {
                $data[] = [
                    "type" => "blank",
                    "id" => $contract->blank->id,
                    ];
            }

            foreach($contract->agreements as $agreement)
            {
                $data[] = [
                    "type" => "agreement",
                    "id" => $agreement->id, 
                    "no" => $agreement->contract_dop_no, 
                    "date" => strtotime($agreement->contract_dop_date)
                    ];
            }
        }

        return $data;
    }

    public function getProperty()
    {
        $a = ClientAccount::findOne($this->accountId);
        $c = $a->contragent;

        $return = ["error" => "Error"];

        if ($c->legal_type == "legal")
        {
            $return = [
                'company_name' => $c->name, 
                'address_jur' => $c->address_jur,
                'inn' => $c->inn,
                'kpp' => $c->kpp,
                'signer_position' => $c->position,
                'signer_fio' => $c->fio,
                ];
        } else if ($c->legal_type == "ip")
        {
            $return = [
                'company_name' => $c->name,
                'address_jur' => $c->address_jur,
                'last_name' => $c->person->last_name,
                'first_name' => $c->person->first_name,
                'inn' => $c->inn,
                'ogrn' => $c->ogrn,
                ];
        } else { //person

            $passportSerial = $c->person->passport_serial;
            $passportSerial = 
                strlen($passportSerial) > 2 
                ? substr($passportSerial, 0, 2).preg_replace("/\d/", "*", substr($passportSerial, 2)) 
                : $passportSerial;

            $passportNumber = $c->person->passport_number;
            $passportNumber = 
                strlen($passportNumber) > 2 
                ? substr($passportNumber, 0, 1).preg_replace("/\d/", "*", substr($passportNumber, 1, strlen($passportNumber)-2)).substr($passportNumber, strlen($passportNumber)-1) 
                : $passportNumber;

            $return = [
                'last_name' => $c->person->last_name,
                'first_name' => $c->person->first_name,
                'middle_name' => $c->person->middle_name,
                'passport_serial' => $passportSerial,
                'passport_number' => $passportNumber,
                'passport_date_issued' => date("Y-m-d", ($c->person->passport_date_issued == "0000-00-00" ? 0 : strtotime($c->person->passport_date_issued))),
                'passport_issued' => $c->person->passport_issued ? "***" : "",
                'address' => $c->person->registration_address,
                ];
        }

        if ($a->allContacts)
        {
            $allEmail = [];
            $officialEmails = [];

            foreach($a->allContacts as $contact)
            {
                if (!$contact->is_active)
                    continue;

                if ($contact->type == "email")
                {
                    $email = trim($contact->data);
                    $allEmail[$email] = 1;

                    if ($contact->is_official)
                    {
                        $officialEmails[$email] = 1;
                    }
                }
            }

            $return["emails"] = array_keys($officialEmails);
            $return["all_emails"] = array_keys($allEmail);

            sort($return["emails"]);
            sort($return["all_emails"]);
        }

        $data = [];
        $counter = 1;
        foreach($return as $title => $value)
        {
            $data[] = [
                "id" => $counter++,
                "title" => $title,
                "value" => $value,
                "is_show" => $title != "all_emails",
                "tips" => false
                ];
        }

        return $data;
    }

    public function actionDocument()
    {
        $form = DynamicModel::validateData(
            Yii::$app->request->bodyParams, 
            [
                ['account_id', AccountIdValidator::className()],
                [["id"], "required"],
            ]
        );

        if ($form->hasErrors()) 
        {
            throw new FormValidationException($form);
        }

        $this->accountId = $form->account_id;

        if ($form->id == 0)
        {
            return $this->getProperty();
        }

        $contractId = ClientAccount::findOne($this->accountId)->contract_id;
        $document = ClientDocument::find()
            ->contractId($contractId)
            ->active()
            ->andWhere(["id" => $form->id])
            ->select(["id", "type", "contract_no", "contract_date", "contract_dop_no", "contract_dop_date"])
            ->one();


        if (!$document) 
        {
            $form->addError("id", "Document not found");
            throw new FormValidationException($form);
        }

        $result = $document->toArray();
        $result["content"] = $document->content;

        if ($document->type != "agreement")
        {
            unset($result["contract_dop_no"], $result["contract_dop_date"]);
        }

        unset($result["client_id"]);

        return $result;
    }

}
