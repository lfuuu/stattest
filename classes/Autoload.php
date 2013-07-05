<?php

spl_autoload_register('stat_autoload');

include PATH_TO_ROOT . 'libs/activerecord/ActiveRecord.php';

function stat_autoload($class_name)
{
    $filePath = PATH_TO_ROOT . "classes/" . $class_name . ".php";
    if(file_exists($filePath))
    {
        require $filePath;
        return true;
    }

    $filePath = PATH_TO_ROOT . "classes/" . strtolower($class_name) . ".php";
    if(file_exists($filePath))
    {
        require $filePath;
        return true;
    }

    return false;
}
