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
$pdo = new PDO('oci:dbname=192.168.0.20:1521/cgbk', 'CLEARINGUSER2017', 'CLEARINGUSER2017coge');  
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Create new PHPExcel object
$objReader = PHPExcel_IOFactory::createReader('Excel2007');
$objPHPExcel = $objReader->load("templates/template_loans.xlsx");


// Set document properties
$objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
							 ->setLastModifiedBy("Maarten Balliauw")
							 ->setTitle("Office 2007 XLSX Test Document")
							 ->setSubject("Office 2007 XLSX Test Document")
							 ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
							 ->setKeywords("office 2007 openxml php")
							 ->setCategory("Test result file");


// CHA
$query = "SELECT * from (
    select a.age,a.cli,b.ncp,a.nomrest,b.dev,b.mon,a.res,b.map,b.DPEC,b.DDEC,b.AMO_IMP,b.INT_IMP,b.NDAYS,a.newcla from 
(select c.age,c.cli,c.nomrest,(a.englo+a.intlo) res,b.NDAYSARR,b.newcla from prod.misclassifbnr a,prod.bkcli c,prod.misnewclassfinal b where a.cli=c.cli and a.cli=b.cli) a
left join 
(select a.cli,b.ncp,b.dev,c.mon,c.map,c.DPEC,c.DDEC,c.AMO_IMP,c.INT_IMP,c.NDAYS from prod.tloanglobal c,prod.bkcptprt b,prod.bkdosprt a where c.eve=a.eve and c.ave=a.ave and c.eve=b.eve and c.ave=B.AVE and b.nat='004') b
on a.cli=b.cli
where a.res <> 0 and (b.ncp is not null or (b.ncp is null and a.cli not in
(select b.cli from prod.bkautc a,prod.bkcom b,prod.misnewclassfinal c where a.age=b.age and a.dev=b.dev and a.ncp=b.ncp and b.cli=c.cli and a.ope=601 and 
eta in ('VA','FO','VF') and a.fin > '31-MAY-18' 
)))
union all
select a.age,a.cli,b.ncp,a.nomrest,b.dev,b.maut,a.res,0,b.debut,b.fin,0,0,b.NDAYSarr,a.newcla from 
(select c.age,c.cli,c.nomrest,(a.englo+a.intlo) res,b.NDAYSARR,b.newcla from prod.misclassifbnr a,prod.bkcli c,prod.misnewclassfinal b where a.cli=c.cli and a.cli=b.cli) a
inner join 
(select a.ncp,a.dev,a.maut,a.debut,a.fin,c.ndaysarr,b.cli from prod.bkautc a,prod.bkcom b,prod.misnewclassfinal c where a.age=b.age and a.dev=b.dev and a.ncp=b.ncp and b.cli=c.cli and a.ope=601 and 
eta in ('VA','FO','VF') and a.fin > '31-MAY-18' ) b on a.cli=b.cli
)
order by cli
";

$stmt = $pdo->prepare($query); 
$arr = array();
$ret = array();
if(isset($_POST['ndt2']))
{
    $dco = $_POST['ndt2'];
}else{
    $dco = '30/6/2017';
}

$age = array();
$cli = array();
$ncp = array();
$nomrest = array();
$dev = array();
$mon = array();
$res = array();
$map = array();
$dpec = array();
$ddec = array();
$amo_imp = array();
$int_imp = array();
$ndays = array();
$newcla = array();


if ($stmt->execute(array())){
    $i = 0;
    $j = 0;
    $prev_cli = '';
    $prev_res = 0;
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $age[] = (string)$row['AGE'];
        $cli[] = (string)trim($row['CLI']);
        $ncp[] = (string)$row['NCP'];
        $nomrest[] = (string)$row['NOMREST'];
        $dev[] = (int)$row['DEV'];  

        if($prev_cli == (string)trim($row['CLI']) && $prev_res == $row['RES']){
            $res[] =  0;
        } else{
            $res[] =  (float)$row['RES'];
        } 
        
        $mon[] = (float)$row['MON'];
        $map[] = $row['MAP'];
        $dpec[] =  $row['DPEC'];      
        $ddec[] =  $row['DDEC'];      
        $ndays[] =  (float)$row['NDAYS'];      
        $newcla[] =  (int)$row['NEWCLA'];  
        $amo_imp[] = (float)$row['AMO_IMP'];
        $int_imp[] = (float)$row['INT_IMP'];

        $prev_cli = (string)trim($row['CLI']);
        $prev_res = (float)$row['RES'];
    }
}


$objPHPExcel->setActiveSheetIndex(0)
->fromArray(
    array_chunk($age, 1),
    NULL,
    'A2'
);
$objPHPExcel->setActiveSheetIndex(0)
->fromArray(
    array_chunk($cli, 1),
    NULL,
    'B2'
);
$objPHPExcel->setActiveSheetIndex(0)
->fromArray(
    array_chunk($ncp, 1),
    NULL,
    'C2'
);
$objPHPExcel->setActiveSheetIndex(0)
->fromArray(
    array_chunk($nomrest, 1),
    NULL,
    'D2'
);
$objPHPExcel->setActiveSheetIndex(0)
->fromArray(
    array_chunk($dev, 1),
    NULL,
    'E2'
);

$objPHPExcel->setActiveSheetIndex(0)
->fromArray(
    array_chunk($mon, 1),
    NULL,
    'F2'
);
$objPHPExcel->setActiveSheetIndex(0)
->fromArray(
    array_chunk($res, 1),
    NULL,
    'G2'
);
$objPHPExcel->setActiveSheetIndex(0)
->fromArray(
    array_chunk($map, 1),
    NULL,
    'H2'
);
$objPHPExcel->setActiveSheetIndex(0)
->fromArray(
    array_chunk($dpec, 1),
    NULL,
    'I2'
);
$objPHPExcel->setActiveSheetIndex(0)
->fromArray(
    array_chunk($ddec, 1),
    NULL,
    'J2'
);
$objPHPExcel->setActiveSheetIndex(0)
->fromArray(
    array_chunk($amo_imp, 1),
    NULL,
    'K2'
);
$objPHPExcel->setActiveSheetIndex(0)
->fromArray(
    array_chunk($int_imp, 1),
    NULL,
    'L2'
);
$objPHPExcel->setActiveSheetIndex(0)
->fromArray(
    array_chunk($ndays, 1),
    NULL,
    'M2'
);
$objPHPExcel->setActiveSheetIndex(0)
->fromArray(
    array_chunk($newcla, 1),
    NULL,
    'N2'
);

// Rename worksheet



// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);


// Redirect output to a client’s web browser (Excel2007)
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Report.xlsx');
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
