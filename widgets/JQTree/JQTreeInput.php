<?php

namespace app\widgets\JQTree;

use app\classes\Assert;
use yii\base\InvalidConfigException;
use yii\db\ActiveRecord;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\widgets\InputWidget;
use yii\web\JsExpression;

/*
 * @see http://mbraak.github.io/jqTree/
 */
class JQTreeInput extends InputWidget
{

    public
        $model,
        $attribute,
        $data = [],
        $clientOptions = [],
        $defaultOptions = [
            'dragAndDrop' => false,
            'selectable' => true,
            'autoOpen' => false,
            'saveState' => false,
            'closedIcon' => '&#x2795;',
            'openedIcon' => '&#x2796;',
        ],
        $htmlOptions = [],
        $defaultHtmlOptions = [
           'class' => 'jqtree-input',
        ];

    private
        $id,
        $valueId,
        $originalFieldName,
        $valueFieldName,
        $valueSuffix = '-jqtree-value';

    public function init()
    {
        parent::init();

        if ($this->data instanceof ActiveRecord) {
            if ($this->data->hasMethod('populateTreeForWidget')) {
                $this->data = $this->data->populateTreeForWidget($data = null, $withDocuments = false);
            }
            else {
                Assert::methodExists($this->data, 'initTreeView');
            }
        }

        if (!$this->hasModel()) {
            throw new  InvalidConfigException('"JQTree::$model" is not instance of Model.');
        }

        if (empty($this->data)) {
            throw new  InvalidConfigException('"JQTree::$data" attribute cannot be blank or an empty array.');
        }

        $this->clientOptions = $this->defaultOptions;
        $this->clientOptions['data'] = $this->data;
        $this->htmlOptions = array_merge($this->htmlOptions, $this->defaultHtmlOptions);

        $this->originalFieldName = Html::getInputName($this->model, $this->attribute);
        $this->valueFieldName = $this->attribute . $this->valueSuffix;
    }

    public function run()
    {
        $this->id = $this->getId();
        $this->valueId = Html::getAttributeValue($this->model, $this->attribute);

        if (!isset($this->htmlOptions['id'])) {
            $this->htmlOptions['id'] = $this->id;
        }
        else {
            $this->id = $this->htmlOptions['id'];
        }

        echo Html::input('hidden', $this->originalFieldName, $this->value, ['class' => 'form-control']);
        echo Html::input('text', $this->valueFieldName, '', ['class' => 'form-control']);
        echo Html::tag('div', '', $this->htmlOptions);

        $this->registerPlugin();
    }

    protected function registerPlugin()
    {
        $view = $this->getView();

        JQTreeAsset::register($view);

        $options = $this->clientOptions;
        $options['onCreateLi'] = new JsExpression('
            function(node, $li) {
                if (node.icon) {
                    $li.find(".jqtree-element span").prepend("<span class=\"" + node.icon + "\"></span>");
                }
            }
        ');

        $options = $options === [] ? '{}' : JSON::encode($options);

        $js = new JsExpression('
            var
                $jqTree = $("#' . $this->id . '").tree(' . $options . '),
                $jqTreeContainer = $("div.' . $this->defaultHtmlOptions['class'] . '"),
                $originalField = $("input[name=\"' . $this->originalFieldName . '\"]"),
                $valueField = $("input[name=\"' . $this->valueFieldName . '\"]");

            ;

            $valueField
                .on("focus", function() {
                    $jqTreeContainer.show();
                    $(this).trigger("blur");
                })

            $(document).on("click", function(e) {
                if ($(e.target).attr("name") != $valueField.attr("name")) {
                    $jqTreeContainer.hide();
                }
            });

            $("#' . $this->id .'").on("tree.select", function(event) {
                if (event.node) {
                    var node = event.node;
                    $originalField.val(node.id);
                    $valueField.val(node.name);
                }
                else {
                    $originalField.val("");
                    $valueField.val("");
                }
            });
        ');

        if ((int)$this->valueId) {
            $js .= new JsExpression('
                $jqTree.tree("selectNode", $jqTree.tree("getNodeById", ' . $this->valueId . '));
            ');
        }

        $view->registerJs($js);
    }

}