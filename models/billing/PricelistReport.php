<?php
namespace app\models\billing;

use app\classes\traits\PgsqlArrayFieldParseTrait;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\db\Query;

/**
 * @property int $id
 * @property int $report_type_id
 * @property string $name
 * @property int $instance_id
 * @property int[] $pricelist_ids
 * @property string[] $dates
 * @property string $created_at
 * @property string $generated_at
 * @property int $volume_calc_task_id
 * @property bool $use_rossvyaz_codes
 */
class PricelistReport extends ActiveRecord
{

    use PgsqlArrayFieldParseTrait;

    const REGION_TYPE_ROUTING = 1;
    const REGION_TYPE_OPERATOR = 2;
    const REGION_TYPE_ANALYZE = 3;

    private $_pricelistReportData = [];

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'voip.pricelist_report';
    }

    /**
     * @return array
     */
    public static function getDb()
    {
        return Yii::$app->dbPg;
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['report_type_id', 'instance_id',], 'integer'],
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'instance_id' => 'Регион',
            'name' => 'Наименование',
            'pricelists' => 'Прайслисты',
        ];
    }

    /**
     * @inheritdoc
     */
    public function prepareData()
    {
        $pricelistIds = $this->getPricelistsIds();
        $dates = $this->getDates();

        $pricelists = self::getPricelists($pricelistIds);

        foreach ($pricelistIds as $index => $pricelistId) {
            $this->_pricelistReportData[$pricelistId] = [
                'date' => array_key_exists($index, $dates) ? $dates[$index] : '',
                'pricelist' => array_key_exists($pricelistId, $pricelists) ? $pricelists[$pricelistId] : false,
            ];
        }
    }

    /**
     * @return array[int pricelistId, string date, Pricelist|boll pricelist]
     */
    public function getData()
    {
        return $this->_pricelistReportData;
    }

    /**
     * @return array
     */
    public function getPricelistsIds()
    {
        if (($pricelists = $this->_parseFieldValue($this->pricelist_ids)) === false) {
            return [];
        }

        return $pricelists;
    }

    /**
     * @return array
     */
    public function getDates()
    {
        if (($dates = $this->_parseFieldValue($this->dates)) === false) {
            return [];
        }

        return $dates;
    }

    /**
     * @param array $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = self::find()
            ->orderBy([
                'created_at' => SORT_DESC,
            ]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => false,
            'sort' => false,
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        if ((int)$this->report_type_id) {
            $query->andFilterWhere(['report_type_id' => $this->report_type_id]);
        }

        if ((int)$this->instance_id) {
            $query->andFilterWhere(['instance_id' => $this->instance_id]);
        }

        return $dataProvider;
    }

    /**
     * @param int[] $pricelistIds
     * @return \yii\db\ActiveRecord[]
     */
    public static function getPricelists(array $pricelistIds = [])
    {
        return Pricelist::find()
            ->where(['IN', 'id', $pricelistIds])
            ->indexBy('id')
            ->all();
    }

    /**
     * @param int $reportId
     * @return array
     */
    public static function getPricelistData($reportId)
    {
        return (new Query)
            ->select([
                'report.prefix', 'report.prices', 'report.orders',
                'destination' => 'geo.name', 'geo.country', 'geo.region', 'geo.zone',
                'destinations.mob',
                'pricelist_report.pricelist_ids',
            ])
            ->from([
                'pricelist_report' => PricelistReport::tableName(),
                'report' => new Expression('voip.select_pricelist_report(' . $reportId . ', false)')
            ])
            ->leftJoin(['destinations' => VoipDestinations::tableName()], 'report.prefix = destinations.defcode')
            ->leftJoin(['geo' => Geo::tableName()], 'geo.id = destinations.geo_id')
            ->where([
                'pricelist_report.id' => $reportId,
            ])
            ->orderBy([
                'geo.name' => SORT_ASC,
                'report.prefix' => SORT_ASC,
            ])
            ->all(Yii::$app->dbPgSlave);
    }

}