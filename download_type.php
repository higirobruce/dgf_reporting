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
require_once '/Classes/PHPExcel.php';

//Database Connection
$pdo = new PDO('oci:dbname=192.168.0.20:1521/cgbk', 'BHIGIRO', 'ABC123456');  
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Create new PHPExcel object
$objReader = PHPExcel_IOFactory::createReader('Excel2007');
$objPHPExcel = $objReader->load("templates/template.xlsx");


// Set document properties
$objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
							 ->setLastModifiedBy("Maarten Balliauw")
							 ->setTitle("Office 2007 XLSX Test Document")
							 ->setSubject("Office 2007 XLSX Test Document")
							 ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
							 ->setKeywords("office 2007 openxml php")
							 ->setCategory("Test result file");


// Add some data

$query = "SELECT c.dev,l.tind ex_rate,sum(c.num) num,sum(c.sde) sde,sum(c.sdecv) sdecv from (
    select i.tcli,a.dev,count(a.sdecv) num,sum(a.sde) sde,sum(a.sdecv) sdecv from prod.bksld  a 
    left join prod.bkcli i on i.cli=a.cli
    where  (cha like ? 
        or cha like ? 
        or cha like ? 
        or cha like ?
        or cha like ? 
        or cha like ? 
        or cha like ? 
        or cha like ? 
        or cha = ? 
        or cha = ?
        or cha like ?) 
        and sde > ? 
        and cha not like ? 
        and cha not like ? 
        and cha not like ? 
        and cha not like ?
        and cha not like ?  
        and a.dco = ?
        group by i.tcli,a.dev
        UNION ALL
        select i.tcli,a.dev,count(a.sdecv) num,sum(a.sde) sde,sum(a.sdecv) sdecv from prod.bksld a
        left join prod.bkcli i on i.cli=a.cli
        where (cha like ? 
            or cha like ? 
            or cha like ? 
            or cha like ?)
            and cha != ? 
            and cha != ? 
            and a.dco = ?  
            and a.sde <> ?
            group by i.tcli,a.dev) c
            join prod.bktau l on l.dev=c.dev 
            and l.dco = ?
            group by  c.dev, l.tind order by c.dev ASC";

$stmt = $pdo->prepare($query); 
$arr = array();
$ret = array();
if(isset($_POST['ndt2']))
{
    $dco = $_POST['ndt2'];
}else{
    $dco = '30/6/2017';
}

$dev = '124';
$numbers = array();
$sde = array();
$data = array();
if ($stmt->execute(array(
    '20%','12%','18%','21%','22%','23%','24%',
    '25%','148635','148636','290%','0','208',
    '219%','229%','239%','249%',$dco,'27%','28%',
    '14%','208%','148635','148636',$dco,0,$dco
    ))){

        $i = 0;
        $j = 0;
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $numbers[] = $row['NUM'];
            $sde[] = $row['SDE'];            
        }
               

}

$objPHPExcel->setActiveSheetIndex(7)
            ->fromArray(
                array_chunk($numbers, 1),  // The data to set
                NULL,        // Array values with this value will not be set
                'C3'         // Top left coordinate of the worksheet range where
                            //    we want to set these values (default is A1)
            );


// $objPHPExcel->setActiveSheetIndex(4)
//             ->setCellValue('C12', $sde[0])
//             ->setCellValue('C13', $sde[2])
//             ->setCellValue('C14', $sde[3])
//             ->setCellValue('C15', $sde[1]);

// Rename worksheet
$objPHPExcel->getActiveSheet()->setTitle('CHA');


// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);


// Redirect output to a client’s web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="01simple.xlsx"');
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
