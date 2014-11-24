<?php
class Graphic2 {
	var $im = null;
	var $x_scale;
	
	function InitColors() {
		$this->c_background = ImageColorAllocate ($this->im, 255, 255, 255);
		$this->c_pict = ImageColorAllocate ($this->im, 192, 192, 192);

		$this->c_font = ImageColorAllocate ($this->im, 0, 128, 64);
		$this->c_font2 = ImageColorAllocate ($this->im, 128, 0, 64);
		$this->c_axis = ImageColorAllocate ($this->im, 128, 128, 128);
		
		$this->c_line=array();
		$this->c_line[0]=ImageColorAllocate ($this->im, 0, 255, 0);
		$this->c_line[10]=ImageColorAllocate ($this->im, 0, 0, 0);
		$this->c_line[5]=ImageColorAllocate ($this->im, 255, 255, 0);
		for ($I=0;$I<=10;$I++) /*if ($I!=5)*/ {
			if ($I<=5) {
				if ($I!=0) {
					$i=($I+1)/6;
				} else $i=0;
			} else {
				$i=($I-5)/5;
			}
			if ($i==1) {
				$v1=1;
				$v2=0;
			} else if ($i==0){
				$v1=0;
				$v2=1;
			} else {
				$v1=1/sqrt(1-$i);
				$v2=1/sqrt($i);
				$vs=$v1+$v2; $v1=$v1/$vs; $v2=$v2/$vs;
			}
			if ($I<=5) {
				$r=255*$v1+128*$v2;
				$g=  0*$v1+255*$v2;
				$b=  0*$v1+  0*$v2;
			} else {
				$r=128*$v1+255*$v2;
				$g=  0*$v1+  0*$v2;
				$b=  0*$v1+  0*$v2;
			}
			$this->c_line[$I]=ImageColorAllocate ($this->im, $r,$g,$b);
		}
	}
	function Graphic2($type,$x_scale=1,$days = null) {
		if ($type=='day') {
			$pict_width=288;
			$pict_height=200;
		} else {
			$pict_width=$days*24;
			$pict_height=200;
			$this->days=$days;
		}
		$this->x_scale=$x_scale;
		$this->type=$type;
		$this->pict_width = $pict_width*$x_scale;
		$this->pict_height = $pict_height;
		$this->pict_margin = array(20,10,10,15);
		$this->width = $this->pict_width+$this->pict_margin[0]+$this->pict_margin[2];
		$this->height = $this->pict_height+$this->pict_margin[1]+$this->pict_margin[3];
		$this->im = ImageCreateTrueColor($this->width,$this->height);
		$this->InitColors();
	}
	function ShowCols() {
		$this->im = ImageCreateTrueColor(40*11,50);
		$this->InitColors();
		ImageFill($this->im,0,0,$this->c_background);
		for ($i=0;$i<=10;$i++) {
			ImageFilledRectangle($this->im,$i*40,0,$i*40+39,40,$this->c_line[$i]);
			ImageString($this->im,1,$i*40+15,42,(10*$i)."%",$this->c_font);
		}
		header ("Content-Type: image/png");
		ImagePng($this->im);
	}
	function Paint($data,$maxval,$E) {
		ImageFill($this->im,0,0,$this->c_background);
		ImageFilledRectangle($this->im,$this->pict_margin[0],$this->pict_margin[1],$this->pict_width+$this->pict_margin[0],$this->pict_height+$this->pict_margin[1],$this->c_pict);

		$dx = $this->pict_margin[0];
		$dy = $this->pict_margin[1]+$this->pict_height;

		if ($this->type=='day') {
			for ($i=0;$i<=24;$i++) {
				$x=$dx+($i/24)*$this->pict_width;
				if ($i!=24 && ($i%2==0)) ImageString($this->im,1,$x-12,$dy+3,sprintf("%02d",$i).":00",$this->c_font);
				ImageLine($this->im,$x,$this->pict_margin[1],$x,$dy+3,$this->c_axis);
			}
		} else {
			$days=$this->days;
			for ($i=0;$i<=$days;$i++) {
				$x=$dx+($i/$days)*$this->pict_width;
				if ($i!=$days) ImageString($this->im,1,$x-5,$dy+3,$i+1,$this->c_font);
				ImageLine($this->im,$x,$this->pict_margin[1],$x,$dy+3,$this->c_axis);
			}
		}
                $M=0;
		for ($i=0;$i<=$maxval[0];$i+=round($maxval[0]/10)) if ($i!=0){
			$y=$dy-($i/$maxval[0])*$this->pict_height;
			$w=ImageFontWidth(1)*strlen($i);
			ImageString($this->im,1,$dx-$w-5,$y-4,$i,$this->c_font2);
			if (abs($i-$E)>$M) ImageLine($this->im,$dx-3,$y,$dx+$this->pict_width,$y,$this->c_axis);
		}

		$M=0;
		foreach ($data as $x=>$val) {
			if ($val[0]<=0 || $val[1]>$maxval[1]*0.9) {
				$v = 1;
			} else {
				$v = $val[0]/$maxval[0];
			}
			$v=$this->pict_height*$v;
			$c = round(10*$val[1]/$maxval[1]);
			if ($this->x_scale==1) {
				ImageLine($this->im,$x+$dx,$dy-$v,$x+$dx,$dy,$this->c_line[$c]);
			} else {
				$x=$x*$this->x_scale;
				ImageFilledRectangle($this->im,$x+$dx,$dy-$v,$x+$this->x_scale-1+$dx,$dy,$this->c_line[$c]);
			}
			$M+=abs($val[0]-$E);
		}
		$maxtime=$x;
		$cnt=count($data);
		$M=$M/($cnt?$cnt:1);
		for ($i=0;$i<$maxval[0];$i+=round($maxval[0]/10)) if (abs($i-$E)>$M) {
			$y=$dy-($i/$maxval[0])*$this->pict_height;
			ImageLine($this->im,$dx-3,$y,$dx+$this->pict_width,$y,$this->c_axis);
		}

		header ("Content-Type: image/png");
		ImagePng($this->im);
	}
}


/*
class Graphic {
	var $im=0;
	var $x_size=0;
	var $y_size=0;
	var $lpad = 30, $rpad = 2, $dpad = 48, $upad = 2;
	
	function GetLimit(){
		return ($this->x_size-$this->lpad-$this->rpad);
	}
	
	function Graphic($x_size = 608,$y_size = 130) {		//320
		$this->x_size=$x_size;
		$this->y_size=$y_size;
		$this->im=ImageCreate($x_size,$y_size);
		$this->c_background = ImageColorAllocate ($this->im, 255, 255, 255);
		ImageFill($this->im,0,0,$this->c_background);
		$this->c_good_line = ImageColorAllocate ($this->im, 0, 255, 0);
		$this->c_bad_line = ImageColorAllocate ($this->im, 255, 0, 0);
		$this->c_mid_line = ImageColorAllocate ($this->im, 255, 255, 0);

		$this->c_good_fill = ImageColorAllocate ($this->im, 96, 255, 96);
		$this->c_bad_fill = ImageColorAllocate ($this->im, 255, 96, 96);
		$this->c_mid_fill = ImageColorAllocate ($this->im, 255, 255, 96);

		$this->c_font = ImageColorAllocate ($this->im, 0, 0, 128);
		$this->c_font2 = ImageColorAllocate ($this->im, 128, 0, 0);
		$this->c_axis = ImageColorAllocate ($this->im, 192, 192, 192);
	}
	function Paint($arr,$period_days ,$fmt,$num ,$str) {
		ksort($arr);
		$lpad=$this->lpad;
		$rpad=$this->rpad;
		$upad=$this->upad;
		$dpad=$this->dpad;
		$cnt=$this->GetLimit();

		$maxX=0; $maxY=0; $minX=1000000000000;
		foreach ($arr as $k=>$v){
			$maxY=max($maxY,$v);
			$maxX=max($maxX,$k);
//			$minX=min($minX,$k);
		}
		$maxY=$maxY+1;
		if ($maxY<5) $maxY=5;
		$width_src=3600*24.0*$period_days;
		$minX=$maxX-$width_src;
		$width_dst=$cnt;
		$width_asp=$width_dst/$width_src;

		$n=0;
		foreach ($arr as $k=>$v){
//			echo $k.' '.$v.' ';
			$k=round(($k-$minX)*$width_asp)+$lpad;
			if ($v==-1){
				$v=$maxY;
				$col = $this->c_bad_line;
				$col2 = $this->c_bad_fill;
			} else if ($v<0) {
				$v=round((-$v)*($this->y_size-$upad-$dpad)/$maxY);
				$col = $this->c_mid_line;
				$col2 = $this->c_mid_fill;
			} else {
				$v=round($v*($this->y_size-$upad-$dpad)/$maxY);
				$col = $this->c_good_line;
				$col2 = $this->c_good_fill;
			}

			if ($n) {
				ImageFilledRectangle($this->im,$k_last+$d+1,$this->y_size-$dpad-$v_last,$k+$d-1,$this->y_size-$dpad+2,$col2);
			}
			ImageLine($this->im,$k+$d,$this->y_size-$dpad-$v,$k+$d,$this->y_size-$dpad+2,$col);
			$n++;
			$k_last=$k;
			$v_last=$v;
		}

		for ($i=0;$i<=5;$i++){
			$y=(1-($i)/5)*($this->y_size-$upad-$dpad)+$upad;
			ImageString($this->im,1,2,$y-3,round($maxY*$i/5).'ms',$this->c_font);
			ImageLine($this->im,28,$y,$this->x_size,$y,$this->c_axis);
		}
		$dt=(int)(date('Z'));

		
		$dx=$width_src/($num*4);
		for ($i=0;$i<$num*4;$i++){
			$k=round($dx*$i*$width_asp)+$lpad;
			ImageLine($this->im,$k,$this->y_size-($dpad-13),$k,$this->y_size-$dpad,$this->c_axis);
		}
		$dx=$width_src/$num;
		for ($i=0;$i<=$num;$i++) {
			$k=round($dx*$i*$width_asp)+$lpad;
			if ($i%2==1-($num%2)) {
				ImageString($this->im,1,$k-($dpad-18),$this->y_size-($dpad-20),gmdate($fmt,$minX+$i*$dx+$dt),$this->c_font);
				ImageLine($this->im,$k,$upad,$k,$this->y_size-($dpad-18),$this->c_axis);
			} else {
				ImageLine($this->im,$k,$this->y_size-($dpad-18),$k,$this->y_size-$dpad,$this->c_axis);
			}
		}
		ImageLine($this->im,$this->x_size-1,$upad,$this->x_size-1,$this->y_size-($dpad-13),$this->c_axis);
		ImageLine($this->im,$lpad-1,$upad,$lpad-1,$this->y_size-$dpad,$this->c_axis);
		
		$str=str_replace('#1',date('Y-m-d H:i',$minX),$str);
		$str=str_replace('#2',date('Y-m-d H:i',$minX+$width_src),$str);
		ImageString($this->im,2, 20, $this->y_size-15,$str,$this->c_font2);
		header ("Content-Type: image/png");
		ImagePng($this->im);
	}
}*/

?>