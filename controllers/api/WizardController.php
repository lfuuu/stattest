<?php

namespace app\controllers\api;

use Yii;
use app\classes\ApiController;
use app\classes\BaseController;

use app\classes\Assert;
use app\models\LkWizardState;
use app\models\ClientAccount;
use app\models\ClientContact;
use app\models\ClientDocument;
use app\models\media\ClientFiles;
use app\models\ClientBPStatuses;
use app\models\TroubleState;
use app\models\User;
use app\models\UsageVoip;
use app\models\UsageVirtpbx;
use app\forms\lk_wizard\WizardContragentForm;
use app\forms\lk_wizard\ContactForm;


class WizardController extends /*BaseController*/ApiController
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

        $this->wizard = LkWizardState::findOne(["contract_id" => $this->account->contract->id, "is_on" => 1]);

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
        if ($account->contract->business_process_status_id != ClientBPStatuses::TELEKOM_MAINTENANCE_ORDER_OF_SERVICES) //Клиента включили
        {
            $wizard = LkWizardState::findOne($account->contract->id);
            if ($wizard)
            {
                if ($wizard->step < 4 || ($wizard->step == 4 && $wizard->state == "review"))
                {
                    $wizard->is_on = 0;
                    $wizard->save();
                } else {
                    if (
                        !$wizard->trouble 
                        || !in_array($wizard->trouble->currentStage->state_id, [
                            TroubleState::CONNECT__INCOME,
                            TroubleState::CONNECT__NEGOTIATION,
                            TroubleState::CONNECT__VERIFICATION_OF_DOCUMENTS
                        ])
                    )
                    {
                        $wizard->is_on = 0;
                        $wizard->save();
                    }
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

        $fullWizard = $this->makeWizardFull();

        if ($this->wizard->step == 4 && $this->wizard->state != "review") //удаляем wizard после просмотра последнего шага, с участием менеджера
        {
            $this->wizard->is_on = 0;
            $this->wizard->save();
        }

        return $fullWizard;
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
                if ($step == 1 && $this->wizard->step >= 2) //если пользователь вернулся назад и пересохранил шаг 1
                {
                    $this->eraseContract();
                }
                $this->wizard->step = $step+1;
                $this->wizard->state = ($step == 1 ? "process" : ($step == 3 ? "review" : "process"));
                $this->wizard->save();

                if ($this->wizard->step == 4 && $this->wizard->state == "review")
                {
                    $manager = $this->makeNotify();

                    $this->wizard->trouble->addStage(
                        TroubleState::CONNECT__VERIFICATION_OF_DOCUMENTS, 
                        "Клиент ожидает проверки документов. ЛК - Wizard", 
                        ($manager ? $manager->id : null),
                        User::LK_USER_ID
                    );
                }
            }
        } else { //error
            return $result;
        }

        return $this->makeWizardFull();
    }

    public function actionGetContract()
    {
        $this->loadAndCheck();

        $contract = ClientDocument::findOne([
            "contract_id" => $this->account->contract->id, 
            "user_id" => User::CLIENT_USER_ID
        ]);
        
        $agreement = null;

        if (!$contract)
        {

            $clientDocument = new ClientDocument();
            $clientDocument->contract_id = $this->account->contract->id;
            $clientDocument->type = 'contract';
            $clientDocument->contract_no = $this->accountId;
            $clientDocument->contract_date = date("Y-m-d");
            $clientDocument->comment = 'ЛК - wizard';
            $clientDocument->user_id = User::CLIENT_USER_ID;
            $clientDocument->group = 'mcn';
            $clientDocument->template = 'Dog_UslugiSvayzi';
            $clientDocument->save();


            $contract = ClientDocument::findOne([
                "contract_id" => $this->account->contract->id,
                "user_id" => User::CLIENT_USER_ID,
                "type" => "contract"
                ]);


            if (
                    UsageVoip::find()   ->client($this->account->client)->count()
                ||  UsageVirtpbx::find()->client($this->account->client)->count()
            )
            {

                $clientDocument = new ClientDocument();
                $clientDocument->contract_id = $this->account->contract->id;
                $clientDocument->type = 'agreement';
                $clientDocument->contract_no = 1;
                $clientDocument->contract_date = date("Y-m-d");
                $clientDocument->comment = 'ЛК - wizard';
                $clientDocument->user_id = User::CLIENT_USER_ID;
                $clientDocument->group = 'mcn';
                $clientDocument->template = 'Zakaz_Uslug';
                $clientDocument->save();

                $agreement = ClientDocument::findOne([
                    "contract_id" => $this->account->contract->id,
                    "user_id" => User::CLIENT_USER_ID,
                    "type" => "agreement"
                    ]);
            }
        }

        if ($this->wizard->step == 2)
        {
            $this->wizard->step = 3;
            $this->wizard->save();
        }

        $content = "";

        if (!$contract || !$contract->fileContent)
        {
            $content = "Ошибка в данных";
        } else {
            $content = $contract->fileContent;

            if ($agreement && $agreement->content)
            {
                $content .= "<p style=\"page-break-after: always;\"></p>";
                $content .= $agreement->fileContent;
            }

        }

        $content = "<html><head><meta charset=\"UTF-8\"/></head><body>".$content."</body></html>";
        //return $content;

        return base64_encode($this->getPDFfromHTML($content));
    }


    public function actionSaveDocument()
    {
        $data = $this->loadAndCheck();

        if (!isset($data["file"]) || !isset($data["file"]["name"]) || !$data["file"]["name"])
            throw new \Exception("data_error");

        $file = $this->account->contract->mediaManager->addFileFromParam(
            $data["file"]["name"], 
            base64_decode($data["file"]["content"]), 
            "ЛК - wizard", 
            User::CLIENT_USER_ID
        );

        if ($file)
        {
            return ["file_name" => $file->name, "file_id" => $file->id];
        } else {
            return ["errors" => ["file" => "error upload file"]];
        }
    }

    public function actionSaveContacts()
    {
        $data = $this->loadAndCheck();
        $result = $this->_saveStep3($data);

        if ($result === true)
        {
            return $this->makeWizardFull();
        } else {
            return $result;
        }
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
            return ["step" => -1, "good" => -1, "wizard_type" => ""];

        if ($wizard->step == 4)
        {
            return [
                "step" => $wizard->step, 
                "good" => ($wizard->step-($wizard->state == 'review' ? 1 : 0)), 
                "step_state" => $wizard->state,
                "wizard_type" => $wizard->type
            ];
        } else {
            return [
                "step" => $wizard->step, 
                "good" => ($wizard->step-1),
                "wizard_type" => $wizard->type
            ];
        }
    }


    private function getOrganizationInformation()
    {
        $c = $this->account->contract->contragent;
        $d = [
            "name" =>  $c->name,
            "legal_type" =>  $c->legal_type,
            "address_jur" =>  $c->address_jur,
            "address_post" => $this->account->address_post,
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
            "passport_date_issued" => (($c->person ? $c->person->passport_date_issued : "") ?: "2000-01-01"),
            "passport_issued" => ($c->person ? $c->person->passport_issued : ""),
            "address" => ($c->person ? $c->person->registration_address : "")
        ];
        return $d;
    }

    private function getContract()
    {
        return ["link_dogovor" => "/lk/wizard/contract"];
    }

    private function eraseContract()
    {
        $contracts = ClientDocument::findAll([
            "contract_id" => $this->account->contract->id, 
            "user_id" => User::CLIENT_USER_ID
        ]);

        if ($contracts)
        {
            foreach($contracts as $contract)
            {
                $contract->erase();
            }
        }
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
        foreach(ClientFiles::findAll(["contract_id" => $this->account->contract_id, "user_id" => User::CLIENT_USER_ID]) as $file)
        {
            $files[] = $file->name;
        }

        return $files;
    }

    private function getAccountManager()
    {
        $manager = $this->account->userAccountManager ?: User::findOne(User::DEFAULT_ACCOUNT_MANAGER_USER_ID);

        if (!$manager)
        {
            return [
                "manager_name" => "", 
                "manager_phone" => "(495) 105-99-99"
                ];
        }

        return [
            "manager_name" => $manager->name,
            "manager_phone" => "(495) 105-99-99".($manager->phone_work ? " доп. ".$manager->phone_work : "")
        ];
    }


    private function makeNotify()
    {
        $manager = $this->account->userAccountManager;

        $subj = "ЛК - Wizard";
        $text = "Клиент id: ".$this->account->id." заполнил Wizard в ЛК";

        if ($manager && $manager->email)
        {
            mail($manager->email, $subj, $text);
        } else {
            $manager = User::findOne(User::DEFAULT_ACCOUNT_MANAGER_USER_ID);
            if ($manager && $manager->email)
            {
                mail($manager->email, $subj, $text);
            }
        }

        return $manager ?: null;
    }

    private function _saveStep1($stepData)
    {
        $form = new WizardContragentForm();

        $form->setScenario('mcn');
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
            return $form->saveInContragent($this->account);
        }
    }

    private function _saveStep3($stepData)
    {
        $form = new ContactForm;

        $form->load($stepData, "");

        if (!$form->validate()) // all validators are turned off
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

    private function getPDFfromHTML($html)
    {
        $tmp_dir = sys_get_temp_dir();
        $file_html = tempnam($tmp_dir, "lk_wizard_html");
        $file_pdf  = tempnam($tmp_dir, "lk_wizard_pdf");

        unlink($file_html);
        unlink($file_pdf);

        $file_html = $file_html . ".html";
        $file_pdf  = $file_pdf  . ".pdf";

        /*wkhtmltopdf*/
        $options = ' --quiet -L 15 -R 15 -T 15 -B 15';

        file_put_contents($file_html, $html);

        exec($q = "/usr/bin/wkhtmltopdf ".$options." ".$file_html." ".$file_pdf);

        $content = file_get_contents($file_pdf);

        unlink($file_html);
        unlink($file_pdf);

        return $content;
    }
}
