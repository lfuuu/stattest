<?php

spl_autoload_register('stat_autoload');

include PATH_TO_ROOT . 'libs/activerecord/ActiveRecord.php';

function stat_autoload($class_name)
{
    if (substr($class_name, -9) === 'Exception') {
        if(file_exists($filePath = PATH_TO_ROOT . "exceptions/" . $class_name . ".php")) {
            require $filePath;
            return true;
        }
    }

    if(file_exists($filePath = PATH_TO_ROOT . "classes/" . $class_name . ".php")) {
        require $filePath;
        return true;
    }

    if(file_exists($filePath = PATH_TO_ROOT . "classes/" . strtolower($class_name) . ".php")) {
        require $filePath;
        return true;
    }

    if(file_exists($filePath = PATH_TO_ROOT . "include/" . strtolower($class_name) . ".php")) {
        require $filePath;
        return true;
    }

    if (strpos($class_name, "m_") !== false && file_exists($filePath = PATH_TO_ROOT . "modules/" . substr(strtolower($class_name), 2) . "/module.php")) {
        require $filePath;
        return true;
    }


    return false;
}
