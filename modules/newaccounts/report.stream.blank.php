<?php

// Create a new image instance
define(path, "../design/newaccounts/");
$im = imagecreatefromgif(path."blank.gif");
$black = imagecolorallocate($im, 0, 0, 0);
$white = imagecolorallocate($im, 255, 255, 255);

// Make the background white
imagefilledrectangle($im, 0, 0, 49, 19, $white);

$wd = new gd_digitalWriter();
$wt = new gd_textWriter();

/*
$a = array(
        "bill_no" => "201009/0079",
        "fio" => "ζιο",
        "address" => "αδςεσσ",
        "req_no" => "201009-2545",
        "acc_no" => "9876",
        "connum" => "9505678",
        "comment1" => "λονεξτ1",
        "comment2" => "λονεξτ2",
        "passp_series" => "44 55",
        "passp_num" => "1234567",
        "passp_whos_given" => "οχδ φδ ς-ξα",
        "passp_when_given" => "24.23.2003",
        "passp_code" => "2222-11",
        "passp_birthday" => "9.11.1999",
        "reg_city" => "νοσλχα",
        "reg_street" => "ώεςενυϋλιξσλαρ",
        "reg_house" => "27",
        "reg_build" => "97",
        "reg_housing" => "2",
        "reg_flat" => "1",
        "email" => "ZAA@MAIL.RU",
        "order_given" => "",
        "phone" => "1111",
        "sms_send" => "0000-00-00 00:00:00",
        "sms_sender" => "",
        "sms_get_time" => "0000-00-00 00:00:00",
        "line_owner" => "δεςφατεμψ μιξιι",
        "metro_id" => "αλαλδενιώεσλαρ",
        "logistic" => "τλ"
        );
*/
        foreach($a as &$l)
            $l = strtoupper($l);
        unset($l);


#στςιν ιξτεςξετ[v]
$wd->write($im,"v", 388,121);
#στςιν τχ[v]
$wd->write($im, (strlen($a["passp_num"]) ? "v": "_"),604,121);
#σεςιρ πασποςτα[4]
$wd->write($im, ($a["passp_series"] ? $a["passp_series"] : "____"),374,232);
#ξονες πασποςτα[6]
$wd->write($im,($a["passp_num"] ? $a["passp_num"] : "______"),615,232);
#ξονες μιγεχοηο σώετα[8-2]
$wd->write($im, $a["acc_no"] ? $a["acc_no"] : "________-__",300,349);
#ξονες ϊαρχλι[10]
$wd->write($im, $a["req_no"] ? $a["req_no"] : "__________",235,510);
#τεμεζοξ υσταξοχλι[3-7]
$wd->write($im,clearPhone($a["connum"]),235,551);
/*
#ξαμιώιε οθςαξω ξα τεμεζοξε: δα[v]
$wd->write(^if(^st.pos[ΔΜΡ ΜΙΞΙΚ Σ ΣΙΗΞΑΜΙΪΑΓΙΕΚ,390,617);
*/
$wd->write($im, "_",390,617);
#ξαμιώιε οθςαξω ξα τεμεζοξε: ξετ[v]
$wd->write($im, "_",528,617);
#οτλυδα υϊξαμι οβ υσμυηαθ: στςιν ιξτεςξετ[v]
$wd->write($im, "_",435,1028);
#οτλυδα υϊξαμι οβ υσμυηαθ: στςιν τχ[v]
$wd->write($im, "_",651,1028);
#οτλυδα υϊξαμι οβ υσμυηαθ: ιξτεςξετ[v]
$wd->write($im, "_",51,1064);
#οτλυδα υϊξαμι οβ υσμυηαθ: νετςο[v]
$wd->write($im, "_",189,1064);
#οτλυδα υϊξαμι οβ υσμυηαθ: τχ[v]
$wd->write($im, "_",297,1064);
#οτλυδα υϊξαμι οβ υσμυηαθ: ςαδιο[v]
$wd->write($im, "_",376,1064);
#οτλυδα υϊξαμι οβ υσμυηαθ: πςεσσα[v]
$wd->write($im, "_",485,1064);
#οτλυδα υϊξαμι οβ υσμυηαθ: ϊξαλονωε[v]
$wd->write($im, "_",601,1064);
#οτλυδα υϊξαμι οβ υσμυηαθ: δς.[v]
$wd->write($im, "_",743,1064);

//^blank.font[0123456789αβχηδε³φϊικλμνξοπςστυζθγώϋύÿωψόΰρABCDEFGHIJKLMNOPQRSTUVWXYZ.,!?:^;()<>@#^$%&*"_-+=/\|;/admin/img/simbols.gif](0;10)
#χμαδεμεγ λοξτςαλτα
$wt->write($im, $a["fio"], 252,181);
#λεν χωδαξ
$wt->write($im, $a["passp_whos_given"], 180, 271);
#λοηδα χωδαξ
$wt->write($im, $a["passp_when_given"], 191, 311); 
#δατα ςοφδεξιρ
$wt->write($im, $a["passp_birthday"], 576, 311); 
#ταςιζξωκ πμαξ
$wt->write($im, $tarif, 208, 389);
#ζ.ι.ο. χμαδεμψγα μιξιι
$wt->write($im, $a["line_owner"], 309, 648); 
#ηοςοδ
$wt->write($im, $a["reg_city"], 110, 782); 
#υμιγα
$wt->write($im, $a["reg_street"], 360, 782); 
#δον
$wt->write($im, $a["reg_house"], 89, 815); 
#στςοεξιε
$wt->write($im, $a["reg_build"], 310, 815); 
#λοςπυσ
$wt->write($im, $a["reg_housing"], 498, 815); 
#λχαςτιςα
$wt->write($im, $a["reg_flat"], 708, 815); 
#λοξταλτξοε μιγο
$wt->write($im, $a["fio"], 227, 930); 
#τεμεζοξ δμρ σχρϊι
$wt->write($im, $a["phone"], 249, 962); 
#E-MAIL
$wt->write($im, $a["email"], 122, 993); 
#δατα
$wt->write($im, date("d.m.Y"), 697, 1188); 


// Output to browser
header('Content-type: image/png');

imagepng($im);
imagedestroy($im);


function clearPhone($n)
{
    $n = ereg_replace("^\d", "", $n);
    switch(strlen($n)){ 
        case 7: $n = "495-".$n; break;
        case 10: $n = substr($n, 0,3)."-".substr($n,3,7); break;
        case 11: $n = substr($n, 1, 3)."-".substr($n, 4,7); break;
        default: $n = "___________"; break;
    }
    return $n;
}

class gd_stringWriter{
    protected $img = null;
    public function __construct()
    {
        $this->fileName = path.$this->fileName;
        if(!(file_exists($this->fileName) && is_readable($this->fileName)))
            die("ξΕΧΟΪΝΟΦΞΟ ΠΟΜΥήΤΨ ΔΟΣΤΥΠ Λ ΖΑΚΜΥ ΫÒΙΖΤΑ ".$this->fileName);

        $this->img = imagecreatefromgif($this->fileName) or die("ΟΫΙΒΛΑ ΣΟΪΔΑΞΙΡ ΫÒΙΖΤΑ");
    }

    public function write(&$img, $str, $fromX, $fromY)
    {
        $count = 0;
        $str = strtoupper($str);
        for($i = 0; $i < strlen($str); $i++)
        {
            $s = substr($str, $i,1);
            if(($pos = array_search($s, $this->idx)) !== false){
                imagecopy($img, $this->img, $fromX+(($this->size-$this->offset)*$count), $fromY, 0, $pos*$this->size, $this->size, $this->size);
            }
            $count++;
        }
    }
}

class gd_digitalWriter extends gd_stringWriter {
    public $fileName = "cifri.gif";
    public $idx = array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "-", "V","_");
    public $size = 30;
    public $offset = 0;
}

class gd_textWriter extends gd_stringWriter {
    public $fileName = "simbols.gif";
    public $idx = array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "α", "β", "χ", "η", "δ", "ε", "³", "φ", "ϊ", "ι", "κ", "λ", "μ", "ν", "ξ", "ο", "π", "ς", "σ", "τ", "υ", "ζ", "θ", "γ", "ώ", "ϋ", "ύ", "ÿ", "ω", "ψ", "ό", "ΰ", "ρ", "A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z", ".", ",", "!", "?", ":", ";", "(", ")", "<", ">", "@", "#", "$", "%", "&", "*", "\"", "_", "-", "+", "=", "/", "\\", "|");
    public $size = 20;
    public $offset = 10;
}

//^blank.font[0123456789αβχηδε³φϊικλμνξοπςστυζθγώϋύÿωψόΰρABCDEFGHIJKLMNOPQRSTUVWXYZ.,!?:^;()<>@#^$%&*"_-+=/\|;/admin/img/simbols.gif](0;10)
