<?php

class GoodUnit extends ActiveRecord\Model
{
    const CODE_MONTH = 362; // для счета-фактуры

    static $table_name = "g_unit";
    static $private_key = 'id';
}
