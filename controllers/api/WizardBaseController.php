<?php

namespace app\controllers\api;

use app\classes\Form;
use app\classes\Html2Pdf;
use app\models\Country;
use yii;
use app\classes\ApiController;
use app\models\LkWizardState;
use app\models\ClientAccount;
use app\models\ClientDocument;
use app\models\BusinessProcessStatus;
use app\models\TroubleState;
use app\models\User;
use yii\base\InvalidParamException;
use yii\base\Model;


/**
 * Class WizardBaseController
 */
abstract class WizardBaseController extends ApiController
{
    protected $lastStep = 4;

    protected $accountId = null;

    /** @var ClientAccount */
    protected $account = null;

    /** @var LkWizardState */
    protected $wizard = null;

    protected $postData = [];

    /**
     * Загружает из POST'а данные и проверяет, что указанный accountId правильный
     *
     * @param bool $isCheckWizard включить в проверку наличие включенного визарда
     * @return array|mixed
     */
    protected function loadAndSet($isCheckWizard = true)
    {
        $this->postData = Yii::$app->request->bodyParams;

        $this->_getAndSetAccount();

        $this->wizard = LkWizardState::findOne([
            "contract_id" => $this->account->contract->id,
            "is_on" => 1
        ]);

        if ($isCheckWizard && !$this->wizard) {
            throw new InvalidParamException("account_is_bad");
        }

        $this->_postProcessing();

        return $this->postData;
    }

    /**
     * Проверяет и получает ЛС
     */
    private function _getAndSetAccount()
    {
        if (!isset($this->postData["account_id"])) {
            throw new InvalidParamException("account_is_bad");
        }

        $this->accountId = $this->postData["account_id"];

        if (is_array($this->accountId) || !$this->accountId || !preg_match("/^[0-9]{1,6}$/", $this->accountId)) {
            throw new InvalidParamException("account_is_bad");
        }

        $this->account = ClientAccount::findOne($this->accountId);
        if (!$this->account) {
            throw new InvalidParamException("account_not_found");
        }

        $countryCode = $this->account->getUuCountryId() ?: Country::RUSSIA;
        $country = Country::findOne(['code' => $countryCode]) ?: Country::findOne(['code' => Country::RUSSIA]);

        \Yii::$app->language = $country->language;
    }

    /**
     * Постобработчик завершения работы визарда
     * в случаи включения его ЛС
     * или перевода заявки на подключение в отработанное состояние
     */
    private function _postProcessing()
    {
        // Клиента включили
        if ($this->account->contract->business_process_status_id != BusinessProcessStatus::TELEKOM_MAINTENANCE_ORDER_OF_SERVICES) {
            /** @var LkWizardState $wizard */
            $wizard = LkWizardState::findOne(['contract_id' => $this->account->contract->id]);
            if ($wizard) {
                if ($wizard->step < $this->lastStep || ($wizard->step == $this->lastStep && $wizard->state == LkWizardState::STATE_REVIEW)) {
                    $wizard->is_on = 0;
                    $wizard->save();
                } else {
                    if (
                        !$wizard->trouble || !in_array($wizard->trouble->currentStage->state_id, [
                            TroubleState::CONNECT__INCOME,
                            TroubleState::CONNECT__NEGOTIATION,
                            TroubleState::CONNECT__VERIFICATION_OF_DOCUMENTS
                        ])
                    ) {
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
     *   tags={"LkWizard"},
     *   path="/wizard-mcn/state",
     *   summary="Российский визард. Получение состояния",
     *   operationId="wizard-mcn-state",
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
        $this->loadAndSet(false);

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
     *   tags={"LkWizard"},
     *   path="/wizard-mcn/read",
     *   summary="Российский визард. Получение информации",
     *   operationId="wizard-mcn-read",
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
        $this->loadAndSet();

        $fullWizard = $this->makeWizardFull();

        // удаляем wizard после просмотра последнего шага, с участием менеджера
        if ($this->wizard->step == $this->lastStep && $this->wizard->state != LkWizardState::STATE_REVIEW) {
            $this->wizard->is_on = 0;
            $this->wizard->save();
        }

        return $fullWizard;
    }

    /**
     * Возвращает структуру состояния визарда
     *
     * @return array
     */
    public function getWizardState()
    {
        $wizard = $this->wizard;

        if (!$wizard) {
            return [
                "step" => -1,
                "good" => -1,
                "step_state" => "",
                "wizard_type" => ""
            ];
        }

        if ($wizard->step == $this->lastStep) {
            return [
                "step" => $wizard->step,
                "good" => ($wizard->step - ($wizard->state == 'review' ? 1 : 0)),
                "step_state" => $wizard->state,
                "wizard_type" => $wizard->type
            ];
        } else {
            return [
                "step" => $wizard->step,
                "good" => ($wizard->step - 1),
                "step_state" => $wizard->state,
                "wizard_type" => $wizard->type
            ];
        }
    }

    /**
     * Возвращает полную структуру данных визарда
     *
     * @return array
     */
    abstract function makeWizardFull();

    /**
     * Действие контроллера. Сохранение.
     */
    abstract function actionSave();

    /**
     * Удаление документа
     */
    protected function eraseContract()
    {
        $contracts = ClientDocument::findAll([
            "contract_id" => $this->account->contract->id,
            "user_id" => User::CLIENT_USER_ID
        ]);

        if ($contracts) {
            foreach ($contracts as $contract) {
                $contract->erase();
            }
        }
    }


    /**
     * Оповещение по почте менеджера о завершении заполнения визарда
     *
     * @return User|null
     */
    protected function makeNotify()
    {
        $manager = $this->account->userAccountManager ?: ($this->account->superClient->entryPoint ? $this->account->superClient->entryPoint->connectTroubleUser : null);

        $subj = "ЛК - Wizard";
        $text = "Клиент id: " . $this->account->id . " заполнил Wizard в ЛК";

        // если менеджер установлен
        if ($manager && $manager->email) {
            mail($manager->email, $subj, $text);
        } else {
            // менеджер по умолчанию
            $manager = User::findOne(['id' => User::getDefaultAccountManagerUserId()]);
            if ($manager && $manager->email) {
                mail($manager->email, $subj, $text);
            }
        }

        return $manager;
    }


    /**
     * Функция возвращает ошибки формы в формате ошибки визарда.
     *
     * @param array|Form $error
     *
     * @return array
     */
    protected function getFormErrors($error)
    {
        $errors = [];

        if ($error instanceof Form || $error instanceof Model) {
            $error = $error->getErrors();
        }

        foreach ($error as $field => $messages) {
            $errors[] = ["field" => $field, "error" => $messages[0]];
        }

        return ["validation_errors" => $errors];
    }

    /**
     * Функция генерации PDF из HTML
     *
     * @param string $html
     *
     * @return mixed
     */
    protected function getPDFfromHTML($html)
    {
        $generator = new Html2Pdf();
        $generator->html = $html;

        return $generator->pdf;
    }


    /**
     * Получаем правильную дату
     *
     * @param string $date
     *
     * @return string
     */
    protected function getValidedDateStr($date)
    {
        if (!$date || in_array($date, ['0000-00-00', '1970-01-01'])) {
            return '';
        }

        return $date;
    }
}
