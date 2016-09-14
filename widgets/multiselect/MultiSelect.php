<?php

namespace app\widgets\multiselect;

use Yii;
use yii\helpers\Json;
use yii\widgets\InputWidget;
use yii\base\InvalidConfigException;
use app\classes\Html;

/**
 * MultiSelect renders a [David Stutz Multiselect widget](http://davidstutz.github.io/bootstrap-multiselect/)
 *
 * @see http://davidstutz.github.io/bootstrap-multiselect/
 */
class MultiSelect extends InputWidget
{

    public
        $data = [],
        $clientOptions = [],

        $nonSelectedText = 'Ничего не выбрано',
        $allSelectedText = 'Все выбрано',
        $selectAllText = 'Выбрать все';

    /**
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();

        $this->clientOptions = array_merge(
            $this->clientOptions,
            [
                'includeSelectAllOption' => true,
                'numberDisplayed' => 2,
                'nonSelectedText' => $this->nonSelectedText,
                'allSelectedText' => $this->allSelectedText,
                'selectAllText' => $this->selectAllText,
                'nSelectedText' => 'Выбрано',
                'filterPlaceholder' => 'Поиск'
            ]
        );
    }

    public function run()
    {
        if ($this->hasModel()) {
            echo Html::activeDropDownList($this->model, $this->attribute, $this->data, $this->options);
        } else {
            echo Html::dropDownList($this->name, $this->value, $this->data, $this->options);
        }

        $this->registerPlugin();
    }

    protected function registerPlugin()
    {
        $view = $this->getView();

        MultiSelectAsset::register($view);

        $id = $this->options['id'];

        $options =
            ($this->clientOptions !== false && !empty($this->clientOptions))
                ? Json::encode($this->clientOptions)
                : '';

        $js = "jQuery('#" . $id . "').multiselect(" . $options . ");";
        $view->registerJs($js);
        $view->registerCss('.multiselect-container { position: relative; }');
    }
}