<?php

namespace app\controllers\api;

use yii;
use app\models\document\DocumentTemplate;
use app\models\LkWizardState;
use app\models\ClientDocument;
use app\models\media\ClientFiles;
use app\models\TroubleState;
use app\models\User;
use app\models\UsageVoip;
use app\models\UsageVirtpbx;
use app\forms\lk_wizard\WizardContragentMcnForm;
use app\forms\lk_wizard\ContactForm;
use yii\base\InvalidParamException;


/**
 * Класс-контроллер работы с российским визардом
 *
 * Class WizardMcnController
 * @package app\controllers\api
 */
class WizardMcnController extends WizardBaseController
{
    protected $lastStep = 4;

    /**
     * @SWG\Post(
     *   tags={"Работа с визардом"},
     *   path="/wizard-mcn/save/",
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
        $postData = $this->loadAndSet();

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

        if ($result === true) {
            if ($step == 1 || $step == 3) {
                if ($step == 1 && $this->wizard->step >= 2) { //если пользователь вернулся назад и пересохранил шаг 1
                    $this->eraseContract();
                }
                $this->wizard->step = $step + 1;
                $this->wizard->state = ($step == 1 ? LkWizardState::STATE_PROCESS : ($step == 3 ? LkWizardState::STATE_REVIEW : LkWizardState::STATE_PROCESS));
                $this->wizard->save();

                if ($this->wizard->step == $this->lastStep && $this->wizard->state == LkWizardState::STATE_REVIEW) {
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
     *   path="/wizard-mcn/get-contract/",
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
        $this->loadAndSet();

        $contract = ClientDocument::findOne([
            "contract_id" => $this->account->contract->id,
            "user_id" => User::CLIENT_USER_ID
        ]);

        $agreement = null;

        if (!$contract) {

            $clientDocument = new ClientDocument();
            $clientDocument->contract_id = $this->account->contract->id;
            $clientDocument->type = 'contract';
            $clientDocument->contract_no = $this->accountId;
            $clientDocument->contract_date = date("Y-m-d");
            $clientDocument->comment = 'ЛК - wizard';
            $clientDocument->user_id = User::CLIENT_USER_ID;
            $clientDocument->template_id = DocumentTemplate::DEFAULT_WIZARD_MCN;
            $clientDocument->save();


            $contract = ClientDocument::find()
                ->where(["user_id" => User::CLIENT_USER_ID])
                ->contractId($this->account->contract->id)
                ->contract()
                ->one();

            if (
                UsageVoip::find()->client($this->account->client)->count()
                || UsageVirtpbx::find()->client($this->account->client)->count()
            ) {
                $clientDocument = new ClientDocument();
                $clientDocument->contract_id = $this->account->contract->id;
                $clientDocument->type = ClientDocument::DOCUMENT_AGREEMENT_TYPE;
                $clientDocument->contract_no = 1;
                $clientDocument->contract_date = date("Y-m-d");
                $clientDocument->comment = 'ЛК - wizard';
                $clientDocument->user_id = User::CLIENT_USER_ID;
                $clientDocument->template_id = DocumentTemplate::ZAKAZ_USLUG;
                $clientDocument->save();

                $clientDocument = new ClientDocument;
                $clientDocument->contract_id = $this->account->contract->id;
                $clientDocument->type = ClientDocument::DOCUMENT_AGREEMENT_TYPE;
                $clientDocument->contract_no = 1;
                $clientDocument->contract_date = date('Y-m-d');
                $clientDocument->comment = 'ЛК - wizard';
                $clientDocument->user_id = User::CLIENT_USER_ID;
                $clientDocument->template_id = DocumentTemplate::DC_telefonia;
                $clientDocument->save();
            }
        }

        if ($this->wizard->step == 2) {
            $this->wizard->step = 3;
            $this->wizard->save();
        }


        if (!$contract || !$contract->fileContent) {
            $content = "error";
        } else {
            $content = $contract->fileContent;

            /** @var ClientDocument $agreements */
            $agreements =
                ClientDocument::find()
                    ->where(['user_id' => User::CLIENT_USER_ID])
                    ->contractId($this->account->contract->id)
                    ->agreement()
                    ->all();

            foreach ($agreements as $agreement) {
                if ($agreement && $agreement->fileContent) {
                    $content .= "<p style=\"page-break-after: always;\"></p>";
                    $content .= $agreement->fileContent;
                }
            }

        }

        $content = $this->renderPartial("//wrapper_html", ['content' => $content]);

        if (isset($this->postData['as_html'])) {
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
     *   path="/wizard-mcn/save-'document/",
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
        $data = $this->loadAndSet();

        if (!isset($data["file"]) || !isset($data["file"]["name"]) || !$data["file"]["content"]) {
            throw new InvalidParamException("data_error");
        }

        $file = $this->account->contract->mediaManager->addFileFromParam(
            $data["file"]["name"],
            base64_decode($data["file"]["content"]),
            "ЛК - wizard",
            User::CLIENT_USER_ID
        );

        if ($file) {
            return ["file_name" => $file->name, "file_id" => $file->id];
        } else {
            return ["errors" => ["file" => "error upload file"]];
        }
    }

    /**
     * @SWG\Post(
     *   tags={"Работа с визардом"},
     *   path="/wizard-mcn/save-contracts/",
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
        $data = $this->loadAndSet();
        $result = $this->_saveStep3($data);

        if ($result === true) {
            return $this->makeWizardFull();
        } else {
            return $result;
        }
    }


    public function makeWizardFull()
    {
        return [
            "step1" => $this->getOrganizationInformation(),
            "step2" => $this->getContract(),
            "step3" => $this->getContactAndContractList(),
            "step4" => $this->getAccountManager(),
            "state" => $this->getWizardState()
        ];
    }


    private function getOrganizationInformation()
    {
        $c = $this->account->contragent;
        $d = [
            "name" => $c->name,
            "legal_type" => $c->legal_type,
            "address_jur" => $c->address_jur,
            "address_post" => $this->account->address_post,
            "inn" => $c->inn,
            "kpp" => $c->kpp,
            "position" => $c->position,
            "fio" => $c->fio,
            "ogrn" => $c->ogrn,
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


    private function getContactAndContractList()
    {
        $contact = $this->getContact();
        $files = $this->getClientFiles();

        $d = [
            "contact_phone" => $contact->data,
            "contact_fio" => $contact->comment,
            "file_list" => $files,
            "is_upload" => count($files) < 10,
        ];

        return $d;
    }

    private function getClientFiles()
    {
        $files = [];
        foreach (ClientFiles::findAll([
            "contract_id" => $this->account->contract_id,
            "user_id" => User::CLIENT_USER_ID
        ]) as $file) {
            $files[] = $file->name;
        }

        return $files;
    }

    private function getAccountManager()
    {
        $manager = $this->account->userAccountManager ?: User::findOne(User::DEFAULT_ACCOUNT_MANAGER_USER_ID);

        if (!$manager) {
            return [
                "manager_name" => "",
                "manager_phone" => "(495) 105-99-99"
            ];
        }

        return [
            "manager_name" => $manager->name,
            "manager_phone" => "(495) 105-99-99" . ($manager->phone_work ? " доп. " . $manager->phone_work : "")
        ];
    }

    private function _saveStep1($stepData)
    {
        $form = new WizardContragentMcnForm();

        $form->load($stepData, "");

        if (!$form->validate()) {
            return $this->getFormErrors($form);
        } else {
            return $form->saveInContragent($this->account);
        }
    }

    private function _saveStep3($stepData)
    {
        $form = new ContactForm;
        $form->setScenario('mcn');

        $form->load($stepData, "");

        if (!$form->validate()) {
            return $this->getFormErrors($form);
        } else {
            return $form->save($this->account);
        }
    }
}
