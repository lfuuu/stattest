<?php

namespace app\modules\notifier\controllers;

use app\classes\BaseController;
use app\classes\Event;
use app\models\ClientAccount;
use app\models\ClientAccountOptions;
use app\modules\notifier\forms\MonitoringPersonalSchemesForm;
use app\modules\notifier\forms\PersonalSchemeForm;
use Yii;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\base\InvalidParamException;
use yii\data\ArrayDataProvider;

class PersonalSchemeController extends BaseController
{

    /**
     * @return string
     * @throws InvalidParamException
     * @throws InvalidConfigException
     * @throws Exception
     */
    public function actionIndex()
    {
        if (!($clientAccount = $this->getFixClient()) instanceof ClientAccount) {
            Yii::$app->session->setFlash('error', 'Выберите клиента');
            return $this->redirect('/');
        }

        $form = new PersonalSchemeForm;
        $form->clientAccount = $clientAccount;

        if (
            $form->load(Yii::$app->request->post(), 'FormData')
            && $form->validate()
            && $form->saveData()
        ) {
            Yii::$app->session->setFlash('success', 'Данные персональной схемы оповещений обновлены');
        }

        return $this->render('grid', [
            'dataForm' => $form,
            'mailDeliveryLanguageOption' => $clientAccount->getOption(ClientAccountOptions::OPTION_MAIL_DELIVERY_LANGUAGE),
        ]);
    }

    /**
     * @return string
     * @throws InvalidParamException
     */
    public function actionMonitoring()
    {
        if (
            Yii::$app->request->isPost
            && ($clientAccountIds = Yii::$app->request->post('clientAccountIds'))
            && is_array($clientAccountIds)
        ) {
            foreach ($clientAccountIds as $clientAccountId) {
                Event::go(Event::LK_SETTINGS_TO_MAILER, $clientAccountId);
            }
            Yii::$app->session->addFlash('success', 'Данные для синхронизации добавлены в очередь, обработка может занять некоторое время');
        }

        $form = new MonitoringPersonalSchemesForm;

        // Получение списка подписчиков Lk
        $subscribers = $form->getLkSubscribers();
        $clientAccountIds = array_keys($subscribers);

        // Получение схем всех подписчиков Lk из Mailer
        $schemes = $form->getSchemesByClientAccountIds($clientAccountIds);
        $prettySchemes = [];

        /**
         * Приведение схем к виду:
         * ClientAccountId => [
         *     EventTypeId => [
         *         EventType (do_email_personal) => int,
         *         EventType (do_sms_personal) => int,
         *     ]
         * ]
         */
        foreach ($schemes as $row) {
            $prettySchemes[$row['client_account_id']][$row['event_type_id']] = [
                'do_email_personal' => (int)$row['do_email_personal'],
                'do_sms_personal' => (int)$row['do_sms_personal'],
            ];
        }

        // Вычисление расхождения схемы подписчика Lk и схемы подписчика Mailer
        foreach ($subscribers as $clientAccountId => $row) {
            // Схема в Mailer отсутствует, необходима синхронизация
            if (!array_key_exists($clientAccountId, $prettySchemes)) {
                continue;
            }

            // Сортировка массива возможных для подписчика Lk типов оповещений по ключу
            ksort($row);
            // Сортировка массива возможных для подписчика Mailer типов оповещений по ключу
            ksort($prettySchemes[$clientAccountId]);

            // Схемы идентичны, синхронизация не требуется
            if (
                array_key_exists($clientAccountId, $subscribers)
                && $prettySchemes[$clientAccountId] == $row
            ) {
                unset($subscribers[$clientAccountId]);
            }
        }

        return $this->render('monitoring', [
            'dataProvider' => new ArrayDataProvider([
                'allModels' => array_keys($subscribers),
                'sort' => false,
                'pagination' => false,
            ])
        ]);
    }

}
