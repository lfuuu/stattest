<?php

namespace app\modules\notifier\forms;

use app\classes\Form;
use app\models\ClientContact;
use app\models\important_events\ImportantEventsNames;
use app\models\LkNoticeSetting;
use app\modules\notifier\components\traits\FormExceptionTrait;
use app\modules\notifier\Module as Notifier;
use Yii;
use yii\db\Expression;

class MonitoringPersonalSchemesForm extends Form
{

    use FormExceptionTrait;

    public static $knownEvents = [
        ImportantEventsNames::MIN_BALANCE => ImportantEventsNames::MIN_BALANCE,
        ImportantEventsNames::MIN_DAY_LIMIT => ImportantEventsNames::MIN_DAY_LIMIT,
        ImportantEventsNames::PAYMENT_ADD => ImportantEventsNames::ADD_PAY_NOTIF,
    ];

    /**
     * @return array
     */
    public function getLkSubscribers()
    {
        $query = LkNoticeSetting::find()
            ->select([
                'contacts.client_id',
                'contacts.type',
                ImportantEventsNames::MIN_BALANCE => new Expression('MAX(min_balance)'),
                ImportantEventsNames::MIN_DAY_LIMIT => new Expression('MAX(min_day_limit)'),
                ImportantEventsNames::PAYMENT_ADD => new Expression('MAX(add_pay_notif)'),
            ])
            ->innerJoin(['contacts' => ClientContact::tableName()], 'contacts.id = client_contact_id')
            ->where([
                'AND',
                array_merge(['OR'], array_values(self::$knownEvents)),
                ['status' => LkNoticeSetting::STATUS_WORK],
                ['NOT', 'contacts.is_official'],
            ])
            ->groupBy([
                'contacts.client_id',
                'contacts.type',
            ])
            ->asArray();

        $result = [];
        $types = array_flip(LkNoticeSetting::$noticeTypes);

        foreach ($query->each() as $row) {
            foreach (self::$knownEvents as $eventName => $eventField) {
                foreach (LkNoticeSetting::$noticeTypes as $type) {
                    $key = 'do_' . $types[$type] . '_personal';

                    if (!isset($result[$row['client_id']][$eventName][$key])) {
                        $result[$row['client_id']][$eventName][$key] = 0;
                    }

                    if ($row['type'] === $type) {
                        $result[$row['client_id']][$eventName][$key] = $row[$eventName];
                    }
                }
            }
        }

        return $result;
    }

    /**
     * @param array $clientAccountIds
     * @return array|bool
     */
    public function getSchemesByClientAccountIds(array $clientAccountIds = [])
    {
        try {
            $response = Notifier::getInstance()->actions->getSchemePersonalByIds($clientAccountIds);

            if (!$response || !isset($response['scheme'])) {
                throw new \LogicException("Client's scheme not found");
            }

            return $response['scheme'];
        } catch (\Exception $e) {
            return $this->catchException($e);
        }
    }

}