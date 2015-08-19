<?php
namespace app\classes\grid\account;

use Yii;


interface AccountGridInterface
{
    function getContractSubdivision();

    function getBusinessProcessId();

    /**
     * @return AccountGridFolder[]
     */
    function getFolders();
}