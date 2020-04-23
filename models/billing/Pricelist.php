<?php

namespace app\models\billing;

use app\classes\model\ActiveRecord;
use Yii;
use yii\helpers\Url;

/**
 * @property int $id
 * @property string $name
 * @property string $type
 * @property bool $orig
 *
 * @property string $parser_settings
 *
 * @method static Pricelist findOne($condition)
 * @method static Pricelist[] findAll($condition)
 */
class Pricelist extends ActiveRecord
{
    // Определяет getList (список для selectbox)
    use \app\classes\traits\GetListTrait {
        getList as getListTrait;
    }

    const TYPE_CLIENT = 'client';
    const TYPE_OPERATOR = 'operator';
    const TYPE_LOCAL = 'network_prices';

    const STATE_STORED = 1;
    const STATE_PUBLIC = 2;
    const STATE_SPECIAL = 3;
    const STATE_UNIVERSAL = 4;

    public static $states = [
        self::STATE_STORED => 'Архивный',
        self::STATE_PUBLIC => 'Публичный',
        self::STATE_SPECIAL => 'Специальный',
        self::STATE_UNIVERSAL => 'Универсальный',
    ];

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'voip.pricelist';
    }

    /**
     * Returns the database connection
     *
     * @return \yii\db\Connection
     */
    public static function getDb()
    {
        return Yii::$app->dbPg;
    }

    /**
     * @return bool
     */
    public function isClient()
    {
        return $this->type == self::TYPE_CLIENT;
    }

    /**
     * @return bool
     */
    public function isOperator()
    {
        return $this->type == self::TYPE_OPERATOR;
    }

    /**
     * @return bool
     */
    public function isLocal()
    {
        return $this->type == self::TYPE_LOCAL;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return self::getUrlById($this->id);
    }

    /**
     * @param int $id
     * @return string
     */
    public static function getUrlById($id)
    {
        return Url::to(['/voip/pricelist/edit', 'id' => $id]);
    }

    /**
     * Вернуть список всех доступных значений
     *
     * @param bool|string $isWithEmpty false - без пустого, true - с '----', string - с этим значением
     * @param bool $isWithNullAndNotNull
     * @param string $type
     * @param bool $orig
     * @param bool $priceIncludeVat
     * @return \string[]
     */
    public static function getList(
        $isWithEmpty = false,
        $isWithNullAndNotNull = false,
        $type = null,
        $orig = null,
        $priceIncludeVat = null
    )
    {
        return self::getListTrait(
            $isWithEmpty,
            $isWithNullAndNotNull,
            $indexBy = 'id',
            $select = 'name',
            $orderBy = ['name' => SORT_ASC],
            $where = [
                'AND',
                is_null($priceIncludeVat) ? [] : ['price_include_vat' => $priceIncludeVat],
                [
                    'AND',
                    is_null($type) ? [] : ['type' => $type],
                    is_null($orig) ? [] : ['orig' => $orig]
                ]
            ]
        );
    }

    /**
     * Синхронизировать в биллер
     *
     * @throws \yii\db\Exception
     */
    public static function sync()
    {
        $db = self::getDb();
        $db->createCommand("select event.notify('defs-manual',0)")->execute();
        $db->createCommand("select event.notify('pricelist-manual',0)")->execute();
        $db->createCommand("select event.notify('nnp_pricelist',0)")->execute();
        $db->createCommand("select event.notify('nnp_pricelist_filter_a',0)")->execute();
        $db->createCommand("select event.notify('nnp_pricelist_filter_b',0)")->execute();
        $db->createCommand("select event.notify('nnp_pricelist_prefix_price',0)")->execute();
        $db->createCommand("select event.notify('nnp_pricelist_location',0)")->execute();
    }
}