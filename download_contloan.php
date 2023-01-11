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
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt    LGPL
 * @version    ##VERSION##, ##DATE##
 */

/** Error reporting */
error_reporting(E_ALL);
ini_set('display_errors', true);
ini_set('display_startup_errors', true);
date_default_timezone_set('Europe/London');

define('EOL', (PHP_SAPI == 'cli') ? PHP_EOL : '<br />');

if (PHP_SAPI == 'cli') {
    die('This example should only be run from a Web Browser');
}

/** Include PHPExcel */
require_once 'Classes/PHPExcel.php';

//Database Connection
$pdo = new PDO('oci:dbname=192.168.0.20:1521/cgbk', 'CLEARINGUSER2017', 'CLEARINGUSER2017coge');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


// CHA
$query = "SELECT 
'RW' Country,
'030' LE_BOOK,
prod.year(dco)||'_'||prod.month(dco) year_month,
to_char(CONTRACT_ID) CONTRACT_ID,
CLI customer_id,
NVL(DECODE(NEWCLA,'1','NL','2','WL','3','SL','4','DL','5','LL','6','WO'),'NL') perfomance_class,
nvl(LOAN_AMOUNT,0) DISBURSSED_AMOUNT,
nvl(SDE,0)  PRIN_OUTSTANDING_AMT_FCY ,
nvl(SDECV,0)  PRIN_OUTSTANDING_AMT_LCY ,
nvl(INTEREST_DUE_FCY,0) INTEREST_DUE_FCY ,
nvl(INTEREST_DUE_LCY,0) INTEREST_DUE_LCY ,
nvl(REGULATORY_PROV,0)  REGULATORY_PROVISION ,
nvl(PROV_HELD,0) PROVISION_HELD,
dco DATE_OF_PROVISION,
nvl(LOAN_INCLUD_INTEREST,0) LOAN_INCLUD_INTEREST,
0 OTHER_CR_PENALTIES,
nvl(OTHER_CHARGES,0) OTHER_CHARGES,
nvl(INTEREST_SUSP,0) SUSPENSE_INTEREST,
 NVL(DECODE(REP_FREQ,'MTH','MTH','HYR','HYR','QTR','QTR','6 TIMES A YEAR','QTR','IDF','IDF'),'NA') REPAYMENT_FREQUENCY,
nvl(INSTALLMENT,0) EMI_AMOUNT,
DATE_PAST_DUE,
nvl(DUE_AMOUNT,0) DUE_AMOUNT ,
case when GRACE_PERIOD_MONTHS - 31 <=0 then 0 
else nvl(GRACE_PERIOD_MONTHS,0)
end as GRACE_PERIOD_ACCORDED,
nvl(INSTALMENTS_IN_ARREARS,0) INSTALMENTS_IN_ARREARS,
nvl(NUM_OF_INSTALMENTS,0) NUM_OF_INSTALMENTS,
nvl(TOTAL_INSTALMENTS_PAID,0) TOTAL_INSTALMENTS_PAID, 
nvl(TOTAL_INSTALMENTS_OUTSTANDING,0) TOTAL_INSTALMENTS_OUTSTANDING,
ncp ACCOUNT_NUMBER,
cha chapitre,
newcla

  from clearinguser.ifrs9_tloans";

$stmt = $pdo->prepare($query);

$country = array();
$le_book = array();
$contract_id = array();
$customer_id = array();
$perfomance_class = array();
$disburssed_amount = array();
$prin_outstanding_amt_fcy = array();
$prin_outstanding_amt_lcy = array();
$interest_due_fcy = array();
$interest_due_lcy = array();
$regulatory_provision = array();
$provision_held = array();
$date_of_provision = array();
$loan_includ_interest = array();
$other_cr_penalties = array();
$other_charges = array();
$suspense_interest = array();
$repayment_frequency = array();
$emi_amount = array();
$date_past_due = array();
$due_amount = array();
$grace_period_accorded = array();
$instalments_in_arrears = array();
$num_of_instalments = array();
$total_instalments_paid = array();
$total_instalments_outstanding = array();

$result = [];

if ($stmt->execute(array(
))) {
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

        $tempObj = new stdClass();

        $tempObj->COUNTRY = $row['COUNTRY'];
        $tempObj->LE_BOOK = $row['LE_BOOK'];
        $tempObj->YEAR_MONTH = $row['YEAR_MONTH'];
        $tempObj->CONTRACT_ID = (string) $row['CONTRACT_ID'];
        $tempObj->CUSTOMER_ID = $row['CUSTOMER_ID'];
        $tempObj->PERFOMANCE_CLASS = $row['PERFOMANCE_CLASS'];
        $tempObj->DISBURSSED_AMOUNT = abs((float)$row['DISBURSSED_AMOUNT']);
        $tempObj->PRIN_OUTSTANDING_AMT_FCY = (float) $row['PRIN_OUTSTANDING_AMT_FCY'];
        $tempObj->PRIN_OUTSTANDING_AMT_LCY = (float)$row['PRIN_OUTSTANDING_AMT_LCY'];
        $tempObj->INTEREST_DUE_FCY = (float)$row['INTEREST_DUE_FCY'];
        $tempObj->INTEREST_DUE_LCY = (float)$row['INTEREST_DUE_LCY'];
        $tempObj->REGULATORY_PROVISION = (float)$row['REGULATORY_PROVISION'];
        $tempObj->PROVISION_HELD = (float)$row['PROVISION_HELD'];
        $tempObj->DATE_OF_PROVISION = (float)$row['DATE_OF_PROVISION'];
        $tempObj->LOAN_INCLUD_INTEREST = (float)$row['LOAN_INCLUD_INTEREST'];
        $tempObj->OTHER_CR_PENALTIES = (float)$row['OTHER_CR_PENALTIES'];
        $tempObj->OTHER_CHARGES = (float)$row['OTHER_CHARGES'];
        $tempObj->SUSPENSE_INTEREST = (float)$row['SUSPENSE_INTEREST'];
        $tempObj->REPAYMENT_FREQUENCY = $row['REPAYMENT_FREQUENCY'];
        $tempObj->EMI_AMOUNT = (float)$row['EMI_AMOUNT'];
        $tempObj->DATE_PAST_DUE = $row['DATE_PAST_DUE'];
        $tempObj->DUE_AMOUNT = (float)$row['DUE_AMOUNT'];
        $tempObj->GRACE_PERIOD_ACCORDED = (float)$row['GRACE_PERIOD_ACCORDED'];
        $tempObj->INSTALMENTS_IN_ARREARS = (float)$row['INSTALMENTS_IN_ARREARS'];
        $tempObj->NUM_OF_INSTALMENTS = (float)$row['NUM_OF_INSTALMENTS'];
        $tempObj->TOTAL_INSTALMENTS_PAID = (float)$row['TOTAL_INSTALMENTS_PAID'];
        $tempObj->TOTAL_INSTALMENTS_OUTSTANDING = (float)$row['TOTAL_INSTALMENTS_OUTSTANDING'];
        $tempObj->ACCOUNT_NUMBER = $row['ACCOUNT_NUMBER'];
        $tempObj->CHAPITRE = $row['CHAPITRE'];

        $result[] = $tempObj;
       
    }
}


$res = new stdClass();



$res->data = $result;

echo(json_encode($res));