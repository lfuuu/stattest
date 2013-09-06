<?php
class Gtd extends ActiveRecord\Model
{
	static $table_name = 'g_gtd';
	static $belongs_to = array(
		array('country', 'class_name' => 'Country'),
	);
}