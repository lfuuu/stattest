<?php

function access($action, $permission)
{
    return Yii::$app->user->can($action . '.' . $permission);
}
