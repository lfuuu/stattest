<?php
class GoodsIncomeDocumentLine extends ActiveRecord\Model
{
	static $table_name = 'g_income_document_lines';
	static $belongs_to = array(
		array('document', 'class_name' => 'GoodsIncomeDocument'),
		array('good', 'class_name' => 'Good'),
		array('gtd', 'class_name' => 'Gtd'),
	);
}