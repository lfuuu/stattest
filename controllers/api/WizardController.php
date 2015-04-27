<?php

namespace app\controllers\api;

use Yii;
use app\classes\ApiController;
use app\classes\BaseController;

use app\classes\Assert;
use app\models\LkWizardState;
use app\models\ClientAccount;
use app\models\ClientContact;
use app\models\ClientContract;
use app\models\ClientFile;
use app\models\ClientBPStatuses;
use app\models\User;
use app\forms\contragent\ContragentEditForm;
use app\forms\lk_wizard\ContactForm;


class WizardController extends BaseController/*ApiController*/
{
    private $accountId = null;
    private $account = null;
    private $wizard = null;

    private $postData = [];
       

    private function loadAndCheck($isCheckWizard = true)
    {
        $this->postData = Yii::$app->request->bodyParams;

        if (!isset($this->postData["account_id"]))
            throw new \Exception("account_is_bad");

        $this->accountId = $this->postData["account_id"];

        $this->account = $this->_checkAndGetAccount($this->accountId);

        $this->wizard = LkWizardState::findOne($this->account->id);

        if ($isCheckWizard)
            if (!$this->wizard)
                throw new \Exception("account_is_bad");

        return $this->postData;
    }


    private function _checkAndGetAccount($accountId)
    {
        if (is_array($accountId) || !$accountId || !preg_match("/^\d{1,6}$/", $accountId))
            throw new \Exception("account_is_bad");

        $account = ClientAccount::findOne($accountId);
        if(!$account)
            throw new \Exception("account_not_found");

        $this->_checkClean($account);

        return $account;
    }

    private function _checkClean($account)
    {
        if ($account->business_process_status_id == ClientBPStatuses::TELEKOM__SUPPORT__WORK ) //Клиента включили
        {
            $wizard = LkWizardState::findOne($account->id);
            if ($wizard)
            {
                if ($wizard->step < 4 || ($wizard->step == 4 && $wizard->state == "review"))
                {
                    $wizard->delete();
                }
            }
        }
    }

    public function actionState()
    {
        $this->loadAndCheck(false);

        return $this->getWizardState();

    }

    public function actionRead()
    {
        $this->loadAndCheck();

        return $this->makeWizardFull();
    }

    public function actionSave()
    {
        $postData = $this->loadAndCheck();

        $result = true;

        $step = $postData["state"]["step"];

        if ($step == 1)
        {
            $result = $this->_saveStep1($postData["step1"]);

        } else if ($step == 3)
        {
            $result = $this->_saveStep3($postData["step3"]);
        }

        if ($result === true)
        {
            if ($step == 1 || $step == 3)
            {
                $this->wizard->step = $step+1;
                $this->wizard->state = ($step == 1 ? "process" : ($step == 3 ? "review" : "process"));
                $this->wizard->save();
            }
        } else { //error
            return $result;
        }

        return $this->makeWizardFull();
    }

    public function actionGetContract()
    {
        $this->loadAndCheck();

        $contract = ClientContract::findOne([
            "client_id" => $this->accountId, 
            "user_id" => User::CLIENT_USER_ID
        ]);

        if (!$contract)
        {
            $contractId = ClientContract::dao()->addContract(
                $this->accountId,

                "contract",
                "MCN",
                "Usludi_svyazi",

                $this->accountId."-".date("Y"),
                date("d.m.Y"),

                "",
                "ЛК - wizard",
                User::CLIENT_USER_ID
            );

            $contract = ClientContract::findOne([
                "client_id" => $this->accountId, 
                "user_id" => User::CLIENT_USER_ID
            ]);
        }

        if ($this->wizard->step == 2)
        {
            $this->wizard->step = 3;
            $this->wizard->save();
        }

        return $contract->content;
    }

    public function actionSaveDocument()
    {
        $this->loadAndCheck();

        return $this->account->fileManager->addFileFromParam("тестовый документ.txt", " какойто текст", "ЛК - wizard", User::CLIENT_USER_ID);
    }


    private function makeWizardFull()
    {
        return [
            "step1" => $this->getOrganizationInformation(),
            "step2" => $this->getContract(),
            "step3" => $this->getContactAndContractList(),
            "step4" => $this->getAccountManager(),
            "state" => $this->getWizardState()
        ];
    }

    public function getWizardState()
    {
        $wizard = $this->wizard;

        if (!$wizard)
            return ["step" => -1, "good" => -1];

        if ($wizard->step == 4)
        {
            return [
                "step" => $wizard->step, 
                "good" => $wizard->step-($wizard->state == 'review' ? 1 : 0), "step_state" => $wizard->state
            ];
        } else {
            return [
                "step" => $wizard->step, 
                "good" => $wizard->step-1
            ];
        }
    }


    private function getOrganizationInformation()
    {
        $c = $this->account->contragent;
        $d = [
            "name" =>  $c->name,
            "legal_type" =>  $c->legal_type,
            "address_jur" =>  $c->address_jur,
            "inn" =>  $c->inn,
            "kpp" =>  $c->kpp,
            "position" =>  $c->position,
            "fio" =>  $c->fio,
            "ogrn" =>  $c->ogrn,
            "last_name" => ($c->person ? $c->person->last_name : ""),
            "first_name" => ($c->person ? $c->person->first_name : ""),
            "middle_name" => ($c->person ? $c->person->middle_name : ""),
            "passport_serial" => ($c->person ? $c->person->passport_serial : ""),
            "passport_number" => ($c->person ? $c->person->passport_number : ""),
            "passport_date_issued" => ($c->person ? $c->person->passport_date_issued : ""),
            "passport_issued" => ($c->person ? $c->person->passport_issued : ""),
            "address" => ($c->person ? $c->person->address : "")
        ];
        return $d;
    }

    private function getContract()
    {
        return ["link_dogovor" => "/lk/wizard/contract"];
    }

    private function getContactAndContractList()
    {
        $contact = $this->getContact();
        $files = $this->getClientFiles();

        $d = [
                "contact_phone" => $contact["phone"],
                "contact_fio" => $contact["fio"],
                "file_list" => $files,
                "is_upload" => count($files) < 5
            ];

        return $d;
    }

    private function getContact()
    {
        $contact = ClientContact::findOne([
            "client_id" => $this->account->id, 
            "user_id"   => User::CLIENT_USER_ID, 
            "type"      => "phone"
        ]);

        if ($contact)
        {
            return ["phone" => $contact->data, "fio" => $contact->comment];
        } 

        return ["phone" => "", "fio" => ""];
    }

    private function getClientFiles()
    {
        $files = [];
        foreach(ClientFile::findAll(["client_id" => $this->account->id, "user_id" => User::CLIENT_USER_ID]) as $file)
        {
            $files[] = $file->name;
        }

        return $files;
    }

    private function getAccountManager()
    {
        $manager = $this->account->userAccountManager ?: User::findOne(User::DEFAULT_ACCOUNT_MANAGER_USER_ID);

        return [
            "manager_name" => $manager->name,
            "manager_phone" => "(495) 105-55-55".($manager->phone_work ? " доп. ".$manager->phone_work : "")
        ];
    }

    private function _saveStep1($stepData)
    {
        $form = new ContragentEditForm();

        $form->load($stepData, "");

        if (!$form->validate())
        {
            $errors = [];
            foreach($form->getErrors() as $field => $error)
            {
                $errors[] = ["field" => $field, "error" => $error[0]];
            }
            return ["errors" => $errors];
        } else {
            return $form->saveInContragent($this->account->contragent);
        }
    }

    private function _saveStep3($stepData)
    {
        $form = new ContactForm;

        $form->load($stepData, "");

        if (!$form->validate())
        {
            $errors = [];
            foreach($form->getErrors() as $field => $error)
            {
                $errors[] = ["field" => $field, "error" => $error[0]];
            }
            return ["errors" => $errors];
        } else {
            return $form->save($this->account);
        }
    }
}
