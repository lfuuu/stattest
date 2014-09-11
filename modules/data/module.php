<?php
class m_data extends IModule {

	public function data_get_gtd()
	{
		$code = isset($_REQUEST['code']) ? $_REQUEST['code'] : '';

		$gtd = Gtd::find('first', array('conditions' => array('code = ?', $code)));

		$result = array();

		if ($gtd) {
			$result = array(
				'id' => $gtd->id,
				'code' => $gtd->code,
				'country' => iconv('koi8-r', 'utf-8', $gtd->country->name),
			);
		}

		header('Content-Type: application/json');
		echo json_encode($result); exit;
	}

	public function data_search_goods()
	{
		header('Content-Type: application/json');

		$query = isset($_REQUEST['query']) ? $_REQUEST['query'] : '';

		$items = array();
		foreach(Good::search(iconv('utf-8', 'koi8-r', $query), 25) as $good) {
			$items[] = array(
				'id' => $good->id,
				'num_id' => $good->num_id,
				'art' => iconv('koi8-r', 'utf-8', $good->art),
				'name' => iconv('koi8-r', 'utf-8', $good->name),
				'okei' => iconv('koi8-r', 'utf-8', $good->unit->okei),
				'unit_name' => iconv('koi8-r', 'utf-8', $good->unit->name),
			);
		}

		die(json_encode(array(
			'query' => $query,
			'result' => $items,
		)));
	}
}
