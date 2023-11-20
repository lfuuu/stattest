<?php

namespace app\classes\grid;

use app\classes\Html;
use Yii;
use yii\base\Widget;
use yii\helpers\Json;
use yii\web\JsExpression;

class GridView extends \kartik\grid\GridView
{
    use FilterQueryTrait;

    const DEFAULT_HEADER_CLASS = \kartik\grid\GridView::TYPE_INFO; // голубой фон th

    const FLOAT_HEADER_TOP = 85; // px. @todo менять динамически в зависимости от выбрать клиент или нет. По аналогии с позиционированием "floatTableClass":"kv-table-float","floatContainerClass":"kv-thead-float"

    // кол-во фильтров перед гридом, начиная с которого значения их скрывать
    const MIN_BEFORE_HEADER_FILTER_FOR_HIDE = 5; // то есть больше 1 строки

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
        'type' => '', // шапка без фона
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
        {extraButtons}
        {filterButton}
        {floatThead}
        {toggleData}
        {export}
        {filterQuery}
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
     * Юзерские кнопки
     *
     * @var string
     */
    public $extraButtons = '';

    /**
     * Показывать ли кнопки фильтрации
     *
     * @var bool
     */
    public $isFilterButton = true;

    /**
     * Показывать ли кнопки сохранения/загрузки фильтров
     *
     * @var bool
     */
    public $isFilterQuery = true;

    /**
     * @var array|string the toolbar content configuration. Can be setup as a string or an array.
     */
    public $toolbar = [];

    public $filterSelector = '.beforeHeaderFilters input, .beforeHeaderFilters select';

    public $export = [
        'showConfirmAlert' => false, // boolean, whether to show a confirmation alert dialog before download. This confirmation dialog will notify user about the type of exported file for download and to disable popup blockers.
        'target' => GridView::TARGET_SELF, // no window is popped up in this case, but download is submitted on same page.
    ];

    /**
     * @var string the name of the parameter used to specify the size of the page.
     * This will be used as the input name of the dropdown list with page size options.
     */
    public $pageSizeParam = 'per-page';

    /**
     * @var string the name of the parameter used to specify the size of the page.
     * This will be used as the cookie name
     */
    public $pageSizeCookie = 'GridViewPageSize';

    /**
     * @var [] the list of page sizes
     */
    public $pageSizes = [
        10 => 'по 10 на стр.',
        20 => 'по 20 на стр.',
        50 => 'по 50 на стр.',
        100 => 'по 100 на стр.',
        500 => 'по 500 на стр.',
        1500 => 'по 1000 на стр.',
        2000 => 'по 2000 на стр.',
        5000 => 'по 5000 на стр.',
        -1 => '- Все -',
    ];

    /**
     * @var null|string
     */
    public $exportWidget = null;

    public $showTableBody = true;

    /** @var null|bool Фильтры в заголовке true/false - всегда показывать/скрывать, null - авто (если много) */
    public $isHideFilters = null;

    /**
     * Переопределенный метод рендеринга таблицы.
     * Дополнительно проверяет нужно ли ее рендерить,
     * если нужно, то выполнить родительский метод
     *
     * @return string
     */
    public function renderTableBody()
    {
        return $this->showTableBody ? parent::renderTableBody() : '';
    }

    /**
     * Делает возможным pjax-перезагрузку таблицы без перезагрузки фильтров,
     * если они размещены перед таблицей.
     * Костыль и требует более фундаментального решения.
     *
     * TODO Refactor this method
     *
     * @param array $config
     * @throws \Exception
     */
    public static function separateWidget(array $config = [])
    {
        $config1 = $config2 = $config;
        unset(
            $config1['columns'],
            $config1['exportWidget'],
            $config2['beforeHeader'],
            $config2['panelHeadingTemplate'],
            $config2['extraButtons']
        );
        $config1['dataProvider'] = new \yii\data\ArrayDataProvider(
            [
                'allModels' => [],
            ]
        );

        if (!isset(Yii::$app->request->get()['_pjax'])) {
            echo self::widget(
                [
                    'showTableBody' => false,
                    'showFooter' => false,
                    'panelTemplate' => '
                <div class="{prefix}{type}">
                    {panelHeading}
                    {panelBefore}
                    {items}
                </div>',
                ] + $config1
            );
        }

        echo self::widget(
            [
                'pjax' => true
            ] + $config2
        );
    }

    /**
     * Сгенерировать HTML до/после header/footer
     * В отличии от базового генерируются фильтры вне колонок. Можно было бы сделать это вне грида, но проще использовать его готовые column-классы для фильтров
     *
     * @param array|string $data the table rows configuration
     *
     * @return string
     * @throws \Exception
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
            strpos($filterOptions['class'],
                'col-sm-') === false && $filterOptions['class'] .= ' col-sm-3'; // если класс ширины не указан, указать его
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
                    'options' => $column->filterInputOptions
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

        $this->isHideFilters = $this->isHideFilters === null ? count($data['columns']) >= self::MIN_BEFORE_HEADER_FILTER_FOR_HIDE : $this->isHideFilters;

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
            ['class' => 'beforeHeaderFilters' . ($this->isHideFilters ? ' collapse' : '')]
        );

        if ($this->isHideFilters) {
            $rows = $this->render('//layouts/_toggleButton', ['divSelector' => '.beforeHeaderFilters', 'title' => 'Доп. фильтры']) . $rows;
        }

        /*
            // чтобы был валидный html, надо раскомментировать, но тогда при скроллинге с фильтрами вся шапка занимает очень много места
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
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->_toggleDataKey = '_tog' . hash('crc32', Yii::$app->user->id . $this->_toggleDataKey);

        if (isset($_COOKIE[$this->pageSizeCookie])) {
            $pageSizeValue = Json::decode($_COOKIE[$this->pageSizeCookie]);

            if (isset($pageSizeValue[$this->_toggleDataKey])) {
                switch ($pageSizeValue) {
                    case -1:
                        $this->dataProvider->pagination = false;
                        break;
                    default:
                        $this->dataProvider->getPagination()->pageSize = (int)$pageSizeValue[$this->_toggleDataKey];
                        break;
                }
            }
        }
    }

    /**
     * Initalize grid layout
     *
     * @throws \yii\base\InvalidParamException
     */
    protected function initLayout()
    {
        parent::initLayout();
        $this->layout = strtr($this->layout,
            [
                '{floatThead}' => $this->renderFloatTheadButton(),
                '{extraButtons}' => $this->extraButtons,
                '{filterButton}' => $this->isFilterButton ?
                    $this->render('//layouts/_buttonFilter') :
                    '',
                '{filterQuery}' => $this->isFilterQuery ?
                    $this->render('//layouts/_filterQueryForm', [
                        'filterModel' => $this->filterModel,
                        'columns' => $this->columns, // @todo еще надо $this->beforeHeader['columns'], но это не DataColumn[]
                    ]) :
                    '',
            ]
        );
    }

    /**
     * Returns the options for the grid view JS widget.
     *
     * @return array the options
     */
    protected function getClientOptions()
    {
        $clientOptions = parent::getClientOptions();

        if ($this->isFilterButton) {
            $view = $this->getView();
            $view->registerJs('setTimeout(function () {
                // отменить onchange на фильтре
                $(document).off("change.yiiGridView", "' . $clientOptions['filterSelector'] . '");
                // эмулировать submit по кнопке фильтрации
                $(document).on("click", "#submitButtonFilter", function() {
                    var e = $.Event("keydown");
                    e.keyCode = 13; // enter
                    $("' . $clientOptions['filterSelector'] . '").first().trigger(e);
                });
            }, 300);');
        }

        return $clientOptions;
    }

    /**
     * Сгенерировать кнопку для {floatThead}
     *
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

            // fix additional filters bug
            $(".kv-thead-float").each(function(){
                var elStyle = $(this)[0].style;
                
                if (elStyle.removeAttribute) {
                    elStyle.removeAttribute("top");
                } else {
                    elStyle.removeProperty("top");
                }                
            });
        });');

        return Html::button('',
            [
                'class' => 'btn btn-default glyphicon glyphicon-pushpin active pointer',
                'title' => Yii::t('common', 'Pushpin table header'),
                'id' => 'pushpinTableHeader',
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function renderToggleData()
    {
        if (!$this->toggleData) {
            return '';
        }

        $view = $this->getView();
        $view->registerJs(
            new JsExpression(
                '$("body").on("change", "select[name=\"' . $this->pageSizeParam . '\"]", function() {
                    var data = Cookies.get("' . $this->pageSizeCookie . '");
                    data = data ? $.parseJSON(data) : {};
                    data["' . $this->_toggleDataKey . '"] = $(this).find("option:selected").val();
                    Cookies.set("' . $this->pageSizeCookie . '", data, { path: "/" });
                    $("#submitButtonFilter").click();
                });'
            )
        );

        if (!$this->dataProvider->getTotalCount()) {
            // не показывать кнопку "все", если нет данных
            return '';
        }

        $pagination = $this->dataProvider->getPagination();

        return
            Html::beginTag('div', ['class' => 'btn-group']) .
            Html::dropDownList(
                $this->pageSizeParam,
                ($pagination && $pagination->pageSize) ? $pagination->pageSize : -1, $this->pageSizes,
                [
                    'class' => 'form-control',
                    'style' => 'width:140px;'
                ]
            ) .
            Html::endTag('div');
    }

    /**
     * @inheritdoc
     */
    protected function initExport()
    {
        if (is_null($this->exportWidget)) {
            parent::initExport();
        }
    }

    /**
     * @inheritdoc
     */
    public function renderExport()
    {
        if (!$this->dataProvider->getTotalCount()) {
            // не показывать кнопку, если нет данных
            return '';
        }

        // Отображать кастомный export
        if (!is_null($this->exportWidget)) {
            return $this->exportWidget;
        }

        return parent::renderExport();
    }
}
