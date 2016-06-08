<?php

namespace app\widgets\JQTree;

use app\classes\Assert;
use yii\base\InvalidConfigException;
use yii\db\ActiveRecord;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\bootstrap\Widget;
use yii\web\JsExpression;

/*
 * @see http://mbraak.github.io/jqTree/
 */
class JQTree extends Widget
{

    public
        $data = [],
        $clientOptions = [],
        $defaultClientOptions = [
            'dragAndDrop' => false,
            'selectable' => false,
            'autoOpen' => false,
            'saveState' => true,
            'closedIcon' => '&#x2795;',
            'openedIcon' => '&#x2796;',
        ],
        $htmlOptions = [];

    private $id;

    public function init()
    {
        parent::init();

        if ($this->data instanceof ActiveRecord) {
            if ($this->data->hasMethod('populateTreeForWidget')) {
                $this->data = $this->data->populateTreeForWidget();
            } else {
                Assert::methodExists($this->data, 'initTreeView');
            }
        }

        if (empty($this->data)) {
            throw new  InvalidConfigException('"JQTree::$data" attribute cannot be blank or an empty array.');
        }

        $this->clientOptions = array_merge($this->clientOptions, $this->defaultClientOptions);
        $this->clientOptions['data'] = $this->data;
    }

    public function run()
    {
        $this->id = $this->getId();

        if (!isset($this->htmlOptions['id'])) {
            $this->htmlOptions['id'] = $this->id;
        }
        else {
            $this->id = $this->htmlOptions['id'];
        }

        echo Html::tag('div', '', $this->htmlOptions) . PHP_EOL;

        $this->registerPlugin();
    }

    protected function registerPlugin()
    {
        $view = $this->getView();

        JQTreeAsset::register($view);

        $options = $this->clientOptions;
        $options['onCreateLi'] = new JsExpression('
            function(node, $li) {
                if (node.href) {
                    $li.find(".jqtree-element span.jqtree-title").wrap($("<a />").attr("href", node.href));
                }
                if (node.icon) {
                    var icon = node.icon,
                        iconTitle = node.iconTitle;
                    $li.find(".jqtree-element span").prepend($("<span />").addClass(icon).attr("title", iconTitle));
                }
            }
        ');

        $options = $options === [] ? '{}' : JSON::encode($options);

        $js = '$("#' . $this->id . '").tree(' . $options . ');';

        $view->registerJs($js);
    }

}