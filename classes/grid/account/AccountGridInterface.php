<?php
namespace app\classes\grid\account;

use Yii;


interface AccountGridInterface
{
    function getContractType();

    function getBusinessProcessId();

    /**
     * @return AccountGridFolder[]
     */
    function getFolders();
}