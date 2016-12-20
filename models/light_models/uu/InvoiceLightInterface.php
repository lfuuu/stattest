<?php

namespace app\models\light_models\uu;

interface InvoiceLightInterface
{

    /**
     * @return string
     */
    public static function getKey();

    /**
     * @return string
     */
    public static function getTitle();

    /**
     * @return array
     */
    public static function attributeLabels();

}