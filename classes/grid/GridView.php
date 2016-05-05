<?php
namespace app\classes\grid;

use app\classes\Html;
use Yii;
use yii\base\Widget;

class GridView extends \kartik\grid\GridView
{

    const DEFAULT_HEADER_CLASS = \kartik\grid\GridView::TYPE_INFO; // голубой фон th

    const FLOAT_HEADER_TOP = 85; // px. @todo менять динамически в заивимости от выбрать клиент или нет. По аналогии с позиционированием "floatTableClass":"kv-table-float","floatContainerClass":"kv-thead-float"

    /**
     * @var boolean whether the grid table will highlight row on `hover`.
     */
    public $hover = true; // При наведении мышкой на строку выделять ее

    /**
     * @var array the HTML attributes for the table header row.
     */
    public $headerRowOptions = [
        'class' => self::DEFAULT_HEADER_CLASS,
    ];
    /**
     * @var array the panel settings
     */
    public $panel = [
        'type' => '', //  шапка без фона
    ];

    // заголовок всегда отображать при скроллинге
    public $floatHeader = true;
    public $floatHeaderOptions = [
        'top' => self::FLOAT_HEADER_TOP, // высота шапки дизайна
    ];

    /**
     * @var string the template for rendering the panel heading.
     */
    public $panelHeadingTemplate = <<< HTML
    <div class="pull-right">
        {floatThead}
        {export}
    </div>
    <div class="pull-left">
        {summary}
    </div>
    <h3 class="panel-title">
        {heading}
    </h3>
    <div class="clearfix"></div>
HTML;

    /**
     * @var array|string the toolbar content configuration. Can be setup as a string or an array.
     */
    public $toolbar = [];

    public $filterSelector = '.beforeHeaderFilters input, .beforeHeaderFilters select';

    /**
     * Сгенерировать HTML до/после header/footer
     * В отличии от базового генерируются фильтры вне колонок. Можно было бы сделать это вне грида, но проще использовать его готовые column-классы для фильтров
     *
     * @param array|string $data the table rows configuration
     *
     * @return string
     */
    protected function generateRows($data)
    {
        if (!isset($data['columns'])) {
            return parent::generateRows($data);
        }

        $filters = [];
        foreach ($data['columns'] as $filterColumn) {

            $filterOptions = isset($filterColumn['filterOptions']) ? $filterColumn['filterOptions'] : [];
            !isset($filterOptions['class']) && $filterOptions['class'] = '';
            strpos($filterOptions['class'], 'col-sm-') === false && $filterOptions['class'] .= ' col-sm-3'; // если класс ширины не указан, указать его
            $filterColumn['grid'] = $this;

            /** @var \app\classes\grid\column\DataColumn $column */
            $column = Yii::createObject($filterColumn);
            if (is_string($column->filter)) {
                $row = $column->filter;
            } else {
                /** @var Widget $widgetName */
                $widgetName = $column->filterType;
                $row = $widgetName::widget([
                    'model' => $this->filterModel,
                    'attribute' => $filterColumn['attribute'],
                    'data' => $column->filter,
                ]);
            }

            $label = isset($filterColumn['label']) ? $filterColumn['label'] : $this->filterModel->getAttributeLabel($filterColumn['attribute']);

            $filters[] = Html::tag(
                'div',
                Html::tag('label', $label) . PHP_EOL .
                Html::tag('div', $row),
                $filterOptions
            );
        }

        // объединить в div class=row
        $rows = '';
        $chunkedFilters = array_chunk($filters, 4);
        foreach ($chunkedFilters as $chunkedFilter) {
            $rows .= Html::tag(
                'div',
                implode(PHP_EOL, $chunkedFilter),
                ['class' => 'row']
            );
        }

        $rows = Html::tag(
            'div',
            $rows,
            ['class' => 'beforeHeaderFilters']
        );

        // чтобы был валидный html, надо раскомемнтировать, но тогда при скроллинге с фильтрами вся шапка занимает очень много места
        /*
        $rows = Html::tag(
            'tr',
            Html::tag(
                'td',
                $rows,
                ['colspan' => count($this->columns)]
            )
        );
        */

        return $rows;
    }

    /**
     * Initalize grid layout
     */
    protected function initLayout()
    {
        parent::initLayout();
        $this->layout = strtr($this->layout, [
            '{floatThead}' => $this->renderFloatTheadButton(),
        ]);
    }

    /**
     * Сгенерировать кнопку для {floatThead}
     * @return string
     */
    protected function renderFloatTheadButton()
    {
        $top = self::FLOAT_HEADER_TOP;
        $view = $this->getView();
        $view->registerJs('$(function () {
            $("#pushpinTableHeader").on("click", function() {
                var $this = $(this);
                if ($this.hasClass("active")) {
                    $this.removeClass("active");
                    $(".kv-grid-table").floatThead("destroy");
                } else {
                    $this.addClass("active");
                    $(".kv-grid-table").floatThead({ top:' . $top . ', floatTableClass:"kv-table-float", floatContainerClass:"kv-thead-float" });
                }
            });
        });');

        return Html::button('', [
            'class' => 'btn btn-default glyphicon glyphicon-pushpin active pointer',
            'title' => Yii::t('common', 'Pushpin table header'),
            'id' => 'pushpinTableHeader',
        ]);
    }
}