<?php
namespace app\classes\grid\account;

use Yii;


interface AccountGridInterface
{
    function getBusiness();

    function getBusinessProcessId();

    /**
     * @return AccountGridFolder[]
     */
    function getFolders();
}