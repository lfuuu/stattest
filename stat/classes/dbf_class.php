<?php
/**
 * DBF reader Class v0.04  by Faro K Rasyid (Orca)
 * orca75_at_dotgeek_dot_org
 * v0.05 by Nicholas Vrtis
 * vrtis_at_vrtisworks_dot_com
 * 1) changed to not read in complete file at creation.
 * 2) added function to read individual rows
 * 3) added support for Memo fields in dbt files.
 * 4) See: http://www.clicketyclick.dk/databases/xbase/format/dbf.html#DBF_STRUCT
 *    for some additional information on XBase structure...
 * 5) NOTE: the whole file (and the memo file) is read in at once.  So this could
 *    take a lot of memory for large files.
 * 
 * Input		: name of the DBF( dBase III plus) file
 * Output	:	- dbf_num_rec, the number of records
 * 			- dbf_num_field, the number of fields
 * 			- dbf_names, array of field information ('name', 'len', 'type')
 * 
 * Usage	example:
 * $file= "your_file.dbf";//WARNING !!! CASE SENSITIVE APPLIED !!!!!
 * $dbf = new dbf_class($file);
 * $num_rec=$dbf->dbf_num_rec;
 * $num_field=$dbf->dbf_num_field;
 * 
 * for($i=0; $i<$num_rec; $i++){
 *     $row = $dbf->getRow($i);
 * 	for($j=0; $j<$num_field; $j++){
 * 		echo $row[$j].' ');
 * 	}
 * 	echo('<br>');
 * }
 * 
 * Thanks to :
 * - Willy
 * - Miryadi
 * 
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 * 
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
 * See the GNU  Lesser General Public License for more details.
 *   
 */ 
class dbf_class {
		
	/**
	 * @var int $dbf_num_rec Number of records in the file
	 */
    var $dbf_num_rec;
	/**
	 * @var int $dbf_num_field Number of columns in each row
	 */
    var $dbf_num_field;
	/**
	 * @var array $dbf_names Information on each column ['name'],['len'],['type']
	 */
    var $dbf_names = array();
    
	/**
	 * @var string $_raw The raw input file
	 */
    var $_raw;
	/**
	 * @var int $_rowsize Length of each row
	 */
    var $_rowsize;
	/**
	 * @var int $_hdrsize Length of the header information (offset to 1st record)
	 */
    var $_hdrsize;
	/**
	 * @var string $_memos The raw memo file (if there is one)
	 */
    var $_memos;

    /**
     * Инициализация класса, сохраниние информации о файле и о его содержимом
     * @param string $filename путь к dbf файлу
     */
    function dbf_class($filename) {
        if ( !file_exists($filename)) {
            echo 'Not a valid DBF file !!!'; exit;
        }
        $tail=substr($filename,-4);
        if (strcasecmp($tail, '.dbf')!=0) {
            echo 'Not a valid DBF file !!!'; exit;
        }
				
        $handle = fopen($filename, "r");
        if (!$handle) { echo "Cannot read DBF file"; exit; }
        $filesize = filesize($filename);
        $this->_raw = fread ($handle, $filesize);
        fclose ($handle);

        if(!(ord($this->_raw[0]) == 3 || ord($this->_raw[0]) == 131) && ord($this->_raw[$filesize]) != 26) {
            echo 'Not a valid DBF file !!!'; exit;
        }

        $arrHeaderHex = array();
        for($i=0; $i<32; $i++){
            $arrHeaderHex[$i] = str_pad(dechex(ord($this->_raw[$i]) ), 2, "0", STR_PAD_LEFT);
        }

        $line = 32;

        $this->dbf_num_rec=  hexdec($arrHeaderHex[7].$arrHeaderHex[6].$arrHeaderHex[5].$arrHeaderHex[4]);
        $this->_hdrsize= hexdec($arrHeaderHex[9].$arrHeaderHex[8]);
        
        $this->_rowsize = hexdec($arrHeaderHex[11].$arrHeaderHex[10]);
		$this->dbf_num_field = floor(($this->_hdrsize - $line ) / $line ) ;
				
        for($j=0; $j<$this->dbf_num_field; $j++){
            $name = '';
            $beg = $j*$line+$line;
            for($k=$beg; $k<$beg+11; $k++){
                if(ord($this->_raw[$k])!=0){
                    $name .= $this->_raw[$k];
                }
            }
            $this->dbf_names[$j]['name']= $name;
            $this->dbf_names[$j]['len']= ord($this->_raw[$beg+16]);
            $this->dbf_names[$j]['type']= $this->_raw[$beg+11];
        }
        if (ord($this->_raw[0])==131) { 
            $tail=substr($tail,-1,1);
            if ($tail=='F'){
                $tail='T';
            } else {
                $tail='t';
            }
            $memoname = substr($filename,0,strlen($filename)-1).$tail;
            $handle = fopen($memoname, "r");
            if (!$handle) { echo "Cannot read DBT file"; exit; }
            $filesize = filesize($memoname);
            $this->_memos = fread ($handle, $filesize);
            fclose ($handle);
        }
    }
    /**
     * Возвращает запись из файла
     * @param int $recnum номер записи в файле
     */
    function getRow($recnum) {
        $memoeot = chr(26).chr(26);
        $rawrow = substr($this->_raw,$recnum*$this->_rowsize+$this->_hdrsize,$this->_rowsize);
        $rowrecs = array();
        $beg=1;
        if (ord($rawrow[0])==42) {
            return false;
        }
        for ($i=0; $i<$this->dbf_num_field; $i++) {
            $col=trim(substr($rawrow,$beg,$this->dbf_names[$i]['len']));
            if ($this->dbf_names[$i]['type']!='M') {
                $rowrecs[]=$col;
            } else {
                $memobeg=$col*512;  
                $memoend=strpos($this->_memos,$memoeot,$memobeg);   
                $rowrecs[]=substr($this->_memos,$memobeg,$memoend-$memobeg);
            }
            $beg+=$this->dbf_names[$i]['len'];
        }
        return $rowrecs;
    }
    /**
     * Возвращает запись из файла в виде ассоциативного массива
     * @param int $recnum номер записи в файле
     */
    function getRowAssoc($recnum) {
        $rawrow = substr($this->_raw,$recnum*$this->_rowsize+$this->_hdrsize,$this->_rowsize);
        $rowrecs = array();
        $beg=1;
        if (ord($rawrow[0])==42) {
            return false;   
        }
        for ($i=0; $i<$this->dbf_num_field; $i++) {
            $col=trim(substr($rawrow,$beg,$this->dbf_names[$i]['len']));
            if ($this->dbf_names[$i]['type']!='M') {
                $rowrecs[$this->dbf_names[$i]['name']]=$col;
            } else {
                $memobeg=$col*512;  
                $memoend=strpos($this->_memos,$memoeot,$memobeg);   
                $rowrecs[$this->dbf_names[$i]['name']]=substr($this->_memos,$memobeg,$memoend-$memobeg);
            }
            $beg+=$this->dbf_names[$i]['len'];
        }
        return $rowrecs;
    }
}
?>
