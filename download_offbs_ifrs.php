<?php
/**
 * PHPExcel
 *
 * Copyright (c) 2006 - 2015 PHPExcel
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category   PHPExcel
 * @package    PHPExcel
 * @copyright  Copyright (c) 2006 - 2015 PHPExcel (http://www.codeplex.com/PHPExcel)
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt	LGPL
 * @version    ##VERSION##, ##DATE##
 */

/** Error reporting */
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
date_default_timezone_set('Europe/London');

define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : '<br />');

if (PHP_SAPI == 'cli')
	die('This example should only be run from a Web Browser');

/** Include PHPExcel */
require_once 'Classes/PHPExcel.php';

//Database Connection
$pdo = new PDO('oci:dbname=192.168.0.20:1521/cgbk', 'BRUCE', 'BRUCE123');  
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

//dco

if(isset($_POST['month'])){
    $month = $_POST['month'];
    $year = $_POST['year'];
} else{
    $month = 6;
    $year = 2018;
}

$q_dco = $pdo->prepare("SELECT TO_CHAR(max(dco),'DD-MON-YYYY') d from prod.bksld where cha like '205%' and prod.month(dco)=? and prod.year(dco)=?");

$q_dco->execute(array($month,$year));

$r = $q_dco->fetch(PDO::FETCH_ASSOC);
$dco = $r['D'];


// CHA
$query = "SELECT 
distinct
a.age,
a.cha,
A.NCP,
a.caution_id,
a.cli,
a.names,
b.tpc caution_type,
z.libe caution_type_lib,
a.beneficiary,
a.currency,
b.mon amount,
sum(a.lcy_balance) balance,
b.dco contract_start_date,
b.dva value_date,
chap.lib cha_lib
--a.class_risk,
--a.collateral
from (
select
trim(d.lib) age,
A.NCP,
max(c.eve) caution_id,
b.cli,
b.nomrest names,
c.ben beneficiary,
case when a.dev='646' then 'RWF'
when a.dev = '840' then 'USD'
when a.dev = '978' then 'EUROS'
end as currency,
a.sdecv lcy_balance,
cla.newcla class_risk,
TO_CHAR(TRIM(col_type.lib)) Collateral,
a.cha

from prod.bksld a
join prod.bkcli b on a.cli = b.cli
left join prod.bkcau c on a.ncp = c.ncpe and c.dco<=?--and rownum<=1
left join prod.MISNEWCLASSFINAL cla on cla.CLI = a.cli
left join (
select cli, WMSYS.WM_CONCAT(trim(lib)) lib
    from (
    select distinct bgar.cli, bnatg.LIB lib from prod.bkgar bgar, prod.bknatg bnatg
    where bgar.cnat = bnatg.cnat
    ) group by cli

) col_type on col_type.cli = a.cli
join prod.bkage d on d.age = b.age


where a.cha 
in ('925221','924100','924110','924111','924120',
'924121','924130','924131','924200','924210',
'924211','924400','924910','924911','925210',
'925211','925220','925221','925230','925231',
'925900','925910','925911','902100')
and a.dco=? 

group by trim(d.lib), A.NCP, b.cli, b.nomrest, c.ben, 
case when a.dev='646' then 'RWF' when a.dev = '840' then 'USD' when a.dev = '978' then 'EUROS' end, a.sdecv, cla.newcla, TO_CHAR(TRIM(col_type.lib)), a.cha 
--and a.sde<0 

--and a.cli='0054188' 

) a 
left join (select max(eve) eve, ncpe from prod.bkcau group by ncpe) cau_unique on cau_unique.eve = a.caution_id
left join prod.bkcau b on b.eve = cau_unique.eve and b.ncpe=cau_unique.ncpe
left join prod.bktycau z on b.tpc=z.typ 
left join prod.bkchap chap on a.cha = chap.cha

where --a.cli<> '0053833'and A.CLI<>'0069537' AND 
a.lcy_balance <>0 

group by a.age, A.NCP, a.caution_id, a.cli, a.names, 
b.tpc, z.libe, a.beneficiary, a.currency, b.mon, 
b.dco, b.dva,a.cha,chap.lib

order by a.cli asc";

$stmt = $pdo->prepare($query); 
$age = array();
$cha = array();
$ncp = array();
$caution_id = array();
$cli = array();
$names = array();
$caution_type = array();
$caution_type_lib = array();
$beneficiary = array();
$currency = array();
$amount = array();
$balance = array();
$contract_start_date = array();
$value_date = array();
$cha_lib = array();


if ($stmt->execute(array(
    $dco,$dco
))){
    $i = 0;
    $j = 0;
    $prev_balance = 0;
    $prev_cli = '';
    $prev_ncp = '';
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if($prev_ncp == $row['NCP'] && $prev_balance == $row['BALANCE']){

        }else{
            $age[] = $row['AGE'];
            $cha[] = $row['CHA'];
            $ncp[] = $row['NCP'];
            $caution_id[] = $row['CAUTION_ID'];
            $cli[] = $row['CLI'];
            $names[] = $row['NAMES'];
            $caution_type[] = $row['CAUTION_TYPE'];
            $caution_type_lib[] = $row['CAUTION_TYPE_LIB'];
            $beneficiary[] = $row['BENEFICIARY'];
            $currency[] = $row['CURRENCY'];
            $amount[] = $row['AMOUNT'];
            $balance[] = abs($row['BALANCE']);
            $contract_start_date[] = $row['CONTRACT_START_DATE'];
            $value_date[] = $row['VALUE_DATE'];
            $cha_lib[] = $row['CHA_LIB'];
        }

        $prev_balance = $row['BALANCE'];
        $prev_cli = $row['CLI'];
        $prev_ncp = $row['NCP'];
        
        
    }
}

// Create new PHPExcel object
$objReader = PHPExcel_IOFactory::createReader('Excel2007');
$objPHPExcel = $objReader->load("templates/template_offbs_ifrs.xlsx");


// Set document properties
$objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
->setLastModifiedBy("Maarten Balliauw")
->setTitle("Office 2007 XLSX Test Document")
->setSubject("Office 2007 XLSX Test Document")
->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
->setKeywords("office 2007 openxml php")
->setCategory("Test result file");


$objPHPExcel->setActiveSheetIndex(0)
->fromArray(
    array_chunk($age, 1),
    NULL,
    'A6'
);
unset($age);

$objPHPExcel->setActiveSheetIndex(0)
->fromArray(
    array_chunk($cha, 1),
    NULL,
    'B6'
);
unset($cha);

$objPHPExcel->setActiveSheetIndex(0)
->fromArray(
    array_chunk($ncp, 1),
    NULL,
    'C6'
);
unset($ncp);

$objPHPExcel->setActiveSheetIndex(0)
->fromArray(
    array_chunk($caution_id, 1),
    NULL,
    'D6'
);
unset($caution_id);

$objPHPExcel->setActiveSheetIndex(0)
->fromArray(
    array_chunk($cli, 1),
    NULL,
    'E6'
);
unset($cli);

$objPHPExcel->setActiveSheetIndex(0)
->fromArray(
    array_chunk($names, 1),
    NULL,
    'F6'
);
unset($names);

$objPHPExcel->setActiveSheetIndex(0)
->fromArray(
    array_chunk($caution_type, 1),
    NULL,
    'G6'
);
unset($caution_type);

$objPHPExcel->setActiveSheetIndex(0)
->fromArray(
    array_chunk($caution_type_lib, 1),
    NULL,
    'H6'
);
unset($caution_type_lib);

$objPHPExcel->setActiveSheetIndex(0)
->fromArray(
    array_chunk($beneficiary, 1),
    NULL,
    'I6'
);
unset($beneficiary);

$objPHPExcel->setActiveSheetIndex(0)
->fromArray(
    array_chunk($currency, 1),
    NULL,
    'J6'
);
unset($currency);

$objPHPExcel->setActiveSheetIndex(0)
->fromArray(
    array_chunk($amount, 1),
    NULL,
    'K6'
);
unset($amount);

$objPHPExcel->setActiveSheetIndex(0)
->fromArray(
    array_chunk($balance, 1),
    NULL,
    'L6'
);
unset($balance);

$objPHPExcel->setActiveSheetIndex(0)
->fromArray(
    array_chunk($contract_start_date, 1),
    NULL,
    'Q6'
);
unset($contract_start_date);

$objPHPExcel->setActiveSheetIndex(0)
->fromArray(
    array_chunk($value_date, 1),
    NULL,
    'R6'
);
unset($value_date);

$objPHPExcel->setActiveSheetIndex(0)
->fromArray(
    array_chunk($cha_lib, 1),
    NULL,
    'O6'
);
unset($cha_lib);


$months_names = array();

$months_names[0] = 'January';
$months_names[1] = 'February';
$months_names[2] = 'March';
$months_names[3] = 'April';
$months_names[4] = 'May';
$months_names[5] = 'June';
$months_names[6] = 'July';
$months_names[7] = 'August';
$months_names[8] = 'September';
$months_names[9] = 'October';
$months_names[10] = 'November';
$months_names[11] = 'December';


// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);
$objPHPExcel->getActiveSheet()->setCellValue('A1', 'Off Balancesheet as of the end of '. $months_names[$month-1].' '.$year.'.');

// Redirect output to a client’s web browser (Excel2007)
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Off Balancesheet '. $months_names[$month-1] .'-'. $year .'.xlsx');
header('Cache-Control: max-age=0');
// If you're serving to IE 9, then the following may be needed
header('Cache-Control: max-age=1');

// If you're serving to IE over SSL, then the following may be needed
header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
header ('Pragma: public'); // HTTP/1.0


$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save('php://output');
exit;
