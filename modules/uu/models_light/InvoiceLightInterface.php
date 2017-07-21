<?php

namespace app\modules\uu\models_light;

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