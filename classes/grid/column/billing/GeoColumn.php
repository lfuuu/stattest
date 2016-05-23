<?php

namespace app\classes\grid\column\billing;

use app\classes\grid\column\DataColumn;
use app\models\billing\Geo;
use kartik\grid\GridView;
use yii\db\ActiveRecord;

class GeoColumn extends DataColumn
{
    public $filterType = GridView::FILTER_SELECT2;
    public $filter = ['' => '----'];
    protected $idToName = [];

    public function __construct($config = [])
    {
        // нескольким префиксам соотвествует один регион
        // по любому из них должно отображаться название ($this->prefixToName)
        // но в списке должно быть уникальное имя ($this->filter). Ключ при этом - числа через запятую
        $this->idToName = Geo::getList(true);

        $filter = [];
        /** @var Geo $geo */
        foreach ($this->idToName as $geo) {
            // а фильтр строить по другому массиву, у которого в качестве ключа - список id
            // чтобы получить такой массив - строим инвертированный и потом его инвертируем
            if (!$geo instanceof Geo) {
                continue;
            }
            if (isset($filter[$geo->name])) {
                $filter[$geo->name] .= ',' . $geo->id;
            } else {
                $filter[$geo->name] = $geo->id;
            }
        }
        $this->filter += array_flip($filter);

        parent::__construct($config);
        !isset($this->filterOptions['class']) && ($this->filterOptions['class'] = '');
        $this->filterOptions['class'] .= ' geo-column';
    }

    /**
     * Вернуть отображаемое значение ячейки
     *
     * @param ActiveRecord $model
     * @param string $key
     * @param int $index
     * @return string
     */
    protected function renderDataCellContent($model, $key, $index)
    {
        $value = $this->getDataCellValue($model, $key, $index);
        return isset($this->idToName[$value]) ? (string)$this->idToName[$value] : $value;
    }
}