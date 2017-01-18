<?php

namespace app\modules\notifier\models;

use yii\db\ActiveRecord;
use yii\db\Query;
use app\models\Business;
use app\models\BusinessProcessStatus;
use app\models\ClientAccount;
use app\models\ClientContract;
use app\models\ClientContragent;

/**
 * @property string $country_code
 * @property string $event
 * @property bool $do_email
 * @property bool $do_sms
 * @property bool $do_email_monitoring
 * @property bool $do_email_operator
 */
class Schemes extends ActiveRecord
{

    const NOTIFICATION_TYPE_EMAIL_MONITORING = 'do_email_monitoring';
    const NOTIFICATION_TYPE_EMAIL_OPERATOR = 'do_email_operator';
    const NOTIFICATION_TYPE_EMAIL = 'do_email';
    const NOTIFICATION_TYPE_SMS = 'do_sms';

    public static $types = [
        self::NOTIFICATION_TYPE_EMAIL_MONITORING,
        self::NOTIFICATION_TYPE_EMAIL_OPERATOR,
        self::NOTIFICATION_TYPE_EMAIL,
        self::NOTIFICATION_TYPE_SMS,
    ];

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'notifier_schemes';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['event',], 'string'],
            [['country_code', ], 'integer'],
            [['do_email', 'do_sms', 'do_email_monitoring', 'do_email_operator', ], 'boolean'],
            [['country_code', 'event',], 'required'],
        ];
    }

    /**
     * @param int $countryCode
     * @return Query
     */
    public static function findClientInCountry($countryCode)
    {
        return (new Query)
            ->from([
                'client' => ClientAccount::tableName()
            ])
            ->innerJoin(['contract' => ClientContract::tableName()], 'contract.id = client.contract_id')
            ->innerJoin(['contragent' => ClientContragent::tableName()], 'contragent.id = contract.contragent_id')
            ->where(['contragent.country_id' => $countryCode])
            ->andWhere(['IN', 'contract.business_id', [Business::TELEKOM, Business::OPERATOR]])
            ->andWhere(['IN', 'contract.business_process_status_id', [
                BusinessProcessStatus::TELEKOM_MAINTENANCE_CONNECTED, // Телеком - Подключаемые
                BusinessProcessStatus::TELEKOM_MAINTENANCE_WORK, // Телеком - Включенные
                BusinessProcessStatus::TELEKOM_MAINTENANCE_ORDER_OF_SERVICES, // Телеком - Заказ услуг
                BusinessProcessStatus::OPERATOR_OPERATORS_ACTING, // Оператор - Действующий
            ]]);
    }

}
