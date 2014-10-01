<?php
class GoodsIncomeOrder extends ActiveRecord\Model
{
	static $table_name = 'g_income_order';
	static $belongs_to = array(
		array('client_card', 'class_name' => 'ClientCard'),
		array('client', 'class_name' => 'ClientCard'),
		array('organization', 'class_name' => 'Organization'),
		array('store', 'class_name' => 'Store', 'foreign_key' => 'store_id'),
		array('manager', 'class_name' => 'User', 'foreign_key' => 'manager_id'),
	);
	static $has_many = array(
		array('lines', 'class_name' => 'GoodsIncomeOrderLine', 'foreign_key' => 'order_id'),
		array('documents', 'class_name' => 'GoodsIncomeDocument', 'foreign_key' => 'order_id'),
		array('stores', 'class_name' => 'GoodsIncomeStore', 'foreign_key' => 'order_id'),
	);

    const STATUS_NOT_AGREED    = 'Не согласован';
    const STATUS_AGREED        = 'Согласован';
    const STATUS_CONFIRMED    = 'Подтвержден';
    const STATUS_ENTERING    = 'К поступлению';
    const STATUS_CLOSED        = 'Закрыт';

	const STATUS_STAT_ENTERING	= 'Поступление';
	const STATUS_STAT_CLOSED	= 'Закрыт';

	public static $statuses = array(
		self::STATUS_NOT_AGREED	=> 'Не согласован',
		self::STATUS_AGREED		=> 'Согласован',
		self::STATUS_CONFIRMED	=> 'Подтвержден',
		self::STATUS_ENTERING	=> 'К поступлению',
		self::STATUS_CLOSED		=> 'Закрыт',
	);

    static $before_save = array('calculate_ready');

    public function getOrder($orderNumber, $clientId = false)
    {
        $conditions = array(
                'number' => $orderNumber
                );

        if($clientId)
            $conditions['client_card_id'] = $clientId;

        return GoodsIncomeOrder::find('first', array(
                    'conditions' => $conditions,
                    'order' => 'date desc',
                    'limit' => 1
                    )
                );
    }

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

        $data['Статус'] = $status;
        $data['Проведен'] = (bool)$isActive;

        try{
            $order = Sync1C::getClient()->saveGoodsIncomeOrder($data);
        }catch(Exception $e)
        {
            die($e->getMessage());
        }
        return $order;
    }

    private function _to_save()
    {
        $list = array();
        foreach($this->lines as $line) {

            $list[] = array(
                'Номенклатура' => $line->good_id,
                'Количество' => $line->amount,
                'Цена' => $line->price,
                'КодСтроки' => $line->line_code,
                'ДатаПоступления' => ($line->incoming_date ? $line->incoming_date->format("atom") : '0001-01-01T00:00:00')
            );
        }

        $data = array(
            'Код1С' => $this->id,
            'Проведен' => (bool)$this->active,
            'КодКонтрагента' => $this->client_card_id,
            'НомерПоДаннымПоставщика' => $this->external_number,
            'ДатаПоДаннымПоставщика' => $this->external_date ? $this->external_date->format('atom') : '0001-01-01T00:00:00',
            'Статус' => $this->status,
            'Организация' => $this->organization_id,
            'Склад' => $this->store_id,
            'Валюта' => $this->currency,
            'ЦенаВключаетНДС' => (bool)$this->price_includes_nds,
            'Менеджер' => $this->manager_id,
            'СписокПозиций' => $list,
        );

        return $data;
    }

    public function checkClose($orderId)
    {
        try{
            $order = GoodsIncomeOrder::find($orderId);
        }catch(ActiveRecord\RecordNotFound $e)
        {
            return null;
        }
        // other error let it be throw ecxception

        if(!$order || !$order->active || $order->status == GoodsIncomeOrder::STATUS_CLOSED) return null;


        $order_lines =GoodsIncomeOrderLine::find("first", array(
                    "select" => "sum(l.amount) as sum_amount", 
                    "from" => "g_income_order_lines l",
                    "joins" => "inner join g_income_order o on (o.id = l.order_id)",
                    "conditions" => array("l.order_id = ? and o.active", $orderId))
                );

        if(!$order_lines->sum_amount) return null;
        $orderAmount = (int)$order_lines->sum_amount;


        $doc_lines =GoodsIncomeDocumentLine::find("first", array(
                    "select" => "sum(l.amount) as sum_amount", 
                    "from" => "g_income_document_lines l",
                    "joins" => "inner join g_income_document d on (d.id = l.document_id)",
                    "conditions" => array("l.order_id = ? and d.active", $orderId))
                );

        if(!$doc_lines->sum_amount) return null;
        $docAmount = (int)$doc_lines->sum_amount;


        $store_lines =GoodsIncomeStoreLine::find("first", array(
                    "select" => "sum(l.amount) as sum_amount", 
                    "from" => "g_income_store_lines l",
                    "joins" => "inner join g_income_store s on (s.id = l.document_id)",
                    "conditions" => array("l.order_id = ? and s.active", $orderId))
                );

        if(!$store_lines->sum_amount) return null;
        $storeAmount = (int)$store_lines->sum_amount;

        //echo "<hr>store: ".$storeAmount.", doc: ".$docAmount.", order: ".$orderAmount;

        if($storeAmount == $docAmount && $docAmount == $orderAmount)
        {
            // need close
            $order->setStatusAndSave(GoodsIncomeOrder::STATUS_CLOSED, $order->active);
        }

    }

    public function get_trouble()
    {
        return Trouble::find("first", array(
                    "conditions" => array("bill_no = ?", $this->number), 
                    "order" => "id desc")
                );
    }

    public function isClosed()
    {
        return $this->trouble->current_stage->state->name == GoodsIncomeOrder::STATUS_STAT_CLOSED;
    }

    public function isEntering()
    {
        return $this->trouble->current_stage->state->name == GoodsIncomeOrder::STATUS_STAT_ENTERING;
    }


}
