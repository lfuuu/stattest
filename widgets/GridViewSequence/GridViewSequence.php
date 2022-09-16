<?php

namespace app\widgets\GridViewSequence;

use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\Json;
use app\classes\grid\GridView;

class GridViewSequence extends GridView
{

    /**
     * @var string
     */
    public $sortableAction = '';

    /**
     * @inheritdoc
     */
    public function init()
    {
        array_unshift($this->columns, ['class' => SequenceColumn::class]);

        $modelName = $this->dataProvider->query->modelClass;
        $modelClass = new $modelName;
        if (!$modelClass->hasMethod('gridSort')) {
            throw new InvalidConfigException('Model "' . $modelName . '" must have method "gridSort"');
        }

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $actionRequest = Yii::$app->request->post('grid-sort');

        if (is_array($actionRequest)) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            Yii::$app->controller->layout = false;

            while (ob_get_level() > 0) {
                ob_end_clean();
            }

            $modelName = $this->dataProvider->query->modelClass;
            $modelClass = new $modelName;
            if (!$modelClass->hasMethod('gridSort')) {
                throw new InvalidConfigException('Model "' . $modelName . '" must have method "gridSort"');
            }

            $result = $modelClass->gridSort($actionRequest['moved_element_id'], $actionRequest['next_element_id']);

            if ($result === true) {
                \Yii::$app->response->content = Json::encode(['result' => 'success']);
            } else {
                \Yii::$app->response->content = Json::encode(['result' => $result]);
            }

            Yii::$app->end();
        }

        parent::run();
        $this->registerPlugin();
    }

    /**
     * @inheritdoc
     */
    protected function registerPlugin()
    {
        $view = $this->getView();

        $view->registerJs("jQuery('#{$this->options['id']}').SequenceGridView('{$this->sortableAction}');");
        GridViewSequenceAsset::register($view);
    }

}