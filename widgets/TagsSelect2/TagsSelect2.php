<?php

namespace app\widgets\TagsSelect2;

use app\classes\Html;
use Yii;
use kartik\select2\Select2;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\web\JsExpression;

class TagsSelect2 extends Select2
{

    public
        $attribute = 'id',
        $actionURL = '/tags/apply',
        $listURL = '/tags/load-list',
        $label = 'Метки',
        $feature = '';

    /**
     * @throws InvalidConfigException
     */
    public function init()
    {
        if (!$this->hasModel()) {
            throw new InvalidConfigException('"TagsSelect2::$model" is not instance of Model.');
        }

        if (empty($this->attribute)) {
            throw new InvalidConfigException('"TagsSelect2::$attribute" attribute cannot be blank.');
        }

        echo Html::tag('label', $this->label, ['class' => 'control-label']);

        $this->size = parent::SMALL;
        $this->maintainOrder = true;

        $this->options['id'] = $this->getId($autoGenerate = true);
        $this->options = array_merge([
            'multiple' => true,
            'data-tags-resource' => $this->model->formName(),
            'data-tags-resource-id' => $this->model->getAttribute(reset($this->model->primaryKey())),
            'data-tags-feature' => $this->feature,
        ], $this->options);

        $this->pluginOptions = array_merge([
            'tags' => true,
            'tokenSeparators' => [',', ' '],
            'maximumInputLength' => 10,
            'ajax' => [
                'url' => $this->listURL . '?resource=' . $this->model->formName() . '&feature=' . $this->feature,
                'dataType' => 'json',
                'data' => new JsExpression('function(params) { return {q:params.term}; }'),
            ],
        ], $this->pluginOptions);

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function registerAssetBundle()
    {
        parent::registerAssetBundle();

        $view = $this->getView();
        TagsSelect2Asset::register($view);

        $script = new JsExpression('
            jQuery("select[data-tags-resource]")
                .tagsSelect2({
                    "url": "' . $this->actionURL . '"
                });
        ');

        $view->registerJs($script);
    }

}