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

// Create new PHPExcel object
$objReader = PHPExcel_IOFactory::createReader('Excel2007');
$objPHPExcel = $objReader->load("templates/template_ods.xlsx");


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
    
    select a.agecli age,a.cli,a.ncp,a.nomrest,a.dev,a.princ,a.susp,c.maut,c.debut,c.fin,a.ddd,a.ddc,a.NDAYSARR,a.newcla from
(select c.age agecli,a.age,a.cli,a.ncp,c.nomrest,a.dev,b.engod princ,b.intod susp,a.ddd,a.ddc,d.NDAYSARR,d.newcla from  prod.misclassifbnr b,prod.bkcom a,prod.bkcli c,prod.misnewclassfinal d where a.cli=b.cli and a.cli=c.cli and b.cli=d.cli and b.engod <> 0 
and (a.cha like '12%' or a.cha like '201%' or  a.cha like '203%' or a.cha='219010' or a.cha='291100' or a.cha='291200' or a.cha='291300')) a
left outer join 
(select a.age,a.dev,a.ncp,a.fin,a.maut,a.debut from PROD.bkautc a where eta in ('VA','VF','FO') and 
a.fin=(select max(fin) from prod.bkautc b where b.ncp=a.ncp and b.age=a.age and b.dev=a.dev and  eta in ('VA','VF','FO') ) ) c on a.age=c.age and a.dev=c.dev and a.ncp=c.ncp

     )
    
    order by cli";

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
$maut = array();
$debut = array();
$fin = array();
$ddd = array();
$ddc = array();
$ndaysarr = array();
$newcla = array();
$principal = array();
$suspense = array();

if ($stmt->execute(array())){
    $i = 0;
    $j = 0;
    $prev_cli = '';
    $prev_princ = 0;
    $prev_susp = 0;
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $age[] = (string)$row['AGE'];
        $cli[] = (string)trim($row['CLI']);
        $ncp[] = (string)$row['NCP'];
        $nomrest[] = (string)$row['NOMREST'];
        $dev[] = (int)$row['DEV'];  

        if($prev_cli == (string)trim($row['CLI']) && $prev_princ == $row['PRINC']){
            $principal[] =  0;
        } else{
            $principal[] =  (float)$row['PRINC'];
        } 
        
        if($prev_cli == (string)trim($row['CLI']) && $prev_susp == $row['SUSP']){
            $suspense[] =  0;
        } else{
            $suspense[] =  (float)$row['SUSP'];
        } 
        
        $maut[] = (float)$row['MAUT'];
        $debut[] = $row['DEBUT'];
        $fin[] = $row['FIN'];
        $ddd[] =  $row['DDD'];      
        $ddc[] =  $row['DDC'];      
        $ndaysarr[] =  (float)$row['NDAYSARR'];      
        $newcla[] =  (int)$row['NEWCLA'];  

        $prev_cli = (string)trim($row['CLI']);
        $prev_princ = (float)$row['PRINC'];
        $prev_susp = (float)$row['SUSP'];
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
    array_chunk($principal, 1),
    NULL,
    'F2'
);
$objPHPExcel->setActiveSheetIndex(0)
->fromArray(
    array_chunk($suspense, 1),
    NULL,
    'G2'
);
$objPHPExcel->setActiveSheetIndex(0)
->fromArray(
    array_chunk($maut, 1),
    NULL,
    'H2'
);
$objPHPExcel->setActiveSheetIndex(0)
->fromArray(
    array_chunk($debut, 1),
    NULL,
    'I2'
);
$objPHPExcel->setActiveSheetIndex(0)
->fromArray(
    array_chunk($fin, 1),
    NULL,
    'J2'
);
$objPHPExcel->setActiveSheetIndex(0)
->fromArray(
    array_chunk($ddd, 1),
    NULL,
    'K2'
);
$objPHPExcel->setActiveSheetIndex(0)
->fromArray(
    array_chunk($ddc, 1),
    NULL,
    'L2'
);
$objPHPExcel->setActiveSheetIndex(0)
->fromArray(
    array_chunk($ndaysarr, 1),
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
