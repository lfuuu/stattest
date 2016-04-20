<?php

namespace app\controllers\api;

use app\models\document\DocumentTemplate;
use Yii;
use app\classes\ApiController;
use app\classes\BaseController;

use app\classes\Assert;
use app\models\LkWizardState;
use app\models\ClientAccount;
use app\models\ClientContact;
use app\models\ClientDocument;
use app\models\media\ClientFiles;
use app\models\BusinessProcessStatus;
use app\models\TroubleState;
use app\models\User;
use app\models\UsageVoip;
use app\models\UsageVirtpbx;
use app\forms\lk_wizard\WizardContragentForm;
use app\forms\lk_wizard\ContactForm;


class WizardMcnController extends /*BaseController*/ApiController
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
        if ($account->contract->business_process_status_id != BusinessProcessStatus::TELEKOM_MAINTENANCE_ORDER_OF_SERVICES) //Клиента включили
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

    /**
     * @SWG\Definition(
     *   definition="wizard_state",
     *   type="object",
     *   required={"step","good","wizard_type"},
     *   @SWG\Property(property="step",type="integer",description="текущий шаг визарда"),
     *   @SWG\Property(property="good",type="integer",description="предыдущий завершённый шаг визарда"),
     *   @SWG\Property(property="wizard_type",type="integer",description="тип визарда"),
     *   @SWG\Property(property="step_state",type="integer",description="статус шага"),
     * ),
     * @SWG\Post(
     *   tags={"Работа с визардом"},
     *   path="/wizard_mcn/state/",
     *   summary="Получение статуса, в котором находится визард",
     *   operationId="Получение статуса, в котором находится визард",
     *   @SWG\Parameter(name="account_id",type="integer",description="идентификатор лицевого счёта",in="formData"),
     *   @SWG\Response(
     *     response=200,
     *     description="список тикетов",
     *     @SWG\Definition(
     *       type="array",
     *       @SWG\Items(
     *         ref="#/definitions/wizard_state"
     *       )
     *     )
     *   ),
     *   @SWG\Response(
     *     response="default",
     *     description="Ошибки",
     *     @SWG\Schema(
     *       ref="#/definitions/error_result"
     *     )
     *   )
     * )
     */
    public function actionState()
    {
        $this->loadAndCheck(false);

        return $this->getWizardState();
    }

    /**
     * @SWG\Definition(
     *   definition="wizard_data",
     *   type="object",
     *   @SWG\Property(property="step1",type="object",description="информация по первому шагу"),
     *   @SWG\Property(property="step2",type="object",description="информация по второму шагу"),
     *   @SWG\Property(property="step3",type="object",description="информация по третьему шагу"),
     *   @SWG\Property(property="step4",type="object",description="информация по четвёртому шагу"),
     *   @SWG\Property(property="state",type="object",description="текущий шаг"),
     * ),
     * @SWG\Post(
     *   tags={"Работа с визардом"},
     *   path="/wizard_mcn/read/",
     *   summary="Получение всей необходимой информации для визарда",
     *   operationId="Получение всей необходимой информации для визарда",
     *   @SWG\Parameter(name="account_id",type="integer",description="идентификатор лицевого счёта",in="formData"),
     *   @SWG\Response(
     *     response=200,
     *     description="информация по визарду",
     *     @SWG\Schema(
     *       ref="#/definitions/wizard_data"
     *     )
     *   ),
     *   @SWG\Response(
     *     response="default",
     *     description="Ошибки",
     *     @SWG\Schema(
     *       ref="#/definitions/error_result"
     *     )
     *   )
     * )
     */
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

    /**
     * @SWG\Post(
     *   tags={"Работа с визардом"},
     *   path="/wizard_mcn/save/",
     *   summary="Сохранение состояния визарда",
     *   operationId="Сохранение состояния визарда",
     *   @SWG\Parameter(name="account_id",type="integer",description="идентификатор лицевого счёта",in="formData"),
     *   @SWG\Parameter(name="step1",type="array",items="#/definitions/step1",description="информация по первому шагу",in="formData"),
     *   @SWG\Parameter(name="step2",type="array",items="#/definitions/step2",description="информация по второму шагу",in="formData"),
     *   @SWG\Parameter(name="step3",type="array",items="#/definitions/step3",description="информация по третьему шагу",in="formData"),
     *   @SWG\Parameter(name="step4",type="array",items="#/definitions/step4",description="информация по четвёртому шагу",in="formData"),
     *   @SWG\Parameter(name="state",type="integer",description="текущий шаг",in="formData"),
     *   @SWG\Response(
     *     response=200,
     *     description="информация по визарду",
     *     @SWG\Schema(
     *       ref="#/definitions/wizard_data"
     *     )
     *   ),
     *   @SWG\Response(
     *     response="default",
     *     description="Ошибки",
     *     @SWG\Schema(
     *       ref="#/definitions/error_result"
     *     )
     *   )
     * )
     */
    public function actionSave()
    {
        $postData = $this->loadAndCheck();

        $result = true;

        $step = $postData["state"]["step"];

        switch ($step) {
            case 1: {
                $result = $this->_saveStep1($postData["step1"]);
                break;
            }
            case 3: {
                $result = $this->_saveStep3($postData["step3"]);
                break;
            }
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

    /**
     * @SWG\Post(
     *   tags={"Работа с визардом"},
     *   path="/wizard_mcn/get_contract/",
     *   summary="Получение договора",
     *   operationId="Получение договора",
     *   @SWG\Parameter(name="account_id",type="integer",description="идентификатор лицевого счёта",in="formData"),
     *   @SWG\Response(
     *     response=200,
     *     description="договор в формате HTML или PDF",
     *   ),
     *   @SWG\Response(
     *     response="default",
     *     description="Ошибки",
     *     @SWG\Schema(
     *       ref="#/definitions/error_result"
     *     )
     *   )
     * )
     */
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
            $clientDocument->template_id = DocumentTemplate::findOne(['name' => 'Dog_UslugiSvayzi'])['id'];
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
                $clientDocument->template_id = DocumentTemplate::findOne(['name' => 'Zakaz_Uslug'])['id'];
                $clientDocument->save();

                $clientDocument = new ClientDocument;
                $clientDocument->contract_id = $this->account->contract->id;
                $clientDocument->type = 'agreement';
                $clientDocument->contract_no = 1;
                $clientDocument->contract_date = date('Y-m-d');
                $clientDocument->comment = 'ЛК - wizard';
                $clientDocument->user_id = User::CLIENT_USER_ID;
                $clientDocument->template_id = DocumentTemplate::findOne(['name' => 'DC_telefonia'])['id'];
                $clientDocument->save();
            }
        }

        if ($this->wizard->step == 2)
        {
            $this->wizard->step = 3;
            $this->wizard->save();
        }

        $content = "";

        if (!$contract || !$contract->fileContent) {
            $content = "Ошибка в данных";
        }
        else {
            $content = $contract->fileContent;

            $agreements =
                ClientDocument::find()
                    ->where(['contract_id' => $this->account->contract->id])
                    ->andWhere(['user_id' => User::CLIENT_USER_ID])
                    ->andWhere(['type' => 'agreement'])
                    ->all();

            foreach ($agreements as $agreement) {
                if ($agreement && $agreement->fileContent) {
                    $content .= '<p style="page-break-after: always;"></p>';
                    $content .= $agreement->fileContent;
                }
            }

        }

        $content = '<html><head><meta charset="UTF-8"/></head><body>' . $content . '</body></html>';

        if (isset($this->postData['as_html']))
        {
            return $content;
        }

        return base64_encode($this->getPDFfromHTML($content));
    }

    /**
     * @SWG\Definition(
     *   definition="file",
     *   type="object",
     *   required={"name","content"},
     *   @SWG\Property(property="name",type="string",description="название файла"),
     *   @SWG\Property(property="content",type="string",description="содержимое файла"),
     * ),
     * @SWG\Post(
     *   tags={"Работа с визардом"},
     *   path="/wizard_mcn/save_document/",
     *   summary="Сохранение документа",
     *   operationId="Сохранение документа",
     *   @SWG\Parameter(name="account_id",type="integer",description="идентификатор лицевого счёта",in="formData"),
     *   @SWG\Parameter(name="file",type="file",description="скан документа",in="formData",@SWG\Schema(ref="#/definitions/file")),
     *   @SWG\Response(
     *     response=200,
     *     description="Загруженный файл",
     *     @SWG\Schema(
     *       type="object",
     *       @SWG\Property(property="file_name",type="string",description="название файла"),
     *       @SWG\Property(property="file_id",type="integer",description="идентификатор файла"),
     *     )
     *   ),
     *   @SWG\Response(
     *     response="default",
     *     description="Ошибки",
     *     @SWG\Schema(
     *       ref="#/definitions/error_result"
     *     )
     *   )
     * )
     **/
    public function actionSaveDocument()
    {
        $data = $this->loadAndCheck();

        if (!isset($data["file"]) || !isset($data["file"]["name"]) || !$data["file"]["content"])
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

    /**
     * @SWG\Post(
     *   tags={"Работа с визардом"},
     *   path="/wizard_mcn/save_contracts/",
     *   summary="Сохранение договора",
     *   operationId="Сохранение договора",
     *   @SWG\Parameter(name="account_id",type="integer",description="идентификатор лицевого счёта",in="formData"),
     *   @SWG\Parameter(name="contact_phone",type="string",description="Контактный номер",in="formData"),
     *   @SWG\Parameter(name="contact_fio",type="string",description="Контактное ФИО",in="formData"),
     *   @SWG\Response(
     *     response=200,
     *     description="информация по визарду",
     *     @SWG\Schema(
     *       ref="#/definitions/wizard_data"
     *     )
     *   ),
     *   @SWG\Response(
     *     response="default",
     *     description="Ошибки",
     *     @SWG\Schema(
     *       ref="#/definitions/error_result"
     *     )
     *   )
     * )
     */
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
                "is_upload" => count($files) < 10,
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
