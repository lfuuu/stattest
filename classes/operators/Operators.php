<?php

namespace app\classes\operators;

use Yii;
use yii\base\Object;
use app\dao\reports\ReportExtendsOperatorsDao;
use app\classes\excel\OnlimeOperatorToExcel;

abstract class Operators extends Object
{

    const STORE_ID = '8e5c7b22-8385-11df-9af5-001517456eb1';

    protected static
        $requestProducts = [],
        $requestModes = [],
        $reportFields = [],
        $availableRequestStatuses = [];

    public function getOperator()
    {
        return static::OPERATOR;
    }

    public function getOperatorClient()
    {
        return static::OPERATOR_CLIENT;
    }

    public function getReport()
    {
        return ReportExtendsOperatorsDao::me()->setOperator($this);
    }

    public function getStoreId()
    {
        return static::STORE_ID;
    }

    public function getRequestModes()
    {
        return static::$requestModes;
    }

    public function getProducts()
    {
        return static::$requestProducts;
    }

    public function getProductById($id)
    {
        foreach ($this->products as $product) {
            if ($id == $product['id'])
                return $product;
        }
    }

    public function getAvailableRequestStatuses()
    {
        return static::$availableRequestStatuses;
    }

    public function generateExcel($report)
    {
        $excel = new OnlimeOperatorToExcel;
        $excel->setColumnShift($this->reportColumnsShiftFrom);
        $excel->openFile(Yii::getAlias('@app/templates/' . static::$reportTemplate . '.xls'));
        $excel->prepare(static::$reportFields, $this->products, $report);
        $excel->download($this->operatorClient);
    }

}