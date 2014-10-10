<?php

class TroubleStage extends ActiveRecord\Model
{
    static $table_name = "tt_stages";
    static $primary_key = "stage_id";

    static $belongs_to = array(
        array("trouble", "class_name" => "Trouble"),
        array("state", "class_name" => "TroubleState", "foreign_key" => "state_id", "readonly" => true)
    );

}
