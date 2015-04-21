<?php

namespace app\controllers\api;

use Yii;
//use app\classes\ApiController;
use app\classes\BaseController;

use app\classes\Assert;
use app\models\LkWizardState;
use app\models\ClientAccount;
use app\models\ClientContact;
use app\models\ClientFile;
use app\models\ClientBPStatuses;
use app\models\User;
use app\forms\contragent\ContragentEditForm;


class WizardController extends BaseController/*ApiController*/
{
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
        //$data = Yii::$app->request->bodyParams;
        //

        $data = ["account_id" => 9130];

        $account = $this->_checkAndGetAccount($data["account_id"]);

        return print_r($this->getWizardState($account));

    }

    public function getWizardState($account)
    {
        $wizard = LkWizardState::findOne($account->id);

        if (!$wizard)
            return ["step" => -1, "good" => -1];

        if ($wizard->step == 4)
        {
            return ["step" => $wizard->step, "good" => $wizard->step-($wizard->state == 'review' ? 1 : 0), "step_state" => $wizard->state];
        } else {
            return ["step" => $wizard->step, "good" => $wizard->step-1];
        }
    }

    private function _getStruct()
    {
        $d = [
            "step1" =>  [
                "name" =>  "ИП Иванов",
                "legal_type" =>  "person",
                "address_jur" =>  "москва кремль",
                "inn" =>  "123345",
                "kpp" =>  "43553",
                "position" =>  "генеральный директор",
                "fio" =>  "Васин А.А.",
                "ogrn" =>  "123",
                "last_name" =>  "Иванов",
                "first_name" =>  "Иван",
                "middle_name" =>  "Иванович",
                "passport_serial" =>  "77 00",
                "passport_number" =>  "1123456",
                "passport_date_issued" =>  "2005-01-01",
                "passport_issued" =>  "МИД",
                "address" =>  "aaa"
            ],
            "step2" =>  [
                "link_dogovor" => "http => //lk.mcn.loc"
            ],
            "step3" => [
                "contact_phone" => "",
                "contact_fio" => "",
                "file_list" => ["dfgsdf.pdf","sdfgsdfgsdfgs.txt"],
                "is_upload" =>  true
            ],
            "step4" => [
                "manager_name" => "Балабес Иванович",
                "manager_phone" => "(495) 105-55-55"
            ],
            "state" => [
                "step" => 1,
                "good" => 0,
                "step_state" => "approve"
            ]
        ];

        return $d;
    }

    private function getOrganizationInformation($account)
    {
        $c = $account->contragent;
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

    private function getContract($account)
    {
        return ["link_dogovor" => "/lk/wizard/get_contract"];
    }

    private function getContactAndContractList($account)
    {
        $contact = $this->getContact($account);
        $files = $this->getClientFiles($account);

        $d = [
                "contact_phone" => $contact["phone"],
                "contact_fio" => $contact["fio"],
                "file_list" => $files,
                "is_upload" => count($files) < 5
            ];

        return $d;
    }

    private function getContact($account)
    {
        $contact = ClientContact::findOne([
            "client_id" => $account->id, 
            "user_id"   => User::CLIENT_USER_ID, 
            "type"      => "phone"
        ]);

        if ($contact)
        {
            return ["phone" => $contact->data, "fio" => $contact->comment];
        } 

        return ["phone" => "", "fio" => ""];
    }

    private function getClientFiles($account)
    {
        $files = [];
        foreach(ClientFile::findAll(["client_id" => $account->id, "user_id" => User::CLIENT_USER_ID]) as $file)
        {
            $files[] = $file->name;
        }

        return $files;
    }

    private function getAccountManager($account)
    {
        $manager = $account->userAccountManager ?: User::findOne(User::DEFAULT_ACCOUNT_MANAGER_USER_ID);

        return [
            "manager_name" => $manager->name,
            "manager_phone" => "(495) 105-55-55".($manager->phone_work ? " доп. ".$manager->phone_work : "")
        ];
    }


    public function actionRead()
    {
        //$data = Yii::$app->request->bodyParams;

        $data = [];
        $data["account_id"] = 9130;

        $account = $this->_checkAndGetAccount($data["account_id"]);

        $d = [
            "step1" => $this->getOrganizationInformation($account),
            "step2" => $this->getContract($account),
            "step3" => $this->getContactAndContractList($account),
            "step4" => $this->getAccountManager($account),
            "state" => $this->getWizardState($account)
        ];

        echo "<pre>";
        print_r($d);
        echo "</pre>";
        return 1;
        return $d;
    }

    public function actionSaveStep1()
    {
        $accountId = 9130;
        $postData = $this->_getStruct();
        $postData = $postData["step1"];

        $account = $this->_checkAndGetAccount($accountId);
        $wizard = LkWizardState::findOne($account->id);

        if (!$wizard)
            return false;

        //$this->setStep1TestData();

        //$postData = Yii::$app->request->post();
        echo "<pre>";
        print_r($postData);
        echo "</pre>";

        $form = new ContragentEditForm();
        var_dump($form->load($postData, ""));

        if (!$form->validate())
        {
            $errors = [];
            foreach($form->getErrors() as $field => $error)
            {
                $errors[] = ["field" => $field, "error" => $error[0]];
            }
            echo "<pre>";
            print_r(["errors" => $errors]);
            echo "</pre>";

            return 1;
            return ["errors" => $errors];
        } else {
            $contragent = $account->contragent;
            echo "<pre>";
            print_r($form->saveInContragent($contragent));
            echo "</pre>";

            return 1;
            return $form->saveInContragent($contragent);
        }
    }

    public function actionSaveStep2()
    {
        $accountId = 9130;
    }

    private function setStep1TestData()
    {
        global $_POST;

        $_POST = [
            "account_id" => 9130,

            'legal_type' => 'ip',

            'name' => '1111',
            'name_full' => date("r"),
            'address_jur' => '',
            'address_post' => '',
            'inn' => '',
            'inn_euro' => '',
            'kpp' => '23213',
            'position' => 'aaa',
            'fio' => 'sss',
            'tax_regime' => '',
            'ogrn' => '',
            'opf' => '',
            'okpo' => '',
            'okvd' => '',

            'last_name' => 'Иван',
            'first_name' => 'Петров',
            'middle_name' => 'Сергеевеич',
            'passport_serial' => '77 03',
            'passport_number' => '123456',
            'passport_issued' => 'ОВД г. Москвы',
            'passport_date_issued' => '2001-02-28',
            'address' => 'г. Москва, кремль'
        ];

        Yii::$app->request->setBodyParams($_POST);
    }

}
