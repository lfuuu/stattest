<?php
namespace app\classes;

use Yii;


class Request extends \yii\web\Request
{
    public function getPathInfo()
    {
        if (isset($_GET['module']) || isset($_POST['module'])) {
            if (strpos($_SERVER['REQUEST_URI'], '/index.php') === 0) {
                return 'compatibility/index';
            } elseif (strpos($_SERVER['REQUEST_URI'], '/index_lite.php') === 0) {
                return 'compatibility/lite';
            } else {
                return 'compatibility/index';
            }
        }
        return parent::getPathInfo();
    }
}