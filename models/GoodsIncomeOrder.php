<?php
class GoodsIncomeOrder extends ActiveRecord\Model
{
	static $table_name = 'g_income_order';
	static $belongs_to = array(
		array('client_card', 'class_name' => 'ClientCard'),
		array('organization', 'class_name' => 'Organization'),
		array('store', 'class_name' => 'Store', 'foreign_key' => 'store_id'),
		array('manager', 'class_name' => 'User', 'foreign_key' => 'manager_id'),
	);
	static $has_many = array(
		array('lines', 'class_name' => 'GoodsIncomeOrderLine', 'foreign_key' => 'order_id'),
		array('documents', 'class_name' => 'GoodsIncomeDocument', 'foreign_key' => 'order_id'),
		array('stores', 'class_name' => 'GoodsIncomeStore', 'foreign_key' => 'order_id'),
	);

	const STATUS_NOT_AGREED	= 'îÅ ÓÏÇÌÁÓÏ×ÁÎ';
	const STATUS_AGREED		= 'óÏÇÌÁÓÏ×ÁÎ';
	const STATUS_CONFIRMED	= 'ğÏÄÔ×ÅÒÖÄÅÎ';
	const STATUS_ENTERING	= 'ë ĞÏÓÔÕĞÌÅÎÉÀ';
	const STATUS_CLOSED		= 'úÁËÒÙÔ';

	public static $statuses = array(
		self::STATUS_NOT_AGREED	=> 'îÅ ÓÏÇÌÁÓÏ×ÁÎ',
		self::STATUS_AGREED		=> 'óÏÇÌÁÓÏ×ÁÎ',
		self::STATUS_CONFIRMED	=> 'ğÏÄÔ×ÅÒÖÄÅÎ',
		self::STATUS_ENTERING	=> 'ë ĞÏÓÔÕĞÌÅÎÉÀ',
		self::STATUS_CLOSED		=> 'úÁËÒÙÔ',
	);

	static $before_save = array('calculate_ready');

	public function calculate_ready() {
		$this->ready =
			$this->active
			&& (
				$this->status == self::STATUS_ENTERING
				|| $this->status == self::STATUS_CLOSED
			);
	}

    public function setStatusAndSave($status, $isActive = true)
    {
        $data = $this->_to_save();

        $data['óÔÁÔÕÓ'] = $status;
        $data['ğÒÏ×ÅÄÅÎ'] = (bool)$isActive;

        try{
            $order = Sync1C::getClient()->saveGoodsIncomeOrder($data);
        }catch(Exception $e)
        {
            die($e->getMessage());
        }
        return $order;
    }

    private function _to_save($data)
    {
        $list = array();
        foreach($this->lines as $line) {

            $list[] = array(
                'îÏÍÅÎËÌÁÔÕÒÁ' => $line->good_id,
                'ëÏÌÉŞÅÓÔ×Ï' => $line->amount,
                'ãÅÎÁ' => $line->price,
                'ëÏÄóÔÒÏËÉ' => $line->line_code,
                'äÁÔÁğÏÓÔÕĞÌÅÎÉÑ' => ($line->incoming_date ? $line->incoming_date->format("atom") : '0001-01-01T00:00:00')
            );
        }

        $data = array(
            'ëÏÄ1ó' => $this->id,
            'ğÒÏ×ÅÄÅÎ' => (bool)$this->active,
            'ëÏÄëÏÎÔÒÁÇÅÎÔÁ' => $this->client_card_id,
            'îÏÍÅÒğÏäÁÎÎÙÍğÏÓÔÁ×İÉËÁ' => $this->external_number,
            'äÁÔÁğÏäÁÎÎÙÍğÏÓÔÁ×İÉËÁ' => $this->external_date ? $this->external_date->format('atom') : '0001-01-01T00:00:00',
            'óÔÁÔÕÓ' => $this->status,
            'ïÒÇÁÎÉÚÁÃÉÑ' => $this->organization_id,
            'óËÌÁÄ' => $this->store_id,
            '÷ÁÌÀÔÁ' => $this->currency,
            'ãÅÎÁ÷ËÌÀŞÁÅÔîäó' => (bool)$this->price_includes_nds,
            'íÅÎÅÄÖÅÒ' => $this->manager_id,
            'óĞÉÓÏËğÏÚÉÃÉÊ' => $list,
        );

        return $data;
    }
}
