<?php
/**
 * Модель для отчета по звонкам 4-го и 5-го класса (/voip/combined-statistics)
 */

namespace app\models\voip\filter;

use app\classes\yii\CTEQuery;
use app\helpers\DateTimeZoneHelper;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\db\Expression;

/**
 * Class CombinedStatistics
 */
class CombinedStatistics extends Model
{
    use \app\classes\traits\AddClientAccountFilterTraits;

    /**
     * @var string
     */
    public $dateStart = null;

    /**
     * @var string
     */
    public $dateEnd = null;

    /**
     * @var int
     */
    public $account_id = null;

    /**
     * @var string
     */
    public $date = null;

    /**
     * @var string
     */
    public $five_class = null;

    /**
     * Rule array
     *
     * @return array
     */
    public function rules()
    {
        return [
            [['account_id'], 'trim'],
            [['date', 'five_class'], 'string'],
            [['account_id'], 'integer'],
        ];
    }

    /**
     * Загрузка свойств объекта из GET-параметров
     *
     * @param array $get
     *
     * @return bool
     */
    public function load(array $get)
    {
        if (isset($get['date']) && strpos($get['date'], ':')) {
            list($get['dateStart'], $get['dateEnd']) = explode(':', $get['date']);
            try {
                $dateStart = new \DateTime($get['dateStart']);
                $dateEnd = new \DateTime($get['dateEnd']);
                $dateEnd->modify('+1 day');
                $interval = $dateEnd->diff($dateStart);
                if ($interval->days > 30) {
                    Yii::$app->session->addFlash('error', 'Временной период больше одного месяца');
                    return false;
                }
            } catch (\Exception $e) {
                Yii::$app->session->addFlash('error', 'Неправильный формат даты');
                return false;
            }

            $this->dateStart = $dateStart->format(DateTimeZoneHelper::DATETIME_FORMAT);
            $this->dateEnd = $dateEnd->format(DateTimeZoneHelper::DATETIME_FORMAT);
        }

        $this->account_id = $this->_getCurrentClientAccountId();

        parent::load($get, '');

        return $this->validate();
    }

    /**
     * Объединенная статисктика звоков 4 и 5 классов
     *
     * @return ActiveDataProvider|ArrayDataProvider
     */
    public function getStatistics()
    {
        if (!$this->dateStart || !$this->dateEnd || !$this->account_id) {
            return new ArrayDataProvider(
                [
                    'allModels' => [],
                ]
            );
        }

        $query = new CTEQuery();

        $sub_query1 = new CTEQuery();

        $cte = new CTEQuery();

        $cte->from('cdr_1.nispd')
            ->orderBy(['connect_time' => SORT_DESC])
            ->limit(5000);

        $sub_query1->from(['ll' => 'cdr_1.leg_links']);

        $this->account_id
        && $cte->andWhere(['OR', ['src_account_id' => $this->account_id], ['dst_account_id' => $this->account_id]])
        && $sub_query1->andWhere(['OR', ['ll.start_account_id' => $this->account_id], ['ll.end_account_id' => $this->account_id]]);

        $this->dateStart
        && $cte->andWhere(['>=', 'connect_time', $this->dateStart])
        && $sub_query1->andWhere(['>=', 'll.connect_time', $this->dateStart]);

        $this->dateEnd
        && $cte->andWhere(['<', 'connect_time', $this->dateEnd])
        && $sub_query1->andWhere(['<', 'll.connect_time', $this->dateEnd]);

        $sub_query2 = new CTEQuery();

        $sub_query2->select(
            [
                'calls' => new Expression(
                    "json_agg((
                        SELECT 
                            ll FROM 
                       (SELECT
                            COALESCE(ll.object_data->>'number', ll.src_number) AS src_number, 
                            ll.dst_number, 
                            COALESCE (ll.status, 'NO ANSWER') AS status,
                            ll.connect_time::timestamp,
                            ll.disconnect_time::timestamp,
                            ll.object_type,
                            ll.sip_ip,
                            ll.object_kind) AS ll)
                        ORDER BY
                            ll.connect_time DESC)"
                ),
                'src_signalling_call_id' => new Expression('max(ll.src_signalling_call_id::varchar)'),
                'dst_signalling_call_id' => new Expression('max(ll.dst_signalling_call_id::varchar)'),
                'vpbx_id' => new Expression('min(ll.dst_vpbx_id)'),
                'connect_time' => new Expression('min(ll.connect_time)'),
            ]
        )
            ->from(['ll' => $sub_query1])
            ->groupBy(
                [
                    'll.uniqueid'
                ]
            );

        $query->select(
            [
                'src_number_first' => 'n1.src_number',
                'src_operator_name_first' => 'n1.src_operator_name',
                'src_country_name_first' => 'n1.src_country_name',
                'src_region_name_first' => 'n1.src_region_name',
                'src_city_name_first' => 'n1.src_city_name',
                'dst_number_first' => 'n1.dst_number',
                'dst_operator_name_first' => 'n1.dst_operator_name',
                'dst_country_name_first' => 'n1.dst_country_name',
                'dst_region_name_first' => 'n1.dst_region_name',
                'dst_city_name_first' => 'n1.dst_city_name',
                'src_number_last' => 'n2.src_number',
                'src_operator_name_last' => 'n2.src_operator_name',
                'src_country_name_last' => 'n2.src_country_name',
                'src_region_name_last' => 'n2.src_region_name',
                'src_city_name_last' => 'n2.src_city_name',
                'dst_number_last' => 'n2.dst_number',
                'dst_operator_name_last' => 'n2.dst_operator_name',
                'dst_country_name_last' => 'n2.dst_country_name',
                'dst_region_name_last' => 'n2.dst_region_name',
                'dst_city_name_last' => 'n2.dst_city_name',
                'five_class' => 'll.calls',
                'll.vpbx_id',
            ]
        )->from(['ll' => $sub_query2])
            ->leftJoin('nspd n1', 'n1.dst_signalling_call_id = ll.src_signalling_call_id::uuid')
            ->leftJoin('nspd n2', 'n2.src_signalling_call_id = ll.dst_signalling_call_id::uuid')
            ->orderBy(['ll.connect_time' => SORT_DESC])
            ->addWith(['nspd' => $cte]);

        $count = $sub_query2->liteRowCount(Yii::$app->dbPgSlaveCache);

        return new ActiveDataProvider(
            [
                'db' => Yii::$app->dbPgSlaveCache,
                'query' => $query,
                'pagination' => [],
                'totalCount' => $count,
            ]
        );
    }

}
