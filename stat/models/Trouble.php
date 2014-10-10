<?php

Class Trouble extends ActiveRecord\Model
{
    static $table_name = "tt_troubles";


    static $belongs_to = array(
        array("current_stage", "class_name" => "TroubleStage", "foreign_key" => "cur_stage_id")
        );

    static $has_many = array(
        array("stages", "class_name" => "TroubleStage")
    );


}
