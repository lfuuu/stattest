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
        /*
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
        */
        'count_28' => [
            'id' => 17609,
            'name' => 'Приставка SML-482 HD',
            'nameFull' => 'Приставка, SML-482 HD Base с опцией Wi-Fi',
            'id_1c' => [
                'd78e0644-6dbc-11e5-9421-00155d881200',
                '3a0a1bfe-c8c2-11e5-af87-00155d881200',
            ],
        ],
//        'count_41' => [
//            'id' => 18696,
//            'name' => 'Пульт универс. дистанц. управле- ния',
//            'nameFull' => 'Пульт универсальный дистанционного управления',
//            'id_1c' => '8d4c0c88-145b-11e7-9abf-00155d881200',
//        ],
        'count_44' => [
            'id' => 18711,
            'name' => 'Видео- камера Hikvision DS-2CD-VC1W',
            'nameFull' => 'Видеокамера Hikvision DS-2CD-VC1W',
            'id_1c' => 'bc938b25-2991-11e7-b423-00155d881200',
        ],
//        'count_45' => [
//            'id' => 18716,
//            'name' => 'Пульт RM-E12',
//            'nameFull' => '(Onlime) Пульт, дистанционного управления RM-E12',
//            'id_1c' => '230bbc32-36fc-11e7-9b8f-00155d881200',
//        ],
        'count_46' => [
            'id' => 18790,
            'name' => 'Система Твой умный дом',
            'nameFull' => '(Onlime) Система, мониторинга объекта Твой умный дом комплект "Безопасность"',
            'id_1c' => '30250b07-d35d-11e7-b4f3-00155d881200',
        ],
        'count_47' => [
            'id' => 18825,
            'name' => 'Система Твой умный дом расш.',
            'nameFull' => '(Onlime) Система мониторинга объекта Твой умный дом расширенный комплект "Безопасность"',
            'id_1c' => '5a4f3073-0a6f-11e8-b65c-00155d881200',
        ],
        'count_48' => [
            'id' => 18795,
            'name' => 'Система Твой умный дом баз.',
            'nameFull' => '(Onlime) Система мониторинга объекта Твой умный дом базовый комплект "Безопасность" с датчиком дыма',
            'id_1c' => '5bf04186-d8ec-11e7-bd24-00155d881200',

        ],
        'count_49' => [
            'id' => 18796,
            'name' => 'Система Твой умный дом баз. с д/п',
            'nameFull' => '(Onlime) Система мониторинга объекта Твой умный дом базовый комплект "Безопасность" с датчиком протечки',
            'id_1c' => '8980394e-d8ec-11e7-bd24-00155d881200',

        ],
        'count_50' => [
            'id' => 18849,
            'name' => 'Видео- камера Hikvision DS-I122 ',
            'nameFull' => '(Onlime) Видеокамера Hikvision HiWatch DS- I122 (купольная)',
            'id_1c' => '535eb41f-2790-11e8-8b72-00155d881200',

        ],
        'count_51' => [
            'id' => 18850,
            'name' => 'Видео- камера HikVision DS-I120',
            'nameFull' => '(Onlime) Видеокамера HikVision HiWatch DS-I120 (цилиндрическая)',
            'id_1c' => '6e5cebbf-2790-11e8-8b72-00155d881200',
        ],
        'count_52' => [
            'id' => 18851,
            'name' => 'Инжектор питания ST-4801',
            'nameFull' => '(Onlime) Инжектор питания PoE ST-4801 0,5А',
            'id_1c' => 'adf1e5ff-2790-11e8-8b72-00155d881200',
        ],
        'count_54' => [ //3ed65929-ec98-11e8-8263-00155d881200	18964	(Onlime) Видеокамера IP CS-C2SHW low
            'id' => 18964,
            'name' => 'Видеокамера IP CS-C2SHW low',
            'nameFull' => '(Onlime) Видеокамера IP CS-C2SHW low',
            'id_1c' => '3ed65929-ec98-11e8-8263-00155d881200',
        ],
        'count_55' => [ //cb035e7c-4634-11e9-ab58-00155d881200	19001	(Onlime) Видеокамера IP HikVision Ezviz CS-C6SZW
            'id' => 19001,
            'name' => 'Видеокамера IP HikVision Ezviz CS-C6SZW',
            'nameFull' => '(Onlime) Видеокамера IP HikVision Ezviz CS-C6SZW',
            'id_1c' => 'cb035e7c-4634-11e9-ab58-00155d881200',
        ],
        'count_56' => [ //beced319-c4be-11e9-8b9a-00155d881200	19041	(Onlime) Видеокамера IP Switcam-HS303 low АО НПК РоТеК	Видеокамера IP Switcam-HS303 low АО НПК РоТеК
            'id' => 19041,
            'name' => 'Видеокамера IP Switcam-HS303 low АО НПК РоТеК',
            'nameFull' => '(Onlime) Видеокамера IP Switcam-HS303 low АО НПК РоТеК',
            'id_1c' => 'beced319-c4be-11e9-8b9a-00155d881200',
        ],
        'count_57' => [ //c820ca61-d075-11e9-87a0-00155d881200	19044	(Onlime) Приставка, IPTV Sercomm STB 122A STB Android	Приставка, IPTV Sercomm STB 122A STB Android
            'id' => 19044,
            'name' => 'Приставка, IPTV Sercomm STB 122A STB Android',
            'nameFull' => '(Onlime) Приставка, IPTV Sercomm STB 122A STB Android',
            'id_1c' => 'c820ca61-d075-11e9-87a0-00155d881200',
        ],
        'count_58' => [
            'id' => 19099,
            'name' => 'Умная колонка Prestigio Smartvoice (темно серый)',
            'nameFull' => '(Onlime) -- Умная колонка Prestigio Smartvoice (темно серый)',
            'id_1c' => '880bc818-6d0a-11eb-a10f-00155d881200',
        ],
        'count_59' => [
            'id' => 19100,
            'name' => 'Умная колонка Prestigio Smartvoice (светло серый)',
            'nameFull' => '(Onlime) Умная колонка Prestigio Smartvoice (светло серый)',
            'id_1c' => '9d4d0228-6d0a-11eb-a10f-00155d881200',
        ],
        'count_60' => [
            'id' => 19082,
            'name' => 'Систем мониторинга Колонка капсула Mail.ru дляголосового управления WINK (Черная)',
            'nameFull' => '(Onlime) Систем мониторинга объекта Колонка капсула Mail.ru дляголосового управления WINK (Черная)',
            'id_1c' => 'b487d662-89ea-11ea-80a4-00155d881200',
        ],
        'count_61' => [
            'id' => 19109,
            'name' => 'Систем мониторинга объекта колонка Капсула мини с голосовым помощником Маруся (черная)',
            'nameFull' => '(Onlime) Систем мониторинга объекта колонка Капсула мини с голосовым помощником Маруся (черная)',
            'id_1c' => '574126f5-0c90-11ec-b00f-00155d881200',
        ],
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
        'Номер счета OnLime' => 'req_no',
        'Номер счета Маркомнет Сервис' => 'bill_no',
        'Дата создания заказа' => 'date_creation',
        'Кол-во' => 'products',
        'Оператор' => 'fio_oper',
        'ФИО клиента' => 'fio',
        'Телефон, Адрес' => 'contacts',
        'Серийный номер' => 'serials',
        'Дата доставки желаемая' => 'date_deliv',
        'Дата доставки фактическая' => 'date_delivered',
        'Этап' => 'stages_text',
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
        // какие заявки участвуют в выборке
        $query1 = new Query;
        $query1->select('s.trouble_id');
        $query1->distinct();
        $query1->from(['s' => 'tt_stages']);
        $query1->where(['between', 's.date_start', $dao->dateFrom, $dao->dateTo]);

        // находим первый раз переведенные стадии
        $query2 = new Query;
        $query2->select(['min_stage_id' => 'min(stage_id)']);
        $query2->from(['s' => 'tt_stages', 'a' => $query1]);
        $query2->where('s.trouble_id = a.trouble_id');
        $query2->groupBy(['s.trouble_id', 'state_id']);

        // выбираем эти стадии заявки
        $query3 = new Query;
        $query3->from(['s' => 'tt_stages', 'a' => $query2]);
        $query3->where('s.stage_id = a.min_stage_id');

        $query->leftJoin(['s' => $query3], 's.trouble_id = t.id');

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
