<?php

namespace app\classes\grid;

use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;


class GridView extends \kartik\grid\GridView
{
    public $pjax = true;
    public $bordered = true;
    public $striped = true;
    public $condensed = true;
    public $hover = true;

    public function __construct($config = [])
    {
        $this->export = [
            'options' => ['class' => 'btn btn-default btn-sm'],
        ];

        $this->exportConfig = [
            GridView::EXCEL => true,
            GridView::CSV => true,
            GridView::HTML => true,
        ];

        $this->toggleDataOptions =       [
            'all' => [
                'icon' => 'resize-full',
                'label' => 'All',
                'class' => 'btn btn-default btn-sm',
                'title' => 'Show all data'
            ],
            'page' => [
                'icon' => 'resize-small',
                'label' => 'Page',
                'class' => 'btn btn-default btn-sm',
                'title' => 'Show first page data'
            ],
        ];


        parent::__construct($config);
    }

}