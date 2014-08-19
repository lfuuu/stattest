<?php
/**
 * Класс предназначен для инициализации объектов библиотеки jpgraph
 */
class JpGraphsInit
{
	/**
	 * Создает экземпляр класса Graph для "линейных" графиков 
	 * @param string $title Название графика
	 * @param int $size_x ширина картинки
	 * @param int $size_y высота картинки
	 * @param string $type параметр Scale для экземпляра класса Graph
	 */
	public static function getLineGraph($title, $size_x = 640, $size_y = 480, $type = 'textlin')
	{
		$graph = new Graph($size_x,$size_y, 'auto');
		$graph->SetScale($type);
		
		$theme_class=new UniversalTheme;

		$graph->SetTheme($theme_class);
		$graph->img->SetAntiAliasing(false);
		$graph->title->Set(Encoding::toUtf8($title));
		$graph->SetBox(false);

		$graph->img->SetAntiAliasing();

		$graph->yaxis->HideZeroLabel();
		$graph->yaxis->HideLine(false);
		$graph->yaxis->HideTicks(false,false);

		$graph->xgrid->Show();
		$graph->xgrid->SetLineStyle("solid");
		$graph->xgrid->SetColor('#E3E3E3');
		return $graph;
	}
	/**
	 * Добавление графиков звонков 
	 * @param Graph $graph экземпляр класса Graph
	 * @param array $data массив с данными о количестве или продолжительности звонков по дням
	 * @param int $week_start минимальное числовое значение дня недели начала месяца среди последних 3 месяцев 
	 */
	public static function setLines($graph, $data, $week_start)
	{
		$colors = array('#6495ED', '#B22222', '#FF1493');
		$i = 0;
		$month = date('n');
		foreach ($data as $k => $v)
		{
			$year = ($month < $k) ? date('Y')-1 : date('Y');
			$ts = mktime(0,0,0,$k,1,$year);
			$v = self::prepareLines($v, $week_start, $ts);
			$name='line'.$k;
			$$name = new LinePlot($v);
			$graph->Add($$name);
			$$name->SetColor($colors[$i]);
			$$name->SetLegend(Encoding::toUtf8(mdate('месяц',$ts)));
			$i++;
		}
	}
	/**
	 * Добавление графиков отсутствия звонков 
	 * @param Graph $graph экземпляр класса Graph
	 * @param array $data массив с данными о днях когда не было звонков
	 * @param int $week_start минимальное числовое значение дня недели начала месяца среди последних 3 месяцев 
	 */
	public static function setNoCallLines($graph, $data, $week_start)
	{
		$colors = array('#6495ED', '#B22222', '#FF1493');
		$i = 0;
		$month = date('n');
		foreach ($data as $m => $v2)
		{
			$year = ($month < $m) ? date('Y')-1 : date('Y');
			$ts = mktime(0,0,0,$m,1,$year);
			foreach ($v2 as $v1)
			{
				$_data = self::prepareLines(array(), $week_start, $ts);
				foreach ($v1 as $k => $v) {
					for ($j=0;$j<$k;$j++) $_data[] = '-';
				}
				$_data[] = $v;
				unset($plot);
				$plot = new LinePlot($_data);
				$graph->Add($plot);
				$plot->SetColor($colors[$i]);
				$plot->mark->SetType(MARK_FILLEDCIRCLE,'',1.0);
				$plot->mark->SetWeight(2);
				$plot->mark->SetWidth(4);
				$plot->mark->setColor($colors[$i]);
				$plot->mark->setFillColor($colors[$i]);
			}
			$plot->SetLegend(Encoding::toUtf8('нет звонков в '. mdate('месяце',$ts)));
			$i++;
		}
	}
	/**
	 * Сдвиг вправо графиков 
	 * @param array $_data экземпляр класса Graph
	 * @param int $week_start минимальное числовое значение дня недели начала месяца среди последних 3 месяцев 
	 * @param int $ts  timestamp начала месяца
	 */
	private static function prepareLines($_data, $week_start, $ts)
	{
		$w_day = date('w', $ts);
		$data = array();
		if ($week_start < $w_day)
		{
			for ($i=$week_start;$i<$w_day;$i++)
			{
				$data[] = '-';
			}
			foreach ($_data as $v)
			{
				$data[] = $v;
			}
		} else {
			return $_data;
		}
		return $data;
	}
	/**
	 * Создает экземпляр класса Graph для "столбчатых" диаграмм 
	 * @param string $title Название графика
	 * @param int $size_x ширина картинки
	 * @param int $size_y высота картинки
	 * @param string $type параметр Scale для экземпляра класса Graph
	 */
	public static function getBarGraph($title, $size_x = 640, $size_y = 480, $type = 'textlin')
	{
		$graph = new Graph($size_x,$size_y,'auto');
		$graph->SetScale($type);

		$graph->SetMargin(35,50,20,5);

		$theme_class = new UniversalTheme;
		$graph->SetTheme($theme_class);

		$graph->SetBox(false);

		$graph->ygrid->SetFill(false);
		$graph->yaxis->HideLine(false);
		$graph->yaxis->HideTicks(false,false);
		
		$graph->legend->SetFrameWeight(1);
		$graph->legend->SetColumns(6);
		$graph->legend->SetColor('#4E4E4E','#C0C0C0');
		
		$band = new PlotBand(VERTICAL,BAND_RDIAG,11,"max",'khaki4');
		$band->ShowFrame(true);
		$band->SetOrder(DEPTH_BACK);
		$graph->Add($band);

		$graph->title->Set(Encoding::toUtf8($title));
		return $graph;
	}
}