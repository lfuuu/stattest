<?php

namespace app\widgets\MultipleInput;

use unclead\multipleinput\renderers\TableRenderer as BaseTableRenderer;
use app\classes\Html;

class TableRenderer extends BaseTableRenderer
{

    /**
     * Перечисление ширины столбцов таблицы.
     * Сделано исключительно из-за некорректного поведения таблицы при работе в Select2 в режиме multiple.
     * ```php
     * colgroup => [
     *    '30%',
     *    '70%',
     * ],
     * ```
     *
     * @var array
     */
    public $colgroup = [];

    /**
     * Renders the header.
     *
     * @return string
     */
    public function renderHeader()
    {
        return $this->_renderColgroup() . parent::renderHeader();
    }

    /**
     * @return string
     */
    private function _renderColgroup()
    {
        if (!count($this->colgroup)) {
            return '';
        }

        $cols = [];

        foreach ($this->colgroup as $colWidth) {
            $cols[] = Html::tag('col', '', ['width' => $colWidth]);
        }

        return Html::tag('colgroup', implode(PHP_EOL, $cols));
    }

}