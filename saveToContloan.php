<?php

//Database Connection
$pdo = new PDO('oci:dbname=192.168.0.20:1521/cgbk', 'CLEARINGUSER2017', 'CLEARINGUSER2017coge');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

//dco
if (isset($_GET['month'])) {
    $month = $_GET['month'];
    $year = $_GET['year'];
} else {
    $month = 6;
    $year = 2020;
}

$q_dco = $pdo->prepare("SELECT TO_CHAR(max(dco),'DD-MON-YYYY') d, prod.month(max(dco)) mon from prod.bksld where cha like '205%' and prod.month(dco)=? and prod.year(dco)=?");
$q_dco->execute(array($month, $year));
$r = $q_dco->fetch(PDO::FETCH_ASSOC);
$dco = $r['D'];


$q_totals = $pdo->prepare("SELECT sum(sdecv) total_balance,
sum(interest_due_lcy) total_interest_due,
sum(capital_due_lcy) total_capital_due,
sum(prov_held) total_prov_held,
sum(loan_includ_interest) total_loan_includ_interest,
sum(regulatory_prov) total_regulatory_prov,
sum(interest_susp) total_interest_susp,
sum(other_charges) total_charges
from clearinguser.ifrs9_tloans where dco=?");

$q_totals->execute(array($dco));

$r = $q_totals->fetch(PDO::FETCH_ASSOC);

$response = new stdClass();

$response->balance = $r['TOTAL_BALANCE'];
$response->interest_due = $r['TOTAL_INTEREST_DUE'];
$response->capital_due = $r['TOTAL_CAPITAL_DUE'];
$response->prov_held = $r['TOTAL_PROV_HELD'];
$response->total_loan_includ_interest = $r['TOTAL_LOAN_INCLUD_INTEREST'];
$response->total_regulatory_prov = $r['TOTAL_REGULATORY_PROV'];
$response->total_interest_susp = $r['TOTAL_INTEREST_SUSP'];
$response->total_charges = $r['TOTAL_CHARGES'];


$q_totals_writtenOff = $pdo->prepare("SELECT sum(sdecv) total_balance
from clearinguser.ifrs9_tloans where dco=? and doc_type=?");
$q_totals_writtenOff->execute(array($dco,'WF'));
$r = $q_totals_writtenOff->fetch(PDO::FETCH_ASSOC);
$response->written_off = $r['TOTAL_BALANCE'];

$q_totals_offBal = $pdo->prepare("SELECT sum(sdecv) total_balance
from clearinguser.ifrs9_tloans where dco=? and doc_type=?");
$q_totals_offBal->execute(array($dco,'OF'));
$r = $q_totals_offBal->fetch(PDO::FETCH_ASSOC);

$response->off_balancesheet = $r['TOTAL_BALANCE'];



echo json_encode($response);