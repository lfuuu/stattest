<?php
require PATH_TO_ROOT.'libs/Smarty.class.php';

function __count_rows_func($params,&$smarty){
	if (isset($params['start'])) $c=$params['start']; else $c=1;
	foreach ($params as $k=>$p) if ($k!='start'){	//for out parameter
		$c+=count($p);
	}
	return $c;
}
function __count_comments($params,&$smarty){
	$c=0;
	foreach ($params['v']['pays'] as $k=>&$p) {
		$c++;
		if ($p['comment']) $c++;
	}
	$c = ($c==0)?++$c:$c;
//	if ($params['v']['bill']['comment']) $c--;
	$smarty->assign('rowspan',$c);
//	if (isset($l)) $smarty->assign('last_rowspan',$l);
	return '';
}

function __implode($params,&$smarty){
	return implode($params['sep'],$params['in']);
}

function __sort_link($params, &$smarty){
	$sort=$params['sort_cur'];
	$sort_d=$params['sort'];
	$so=$params['so_cur'];
	$link=$params['link'].(isset($params['link1']) ? $params['link1'] : "");
	for ($i=2;isset($params['link'.$i]);$i++) $link.=$params['link'.$i];
	if ($sort==$sort_d){
		$v = ($so==0 ? '&#8593;' : '&#8595;');
	} else {
		$v = '';		
	}
	$v.='<a href="'.$link.'&sort='.$sort_d.'&so='.($sort==$sort_d ? (1-$so) : $so).'">'.$params['text'].'</a>';
	return $v;
}
function __rus_date($params, &$smarty){
	return get_rus_date();
}

function __mformat($params, &$smarty){
	if ($params['param']==0) return '';
	return mdate($params['format'],is_numeric($params['param'])?$params['param']:strtotime($params['param']));
}
function __fsize($params,&$smarty){
	$v=$params['value'];
	$v=round($v/(1024*10.24))/100;
	$p=explode('.',$v);
	if (strlen($p[1])<1) $v.='.0';
	if (strlen($p[1])<2) $v.='0';
	return $v;
}
function __fsizeKB($params,&$smarty){
	$v=$params['value'];
	$v=round($v/10.24)/100;
	$p=explode('.',$v);
	if (strlen($p[1])<1) $v.='.0';
	if (strlen($p[1])<2) $v.='0';
	return $v.' Kb';
}

function __ipstat($params, &$smarty){
	$v=$params['net'];
	if(!$v)
		return '';
	if(!access('monitoring','view'))
		return $v;
	if(isset($params['color']))
		$c=' style="color:'.$params['color'].'"';
	else
		$c='';

	$data = isset($params['data'])
		  ? $params['data']
		  : array();

	$imgshow = 1;
	if(isset($data['actual']) && !$data['actual'])
		$imgshow=0;

	if(!preg_match("/(\d+)\.(\d+)\.(\d+)\.(\d+)(\/(\d+))?/",$v,$m))
		return '?';
	if(!isset($m[6]) || ($m[6]==32)){
		$R = array($ip="{$m[1]}.{$m[2]}.{$m[3]}.{$m[4]}");
		if($ip=="0.0.0.0")
			return "0.0.0.0";
	}else{
		$R = array("{$m[1]}.{$m[2]}.{$m[3]}.".($m[4]+1),"{$m[1]}.{$m[2]}.{$m[3]}.".($m[4]+2));
		$ip="{$m[1]}.{$m[2]}.{$m[3]}.{$m[4]}";
	}
	return '<table cellspacing=0 cellpadding=0 border=0><tr><td valign=middle>'.\app\classes\StatModule::monitoring()->get_image($R).'</td><td valign=middle'.$c.'>'.
					'<a href="?module=monitoring&ip='.$R[0].'"'.$c.'>'.$ip.'</a>'.
					(isset($R[1])?'/<a href="?module=monitoring&ip='.$R[1].'"'.$c.'>'.$m[6].'</a>':'').' </td></tr></table>';
}

function __my_prefilter($source, &$smarty) {
	$source=str_replace("\n"," ",$source);
	return str_replace("\r","",$source);
}

function __dbmap_prefilter($source,&$smarty) {
	$v='{if ($item.show)}
<TR><TD class=left{if ($hl==$key)} style="background-color: #EEE0B9; font-weight:bold"{/if}>{$item.translate.0}:</TD>
		<TD {if ($hl==$key)} style="background-color: #EEE0B9"{/if}>

{if ($item.show&1)==1}
	<input id=row_{$key} name="row[{$key}]" value="{$item.value}"> 
{/if}

{if ($item.show&2)==2}
	<SELECT id=row2_{$key} name="row[{$key}{if $item.show==3}_{/if}]">
		{foreach from=$item.variants item=i_item name=inner}
			<option value={$i_item.key}{if $item.value==$i_item.key} selected{/if}>{$i_item.show}</option>
		{/foreach}
		{if $item.show==3}
			<option value=nouse selected>использовать текстовое поле</option>
		{/if}
	</SELECT>
{/if}
{if ($item.show&4)==4}
	{$item.value}
	<input id=row_{$key} type=hidden name="row[{$key}]" value="{$item.value}">
{/if}
</TD><td>{$item.translate.1}</td></TR>

{else}
{if (!isset($HIDE_COMPLETELY))}
	<input id=row_{$key} type=hidden name="row[{$key}]" value="{$item.value}">
{/if}
{/if}';
	return str_replace("{dbmap_element}",$v,$source);
}

function __get_region_by_dgroups($params){
	if(isset($params['dgs'])){
		$tmp = explode(':',$params['dgs']);
		$g = $tmp[0];
		$s = $tmp[1];
		unset($tmp);
	}elseif(isset($params['dgroup'])){
		$g = $params['dgroup'];
		$s = $params['dsubgroup'];
	}

	$ret = '';
	if($g == 0){
		if($s == 0)
			$ret = 'Москва (моб)';
		elseif($s==1)
			$ret = 'Москва (стац)';
		elseif($s==96)
			$ret = 'Москва (абон)';
		else
			$ret = 'Москва (др)';
	}elseif($g == 1){
		if($s == 98)
			$ret = 'Россия Фрифон';
		else
			$ret = 'Россия';
	}else{
		if($s == 97)
			$ret = 'Международное Фрифон';
		else
			$ret = 'Международное';
	}
	return $ret;
}
function __get_minutes_by_seconds($params){
	return floor($params['sec']/60).':'.($params['sec']%60);
}

function __get_time($param)
{
    $v = $param["sec"];

    $sign = $v < 0 ? "-" :"";

    $v = abs($v);

    $sec = $v%60;
    $v -=$sec;

    $min = ($v%3600)/60;
    $v -=$min*60; 

    $hour = ($v%(3600*24))/3600;
    $v -= $hour*3600;

    $day = $v/(3600*24);

    return $sign.($day ? $day."d ":"").sprintf("%02d", $hour).":".sprintf("%02d", $min).":".sprintf("%02d", $sec);
}

function smarty_modifier_hl($string,$hl){
	if (!$hl) return $string;
	return preg_replace("/".preg_quote($hl)."/i","<span style='background-color:#D0D0FF; color:#000000'>$0</span>",$string);
}
function smarty_modifier_num_format($string, $with_zero = false, $after_dot = 0){
	if (!$string && !$with_zero)
	{
		return '';
	}
	$string = number_format($string, $after_dot, ',', ' ');
	$string = str_replace(' ', '&nbsp', $string);
	return $string;
}
function smarty_modifier_okei_name($string){
	$options = array();
	$options['select'] = 'name';
	$options['conditions'] = array('okei = ? AND name NOT LIKE ?', $string, '%del%');
	$res = GoodUnit::first($options);
	return $res->name;
}
function smarty_modifier_round($val,$b,$t = ''){
    $val = round($val, $b);
	return sprintf("%0.".$b."f",($t==='-'?-$val:$val));
}
function smarty_modifier_mround($val,$r1,$r2){
	$v = $val - round($val,$r1);
	return sprintf("%0.".($v==0?$r1:$r2)."f",$val);
}
function smarty_modifier_wordify($val,$curr) {
	return Wordifier::Make($val,$curr);	
}
function smarty_modifier_mdate($value,$format) {
	return mdate($format,is_numeric($value)?$value:strtotime($value));	
}
/**
 * Smarty bytesize modifier plugin
 *
 * Type:     modifier<br>
 * Name:     bytesize<br>
 * 
 * @param int  $number        input value in bytes
 * @param string  $esc_type      escape type
 * 
 * @return string escaped input string
 */
function smarty_modifier_bytesize($number, $esc_type = 'Mb')
{
    static $st = 0;
    $sign = '';
    if ($number < 0)
    {
	$number = -$number;
	$sign = '-';
    }
    $step = array(
	'0' => 'b',
	'1' => 'Kb',
	'2' => 'Mb',
	'3' => 'Gb',
	'4' => 'Tb',
	'5' => 'Pb',
	'6' => 'Eb',
	'7' => 'Zb',
	'8' => 'Yb'
    );
    $obr_step = array_flip($step);
    if (!isset($obr_step[$esc_type])) {
	$esc_type = 'b';
    }
    $st = $obr_step[$esc_type];
    while ($number >= 1024) {
	$st++;
	$number = $number/1024;
	if ($st > 8) {
		break;
	}
    }
    if ($number >= 1000 && $st <= 7) {
	$st++;
	$number = $number/1024;
    }
    
    return $sign . round($number, 2) . ' ' . $step[$st];
}
function smarty_modifier_find_urls($text)
{
    $text = preg_replace('#((http|https)?://(\S)+[\.](\S)*[^\s.,> )\];\'\"!?])#is', "<a target='_blank' href='\\1'>\\1</a>", $text);
    return $text;
}
function smarty_function_objCurrency($params,&$smarty) {
	$op = &$params['op'];
	$obj = $params['obj'];
	$simple = (isset($params['simple'])&&$params['simple']==1);
	
	if ($obj=='delta') {
		$curr = (isset($op['bill']) ? $op['bill']['currency'] : $params['currency']);
		$sum = sprintf("%0.2f",$op['delta']);
		if ($curr=='RUR') return $sum.' р';
		
		if (!$simple && count($op['pays'])>=1 && isset($op['pays'][0]) && ($op['pays'][0]['payment_rate']>2) && $op['pays'][0]['currency']=='RUR') {
			return $sum.' $<br><span style="font-size:85%">'.sprintf("%0.2f",$op['delta']*$op['pays'][0]['payment_rate']).' р</span>';
		} else {
			return $sum.' $';
		}
	} elseif ($obj=='delta2') {
		$curr = (isset($op['bill']) ? $op['bill']['currency'] : $params['currency']);
		$sum = sprintf("%0.2f",$op['delta2']);
		if ($curr=='RUR') return $sum.' р';
		
		if (!$simple && count($op['pays'])>=1 && isset($op['pays'][0]) && ($op['pays'][0]['payment_rate']>2) && $op['pays'][0]['currency']=='RUR') {
			return $sum.' $<br><span style="font-size:85%">'.sprintf("%0.2f",$op['delta2']*$op['pays'][0]['payment_rate']).' р</span>';
		} else {
			return $sum.' $';
		}
	} elseif ($obj=='pay_full') {
		$sum = sprintf("%0.2f",$params['pay']['sum_full']);
		$sum_rur = sprintf("%0.2f",$params['pay']['sum_rub_full']);
		$curr = isset($params['pay']['currency']) ? $params['pay']['currency'] : $params['currency'];
		$one = (abs($params['pay']['payment_rate']-1)<0.0005);
		if ($one) return $sum.' р';
		if ($curr=='USD' || $simple) return $sum.' $';
		return $sum_rur.' р = '.$sum.' $';
	} elseif ($obj=='pay2') {
		$sum = sprintf("%0.2f",$params['pay']['sum_pay']);
		$sum_rur = sprintf("%0.2f",$params['pay']['sum_pay_rub']);
		$curr = isset($params['pay']['currency']) ? $params['pay']['currency'] : $params['currency'];
		$one = (abs($params['pay']['payment_rate']-1)<0.0005);
		if ($one) return $sum.' р';
		if ($curr=='USD' || $simple) return $sum.' $';
		return $sum_rur.' р = '.$sum.' $';
	} elseif ($obj=='pay') {
		$sum = sprintf("%0.2f",$params['pay']['sum']);
		$sum_rur = sprintf("%0.2f",$params['pay']['sum_rub']);
		$curr = $params['pay']['currency'];
		$one = (abs($params['pay']['payment_rate']-1)<0.0005);
		if ($one) return $sum.' р';
		if ($curr=='USD' || $simple) return $sum.' $';
		return $sum_rur.' р = '.$sum.' $';
	}
}

function __get_item_price($v, &$smarty) 
{
    $price = $v["item"]["sum"];
    $nds = $v["item"]["line_nds"];
    $sum = $price*1.18;
    $price = (((18-$nds)/100*$price)+$price);

    $round = isset($v["round"]) ? $v["round"] : 4;
    $amount = isset($v["amount"]) ? $v["amount"] : 1;

    return sprintf("%.".$round."f", $price*$amount);
}


class MySmarty extends Smarty {
	var $cid=0;
	var $LINK_START;
	var $ignore=0;
	function MySmarty(){
		global $G;
        $this->Smarty();
		$this->template_dir = DESIGN_PATH;
	   	$this->compile_dir  = DESIGNC_PATH;
		$this->compile_check = true;
		$this->debugging = (DEBUG_LEVEL>=3 ? true : false);
		$this->assign('_smarty_debug_output','html');
		$this->register_prefilter("__dbmap_prefilter");
//		if (DEBUG_LEVEL==0) $this->register_prefilter("__my_prefilter");
		$this->register_function('implode','__implode');
		$this->register_function('access','access');
		$this->register_function('access_action','access_action');
		$this->register_function('count_rows_func','__count_rows_func');
		$this->register_function('count_comments','__count_comments');
		$this->register_function('sort_link','__sort_link');
		$this->register_function('mformat','__mformat');
		$this->register_function('ipstat','__ipstat');
		$this->register_function('fsize','__fsize');
		$this->register_function('fsizeKB','__fsizeKB');
		$this->register_function('rus_date','__rus_date');
		$this->register_function('objCurrency','smarty_function_objCurrency');
		$this->register_function('get_region_by_dgroups','__get_region_by_dgroups');
		$this->register_function('get_minutes_by_seconds','__get_minutes_by_seconds');
		$this->register_function('get_time','__get_time');
		$this->register_function('get_item_price','__get_item_price');
		$this->register_modifier('time_period','time_period');
		$this->register_modifier('hl','smarty_modifier_hl');
		$this->register_modifier('wordify','smarty_modifier_wordify');
		$this->register_modifier('round','smarty_modifier_round');
		$this->register_modifier('mround','smarty_modifier_mround');
		$this->register_modifier('mdate','smarty_modifier_mdate');
		$this->register_modifier('num_format','smarty_modifier_num_format');
		$this->register_modifier('okei_name','smarty_modifier_okei_name');
		$this->register_modifier('bytesize','smarty_modifier_bytesize');
                $this->register_modifier('find_urls','smarty_modifier_find_urls');
		$this->register_modifier('rus_fin','rus_fin');
		$this->assign('premain',array());
		$this->assign('WEB_PATH', WEB_ADDRESS . WEB_PATH);
		$this->assign('IMAGES_PATH',WEB_IMAGES_PATH);
		$this->assign('PATH_TO_ROOT',WEB_PATH);
		$this->assign('SUM_ADVANCE',SUM_ADVANCE);
		$this->LINK_START='index.php?';
		$this->assign_by_ref('LINK_START',$this->LINK_START);
	}
		
	function _add($item, $page, $parse_now){
		if ($this->ignore) return;
		if ($parse_now) {
			$this->append($item,array(0,$this->fetch($page)));
		} else {
			$this->append($item,array(1,$page));
		}
	}
	function AddMain($page, $parse_now = 0){
		$this->_add('main', $page, $parse_now);
	}
	function AddTop($page, $parse_now = 0){
		$this->_add('top', $page, $parse_now);
	}
	function AddPreMain($page, $parse_now = 0){
		$this->_add('premain', $page, $parse_now);
	}
	function Process(){
		if ($this->ignore) return;
		$this->display('index.tpl');
	}
	function ProcessEx($template=''){
		global $G;
		$this->ignore=1;
		if ($template) {
			$this->display($template);
			return (count($G['errors'])+count($G['notices'])?0:1);
		}
	}
	function AddMenu($title,$arr){
		if ($this->ignore) return;
		$this->assign('panel_id',$this->cid);
		$this->assign('panel_title',$title);
		$this->assign_by_ref('panel_data',$arr);
		$this->_add('panel', 'panel.tpl', 1);
		$this->cid++;
	}

    function var_is_array($name) {
	    if(isset($this->_tpl_vars[$name])) {
			return is_array($this->_tpl_vars[$name]);
	    } else return false;
    }

}
?>
