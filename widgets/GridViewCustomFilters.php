<?php

namespace app\widgets;

use Yii;
use app\classes\Html;
use yii\grid\Column;
use yii\helpers\ArrayHelper;
use kartik\widgets\ActiveForm;

class GridViewCustomFilters extends \kartik\grid\GridView
{

    public function renderTableHeader()
    {
        $cells = [];
        foreach ($this->columns as $index => $column) {
            /* @var $column Column */
            if ($this->resizableColumns && $this->persistResize) {
                $column->headerOptions['data-resizable-column-id'] = "kv-col-{$index}";
            }
            $cells[] = $column->renderHeaderCell();
        }
        $content = Html::tag('tr', implode('', $cells), $this->headerRowOptions);

        return "<thead>\n" .
        $this->generateRows($this->beforeHeader) . "\n" .
        $content . "\n" .
        $this->generateRows($this->afterHeader) . "\n" .
        "</thead>";
    }

    /**
     * Sets the grid layout based on the template and panel settings
     */
    protected function renderPanel()
    {
        if (!$this->bootstrap || !is_array($this->panel) || empty($this->panel)) {
            return;
        }
        $type = 'panel-' . ArrayHelper::getValue($this->panel, 'type', 'default');
        $heading = ArrayHelper::getValue($this->panel, 'heading', '');
        $footer = ArrayHelper::getValue($this->panel, 'footer', '');
        $after = ArrayHelper::getValue($this->panel, 'after', '');
        $headingOptions = ArrayHelper::getValue($this->panel, 'headingOptions', []);
        $footerOptions = ArrayHelper::getValue($this->panel, 'footerOptions', []);
        $afterOptions = ArrayHelper::getValue($this->panel, 'afterOptions', []);
        $panelHeading = '';
        $panelAfter = '';
        $panelFooter = '';

        if ($heading !== false) {
            Html::addCssClass($headingOptions, 'panel-heading');
            $content = strtr($this->panelHeadingTemplate, ['{heading}' => $heading]);
            $panelHeading = Html::tag('div', $content, $headingOptions);
        }
        if ($footer !== false) {
            Html::addCssClass($footerOptions, 'panel-footer');
            $content = strtr($this->panelFooterTemplate, ['{footer}' => $footer]);
            $panelFooter = Html::tag('div', $content, $footerOptions);
        }
        if ($after !== false) {
            Html::addCssClass($afterOptions, 'kv-panel-after');
            $content = strtr($this->panelAfterTemplate, ['{after}' => $after]);
            $panelAfter = Html::tag('div', $content, $afterOptions);
        }

        $this->layout = strtr(
            $this->panelTemplate,
            [
                '{panelHeading}' => $panelHeading,
                '{type}' => $type,
                '{panelFooter}' => $panelFooter,
                '{panelBefore}' => $this->prepareFilterContainer(),
                '{panelAfter}' => $panelAfter
            ]
        );
    }

    protected function prepareFilterContainer()
    {
        ob_start();

        ActiveForm::begin([
            'type' => ActiveForm::TYPE_VERTICAL,
            'method' => 'get'
        ]);

            echo Html::beginTag('div', [
                'class' => 'col-xs-12',
                'style' => 'border-bottom: 1px solid #D0D0D0; padding-bottom: 10px; padding-top: 10px; background: #F0F0F0; padding-left: 0px;'
            ]);
                echo Html::tag('label', 'Фильтр', ['style' => 'padding-left: 15px;']);
                echo Html::beginTag('fieldset');

                    echo $this->renderFilters();

                echo Html::endTag('fieldset');
            echo Html::endTag('div');

            echo Html::tag('div', '', ['style' => 'clear: both;']);

        ActiveForm::end();

        $view = $this->getView();
        $view->registerJs('$(".filter-' . $this->filterModel->formName() . ' input, select").on("blur change", function(e) { return false; })');

        $output = ob_get_contents();
        ob_end_clean();

        return $output;
    }

    public function renderFilters()
    {
        $columnsCount = count($this->columns);

        if ($this->filterModel !== null) {
            $cells = [];
            /** @var Column $column */
            foreach ($this->columns as $column) {
                $content = preg_replace('#<\/*td>#i', '', $column->renderFilterCell());

                if (empty($content) || $content == $this->emptyCell) {
                    $columnsCount--;
                    continue;
                }

                $cells[] = Html::tag(
                    'div',
                    $content,
                    [
                        'class' => 'filter-' . $this->filterModel->formName() . ' col-xs-' . floor(12 / $columnsCount),
                    ] + $this->filterRowOptions
                );
            }

            return
                implode('', $cells) .
                Html::tag('div', Html::submitButton('Применить', ['class' => 'btn btn-success']));
        }
        else {
            return '';
        }
    }

}