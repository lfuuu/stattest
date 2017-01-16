<?php
namespace app\commands;

use app\classes\HttpClient;
use app\forms\important_events\ImportantEventsNoticesForm;
use app\models\ClientContact;
use app\models\important_events\ImportantEventsNames;
use app\models\LkNoticeSetting;
use Yii;
use yii\base\InvalidConfigException;
use yii\console\Controller;
use yii\db\Expression;
use yii\db\Query;
use yii\web\BadRequestHttpException;

class LkSettingsToMailerController extends Controller
{

    /**
     * @throws BadRequestHttpException
     * @throws InvalidConfigException
     */
    public function actionIndex()
    {
        $config = Yii::$app->params['MAILER'];

        if (!isset($config, $config['url'])) {
            throw new InvalidConfigException('Mailer was not configured');
        }

        foreach (LkNoticeSetting::$noticeTypes as $type => $typeInLk) {
            $settings = (new Query)
                ->select([
                    'ns.client_id',
                    ImportantEventsNames::IMPORTANT_EVENT_MIN_BALANCE => new Expression('MAX(ns.min_balance)'),
                    ImportantEventsNames::IMPORTANT_EVENT_MIN_DAY_LIMIT => new Expression('MAX(ns.min_day_limit)'),
                    ImportantEventsNames::IMPORTANT_EVENT_PAYMENT_ADD => new Expression('MAX(ns.add_pay_notif)'),
                ])
                ->from(['ns' => LkNoticeSetting::tableName()])
                ->leftJoin([
                    'cc' => ClientContact::tableName(),
                ], 'cc.id = ns.client_contact_id')
                ->where([
                    'ns.status' => LkNoticeSetting::STATUS_WORK,
                    'cc.type' => $typeInLk,
                ])
                ->groupBy('cc.id')
                ->all();

            $result = [];

            foreach ($settings as $setting) {
                $result[] = [
                    'do_' . $type . '_personal' => $setting[ImportantEventsNames::IMPORTANT_EVENT_MIN_BALANCE],
                    'event_code' => ImportantEventsNames::IMPORTANT_EVENT_MIN_BALANCE,
                ];
                $result[] = [
                    'do_' . $type . '_personal' => $setting[ImportantEventsNames::IMPORTANT_EVENT_MIN_DAY_LIMIT],
                    'event_code' => ImportantEventsNames::IMPORTANT_EVENT_MIN_DAY_LIMIT,
                ];
                $result[] = [
                    'do_' . $type . '_personal' => $setting[ImportantEventsNames::IMPORTANT_EVENT_PAYMENT_ADD],
                    'event_code' => ImportantEventsNames::IMPORTANT_EVENT_PAYMENT_ADD,
                ];

                $response = (new HttpClient)
                    ->createJsonRequest()
                    ->setMethod('post')
                    ->setData($result)
                    ->setUrl($config['url'] . ImportantEventsNoticesForm::MAILER_METHOD_UPDATE . '?clientAccountId=' . $setting['client_id'])
                    ->auth(isset($config['auth']) ? $config['auth'] : [])
                    ->send();

                if (!$response->getIsOk()) {
                    throw new BadRequestHttpException($response->getContent());
                }

                echo 'Setting "' . $typeInLk . '" sent for client#' . $setting['client_id'] . PHP_EOL;
            }
        }
    }

}