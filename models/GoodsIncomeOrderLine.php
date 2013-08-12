<?php
class GoodsIncomeOrderLine extends ActiveRecord\Model
{
	static $table_name = 'g_income_order_lines';
	static $belongs_to = array(
		array('order', 'class_name' => 'GoodsIncomeOrder'),
		array('good', 'class_name' => 'Good'),
	);

	public function getDocumentAmount()
	{
		$result =
			self::find_by_sql(
				"	select sum(amount) as amount from g_income_document_lines l
					left join g_income_document d on l.document_id=d.id
					where l.order_id=? and l.line_code=? and d.active=1 ",
				array($this->order_id, $this->line_code)
			);
		return count($result) > 0 ? $result[0]->amount : 0;
	}


	public function getStoreAmount()
	{
		$result =
			self::find_by_sql(
				"	select sum(amount) as amount from g_income_store_lines l
					left join g_income_store d on l.document_id=d.id
					where l.order_id=? and l.good_id=? and d.active=1 ",
				array($this->order_id, $this->good_id)
			);
		return count($result) > 0 ? $result[0]->amount : 0;
	}
}