<?php

namespace app\classes\grid\column\universal;

use app\classes\Html;
use app\widgets\TagsSelect2\TagsSelect2;

class TagsColumn extends \kartik\grid\DataColumn
{

    public $attribute = 'tags_filter';
    public $filterType = '\app\widgets\multiselect\MultiSelect';
    public $filterInputOptions = [];

    public $isEditable = false;

    /**
     * @param array $config
     */
    public function __construct($config = [])
    {
        $this->filterWidgetOptions['data'] = $config['grid']->filterModel->getTagList();
        $this->filterWidgetOptions['nonSelectedText'] = '-- Метки --';
        $this->filterWidgetOptions['clientOptions']['buttonWidth'] = '100%';
        $this->filterWidgetOptions['clientOptions']['enableCollapsibleOptGroups'] = true;
        $this->filterWidgetOptions['clientOptions']['enableClickableOptGroups'] = true;
        $this->filterWidgetOptions['clientOptions']['enableFiltering'] = true;

        $this->filterInputOptions['multiple'] = 'multiple';

        parent::__construct($config);
    }

    /**
     * @param mixed $model
     * @param mixed $key
     * @param int $index
     * @return string
     */
    protected function renderDataCellContent($model, $key, $index)
    {
        $beginTag = Html::beginTag('span', ['class' => 'label label-info tags-label']);
        $endTag = Html::endTag('span');
        $editableBlock = '';

        if ($this->isEditable) {
            $tagsList = TagsSelect2::widget([
                'model' => $model,
                'attribute' => 'tags',
                'label' => null,
                'pluginOptions' => [
                    'placeholder' => 'Метка',
                ]
            ]);

            $editableBtn = Html::tag(
                'div',
                Html::tag('i', '', ['class' => 'glyphicon glyphicon-pencil']),
                ['class' => 'tags-inline-edit',]
            );
            $disableEditableBtn = Html::tag(
                'div',
                Html::tag('i', '', ['class' => 'glyphicon glyphicon-save']),
                ['class' => 'tags-inline-edit disable-edit',]
            );

            $editableBlock = Html::beginTag('div', ['class' => 'tags-resource-list']) . $tagsList . Html::endTag('div') .
                $editableBtn .
                $disableEditableBtn;
        }

        return $beginTag . implode($endTag . $beginTag, $model->tags) . $endTag . $editableBlock;
    }

}