<?php

namespace app\classes\grid\column\universal;

use app\classes\Html;

class TagsColumn extends \kartik\grid\DataColumn
{

    public
        $attribute = 'tags_filter',
        $filterType = '\app\widgets\multiselect\MultiSelect',
        $filterInputOptions = [];

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
        $beginTag = Html::beginTag('span', ['class' =>'label label-info', 'style' => 'margin: 2px;']);
        $endTag = Html::endTag('span');

        return $beginTag . implode($endTag . $beginTag, $model->tags) . $endTag;
    }

}