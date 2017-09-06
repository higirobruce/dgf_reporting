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
header('Access-Control-Allow-Origin: *');
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
date_default_timezone_set('Europe/London');

define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : '<br />');

if (PHP_SAPI == 'cli')
	die('This example should only be run from a Web Browser');

/** Include PHPExcel */
require_once '/Classes/PHPExcel.php';

//Database Connection
$pdo = new PDO('oci:dbname=192.168.0.20:1521/cgbk', 'BHIGIRO', 'ABC123456');  
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Create new PHPExcel object
$objReader = PHPExcel_IOFactory::createReader('Excel2007');
$objPHPExcel = $objReader->load("templates/example.xlsx");


// Set document properties
$objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
							 ->setLastModifiedBy("Maarten Balliauw")
							 ->setTitle("Office 2007 XLSX Test Document")
							 ->setSubject("Office 2007 XLSX Test Document")
							 ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
							 ->setKeywords("office 2007 openxml php")
							 ->setCategory("Test result file");


// CHA
$query = "SELECT * from prod.bkcli where sext ='M' or sext='F'";

$stmt = $pdo->prepare($query); 
$arr = array();
$ret = array();
if(isset($_POST['ndt2']))
{
    $dco = $_POST['ndt2'];
}else{
    $dco = '30/6/2017';
}

$dev = array();
$cha = array();
$numbers = array();
$sums = array();
$data = array();
if ($stmt->execute(array(

))){
    $i = 0;
    $j = 0;
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $cha[] = (string)$row['CHA'];
        $dev[] = (int)$row['DEV'];
        $numbers[] = (int)$row['NUMBERS'];
        $sums[] = (int)$row['SUMS'];            
    }
}

$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('B4', 'REPORT AS AT '.$_POST['day'].'/'.$_POST['month'].'/'.$_POST['year']);

$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('B26', 'Date: '.date('d/m/Y'));


//QUARTER
$objPHPExcel->setActiveSheetIndex(7)
->fromArray(
    array_chunk($typ_client, 1),
    NULL,        // Array values with this value will not be set
    'AK3'
);
$objPHPExcel->setActiveSheetIndex(7)
->fromArray(
    array_chunk($cat_client, 1),
    NULL,        // Array values with this value will not be set
    'AL3'
);
$objPHPExcel->setActiveSheetIndex(7)
->fromArray(
    array_chunk($num6, 1),
    NULL,        // Array values with this value will not be set
    'AM3'
);
$objPHPExcel->setActiveSheetIndex(7)
->fromArray(
    array_chunk($sde6, 1),
    NULL,        // Array values with this value will not be set
    'AN3'
);
$objPHPExcel->setActiveSheetIndex(7)
->fromArray(
    array_chunk($sdecv6, 1),
    NULL,        // Array values with this value will not be set
    'AO3'
);


// Rename worksheet
$objPHPExcel->getActiveSheet()->setTitle('CHA');


// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);


// Redirect output to a client’s web browser (Excel2007)

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="DGF REPORTING -COGEBANQUE "'.$dco.'.xlsx"');
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
