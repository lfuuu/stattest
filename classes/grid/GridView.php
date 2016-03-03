<?php
namespace app\classes\grid;

class GridView extends \kartik\grid\GridView
{

    /**
     * @var boolean whether the grid table will highlight row on `hover`.
     */
    public $hover = true; // При наведении мышкой на строку выделять ее

    /**
     * @var array the HTML attributes for the table header row.
     */
    public $headerRowOptions = [
        'class' => \kartik\grid\GridView::TYPE_INFO, // голубой фон th
    ];
    /**
     * @var array the panel settings
     */
    public $panel = [
        'type' => '', //  шапка без фона
    ];

    /**
     * @var string the template for rendering the panel heading.
     */
    public $panelHeadingTemplate = <<< HTML
    <div class="pull-right">
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

}