<?php

namespace app\modules\uu\forms;

use app\classes\Html;
use app\modules\uu\models\Tag;
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
            Tag::findOne(['id' => $this->id]) :
            new Tag;

        $this->_loadFromInput();
    }

    /**
     * @return ActiveQuery
     */
    public function spawnQuery()
    {
        return Tag::find();
    }

    /**
     * @param array $resources
     * @return string
     */
    public function resourcesMap($resources = [])
    {
        $result = [];
        foreach ($resources as $resource) {
            list($resource, $feature) = explode(', ', $resource);

            $result[] = Html::tag(
                'div',
                (
                array_key_exists($resource, self::$_tagsResourceMap) ?
                    self::$_tagsResourceMap[$resource] :
                    $resource
                ) . ($feature ? ', ' . $feature : ''),
                ['class' => 'label label-info']
            );
        }
        return implode('&nbsp;', $result);
    }

}