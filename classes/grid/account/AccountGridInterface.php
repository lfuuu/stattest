<?php
namespace app\classes\grid\account;

use Yii;


interface AccountGridInterface
{

    /**
     * @return int
     */
    public function getBusiness();

    /**
     * @return int
     */
    public function getBusinessProcessId();

    /**
     * @return AccountGridFolder[]
     */
    public function getFolders();
}