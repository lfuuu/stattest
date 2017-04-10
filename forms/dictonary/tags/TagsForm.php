<?php

namespace app\forms\dictonary\tags;

use app\classes\Html;
use app\models\Tags;
use Yii;
use InvalidArgumentException;
use app\classes\Form;
use yii\db\ActiveQuery;
use yii\db\Exception;

class TagsForm extends Form
{

    private static $_tagsResourceMap = [
        'ImportantEvents' => 'Значимые события',
        'ImportantEventsNames' => 'Названия значимых событий',
        'UsageTrunk' => 'Телефония Транки',
        'ClientFiles' => 'Файлы клиентов',
    ];

    /**
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function init()
    {
        static::$formModel = (int)$this->id ?
            Tags::findOne(['id' => $this->id]) :
            new Tags;

        $this->_loadFromInput();
    }

    /**
     * @return ActiveQuery
     */
    public function spawnQuery()
    {
        return Tags::find();
    }

    /**
     * @param array $resources
     * @return string
     */
    public function resourcesMap($resources = [])
    {
        $result = [];
        foreach ($resources as $resource) {
            $result[] = Html::tag(
                'div',
                (
                array_key_exists($resource, self::$_tagsResourceMap) ?
                    self::$_tagsResourceMap[$resource] :
                    $resource
                ),
                ['class' => 'label label-info']
            );
        }
        return implode('&nbsp;', $result);
    }

}