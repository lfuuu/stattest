<?php
	/**
	 * Генерирует список html элементов option.
	 * Принимает необязательные параметры "mode,start,end,selected"
	 * Если параметр mode не указан, выбирается 'Y'.
	 * Если параметр start не указан, выбирается date($mode)-1.
	 * Если параметр end не указан, выбирается текущий date($mode)+1.
	 * Если параметр selection не указан, выбирается date($mode).
	 * Параметр start - первое число в списке
	 * Параметр end - последнее число в списке
	 * Параметр selected - выбранное, по умолчанию, значение
	 * @param array $params
	 * @param Smarty &$smarty
	 * @return String
	 */
	function smarty_function_generate_sequence_options_select($params,&$smarty){
		$mode = 'Y';
		if(isset($params['mode']))
			$mode = $params['mode'];
		$start = date($mode)-1;
		$end = date($mode)+1;
		$selected = date($mode);
		if(isset($params['start']))
			$start = $params['start'];
		if(isset($params['end']))
			$end = $params['end'];
		if(isset($params['selected']))
			$selected = $params['selected'];

		$output = "";
		for($y=$start;$y<=$end;$y++){
			$output .= "<option value=".$y.(($y==$selected)?" selected ":"").">".$y."</option>";
		}
		return $output;
	}
?>