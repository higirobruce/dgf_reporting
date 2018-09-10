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
// $objPHPExcel = $objReader->load("templates/template_ods_ifrs.xlsx");


// // Set document properties
// $objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
// ->setLastModifiedBy("Maarten Balliauw")
// ->setTitle("Office 2007 XLSX Test Document")
// ->setSubject("Office 2007 XLSX Test Document")
// ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
// ->setKeywords("office 2007 openxml php")
// ->setCategory("Test result file");

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
$query = "SELECT * from CLEARINGUSER.IFRS9_ODS where prod.month(dco) = ? and prod.year(dco)= ?";

$stmt = $pdo->prepare($query); 
$age_name = array();
$ncp = array();
$currency = array();
$cli_id = array();
$names = array();
$maut = array();
$balance = array();
$inter_susp = array();
$start_date = array();
$end_date = array();
$tau = array();
$class = array();
$ndaysarr = array();
$net_a_provisioner = array();

if ($stmt->execute(array(
    $month, $year
))){
    $i = 0;
    $j = 0;
    $prev_inter = 0;
    $prev_cli = '';
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $names[] = (string)trim($row['NAMES']);
    }
}

var_dump($names);