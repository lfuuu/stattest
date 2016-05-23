<?php

namespace app\classes\monitoring;

use Yii;
use yii\base\Component;
use yii\data\ArrayDataProvider;
use yii\db\Expression;
use yii\db\Query;
use app\classes\Html;
use app\models\important_events\ImportantEvents;
use app\models\important_events\ImportantEventsNames;

class ImportantEventsWithoutNames extends Component implements MonitoringInterface
{

    /**
     * @return string
     */
    public function getKey()
    {
        return 'important_events_without_names';
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return 'Значимые события без названий';
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return 'Значимые события без названий';
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        return [
            [
                'attribute' => 'event',
                'label' => 'Код события',
            ],
            [
                'label' => '',
                'format' => 'raw',
                'value' => function ($data) {
                    return Html::a(
                        'Добавить название',
                        ['important_events/names/edit', 'code' => $data['event']],
                        ['target' => '_blank']
                    );
                },
                'width' => '140px'
            ],
        ];
    }

    /**
     * @return ArrayDataProvider
     */
    public function getResult()
    {
        $result =
            (new Query)
                ->select('ie.event')
                ->from([
                    'ie' => new Expression('(SELECT DISTINCT(event) FROM ' . ImportantEvents::tableName() . ')')
                ])
                ->leftJoin(
                    ['ien' => ImportantEventsNames::tableName()],
                    'ien.code = ie.event'
                )
                ->where(['IS', 'ien.id', null])
                ->all();

        return new ArrayDataProvider([
            'allModels' => $result,
        ]);
    }

}