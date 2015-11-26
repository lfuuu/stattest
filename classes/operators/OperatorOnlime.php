<?php

namespace app\classes\operators;

use Yii;
use yii\db\Query;
use app\dao\reports\ReportExtendsOperatorsDao;

class OperatorOnlime extends Operators
{

    const OPERATOR = 'onlime';
    const OPERATOR_CLIENT = 'onlime';

    public static $requestProducts = [
        'count_3' => [
            'id' => 15804,
            'name' => 'Tele CARD',
            'nameFull' => 'OnLime TeleCARD',
            'id_1c' => [
                'ea05defe-4e36-11e1-8572-00155d881200',
                'f75a5b2f-382f-11e0-9c3c-d485644c7711',
                '6d2dfd2a-211e-11e3-95df-00155d881200',
            ],
        ],
        'count_9' => [
            'id' => 16206,
            'name' => 'HD-ресивер',
            'nameFull' => 'HD-ресивер OnLime (STB HD Zapper Humax)',
            'id_1c' => [
                '4acdb33c-0319-11e2-9c41-00155d881200',
                '14723f35-d423-11e3-9fe5-00155d881200',
            ],
        ],
        'count_11' => [
            'id' => 16207,
            'name' => 'НВ-р. с диском',
            'nameFull' => 'Цифровой ресивер со встроенным жестким диском (STB HD PVR Humax)',
            'id_1c' => [
                '72904487-32f6-11e2-9369-00155d881200',
                '2c6d3955-d423-11e3-9fe5-00155d881200'
            ],
        ],
        'count_12' => [
            'id' => 16315,
            'name' => 'NetGear роутер',
            'nameFull' => 'NetGear Беспроводной роутер, JNR3210-1NNRUS',
            'id_1c' => 'e1a5bf94-0764-11e4-8c79-00155d881200',
        ],
        'count_17' => [
            'id' => 17425,
            'name' => 'ТВ-приставка «Стандарт»',
            'nameFull' => 'ТВ-приставка «Стандарт»',
            'id_1c' => '4dff356b-41a0-11e5-93ad-00155d881200',
        ],
        'count_18' => [
            'id' => 16117,
            'name' => 'Zyxel KEENETIC EXTRA',
            'nameFull' => 'Zyxel KEENETIC EXTRA Беспроводной роутер, для выделенной линии Gigabit Ethernet, с двухдиапазонной 2,4 и 5 ГГц точкой доступа Wi-Fi 802.11n 300+300 Мб',
            'id_1c' => '55b6f916-b3fb-11e3-9fe5-00155d881200',
        ],
        'count_19' => [
            'id' => 16710,
            'name' => 'D-Link DWA-182',
            'nameFull' => 'D-Link DWA-182/RU/C1A Беспроводной адаптер, Wireless AC1200 Dual Band USB Adapter',
            'id_1c' => '14265ab3-9bca-11e4-8402-00155d881200',
        ],
        'count_22' => [
            'id' => 16813,
            'name' => 'Gigaset C530',
            'nameFull' => 'Gigaset C530A IP IP-телефон, радио телефон Siemens Gigaset (IP, черный)',
            'id_1c' => '4454e4d5-a79e-11e4-a330-00155d881200',
        ],
        'count_28' => [
            'id' => 17609,
            'name' => 'Приставка SML-482 HD',
            'nameFull' => 'Приставка, SML-482 HD Base с опцией Wi-Fi',
            'id_1c' => 'd78e0644-6dbc-11e5-9421-00155d881200',
        ]
    ];

    public static $requestModes = [
        'work' => [
            'title' => 'В Обработке',
            'queryModify' => 'modeWorkModify',
        ],
        'close' => [
            'title' => 'Закрыт',
            'queryModify' => 'modeCloseModify',
        ],
        'reject' => [
            'title' => 'Отказ',
            'queryModify' => 'modeRejectModify',
        ],
        'rollback' => [
            'title' => 'Возврат',
            'queryModify' => 'modeRollbackModify',
        ],
    ];

    public static $reportFields = [
        'Номер счета OnLime'                            => 'req_no',
        'Номер счета Маркомнет Сервис'                  => 'bill_no',
        'Дата создания заказа'                          => 'date_creation',
        'Кол-во'                                        => 'products',
        'Оператор'                                      => 'fio_oper',
        'ФИО клиента'                                   => 'fio',
        'Телефон, Адрес'                                => 'contacts',
        'Серийный номер'                                => 'serials',
        'Дата доставки желаемая'                        => 'date_deliv',
        'Дата доставки фактическая'                     => 'date_delivered',
        'Этап'                                          => 'stages_text',
    ];

    public static $reportTemplate = 'onlime_operator';
    public $reportColumnsShiftFrom = 1;

    public static $availableRequestStatuses = [
        33 => 'Новый',
        17 => 'В работе',
        24 => 'Отложен',
        20 => 'Закрыт',
        18 => 'Выполнен',
        21 => 'Отказ',
    ];

    public function getReport()
    {
        return
            parent::getReport()
                ->setOperatorClient(OperatorsFactory::me()->getOperator(OperatorOnlimeStb::OPERATOR_CLIENT));
    }

    public function modeWorkModify(Query $query, $dao)
    {
        $query->leftJoin('tt_stages s', 's.stage_id = t.cur_stage_id');

        $query->andWhere(['between', 'date_creation', $dao->dateFrom, $dao->dateTo]);
        $query->andWhere(['is_rollback' => 0]);
        $query->andWhere(['not in', 'state_id', [2, 20, 21]]);
    }

    public function modeCloseModify(Query $query, $dao)
    {
        $query->leftJoin('tt_stages s', 's.trouble_id = t.id');

        $query->andWhere(['is_rollback' => 0]);
        $query->andWhere(['in', 'state_id', [2, 20]]);
        $query->andWhere(['between', 's.date_start', $dao->dateFrom, $dao->dateTo]);
    }

    public function modeRejectModify(Query $query, $dao)
    {
        $query->leftJoin('tt_stages s', 's.stage_id = t.cur_stage_id');

        $query->andWhere(['between', 'date_creation', $dao->dateFrom, $dao->dateTo]);
        $query->andWhere(['is_rollback' => 0]);
        $query->andWhere(['state_id' => 21]);
    }

    public function modeRollbackModify(Query $query, $dao)
    {
        $query->leftJoin('tt_stages s', 's.stage_id = t.cur_stage_id');

        $query->andWhere(['between', 'date_creation', $dao->dateFrom, $dao->dateTo]);
        $query->andWhere(['is_rollback' => 1]);
    }

}
