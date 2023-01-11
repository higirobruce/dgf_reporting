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


// Create new PHPExcel object
$objReader = PHPExcel_IOFactory::createReader('Excel2007');
$objPHPExcel = $objReader->load("templates/template_ods_ifrs.xlsx");

// Set document properties
$objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
    ->setLastModifiedBy("Maarten Balliauw")
    ->setTitle("Office 2007 XLSX Test Document")
    ->setSubject("Office 2007 XLSX Test Document")
    ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
    ->setKeywords("office 2007 openxml php")
    ->setCategory("Test result file");

//Database Connection
$pdo = new PDO('oci:dbname=192.168.0.20:1521/cgbk', 'CLEARINGUSER2017', 'CLEARINGUSER2017coge');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

//dco
if (isset($_POST['hidden_month_od'])) {
    $month = $_POST['hidden_month_od'];
    $year = $_POST['hidden_year_od'];
    print($month);
} else {
    $month = '06';
    $year = '2020';
}


$q_dco = $pdo->prepare("SELECT TO_CHAR(max(dco),'DD-MON-YYYY') d , prod.month(max(dco)) mon, prod.year(max(dco)) yr from prod.bksld where cha like '205%' and prod.month(dco)=? and prod.year(dco)=?");
$q_dco_tau = $pdo->prepare("SELECT TO_CHAR(max(dco),'DD-MON-YYYY') d from prod.bktau where prod.month(dco)=? and prod.year(dco)=?");

$q_dco->execute(array($month, $year));
$q_dco_tau->execute(array($month, $year));

$r = $q_dco->fetch(PDO::FETCH_ASSOC);
$r_tau = $q_dco_tau->fetch(PDO::FETCH_ASSOC);
$dco = $r['D'];
$dco_month = $r['MON'];
$dco_year = $r['YR'];
$dco_tau = $r_tau['D'];

// CHA
$query = "SELECT *
FROM (SELECT a.age_name,
             a.ncp,
             a.currency,
             a.ncp||a.age||a.currency contract_id,
             TRIM(a.cli)                 cli_id,
             a.nomrest                   names,
             case when nvl(aut.maut * taux.tind,0.0)=0 then abs(a.balance)
             else nvl(aut.maut * taux.tind,0.0) end as maut,
             a.balance,
             a.balance_fcy,
             nvl(SUM(intsus.sdecv),0.0)           inter_susp,
             case
               when b.debut is not null then b.debut
               when b.debut is null and (a.class in (1, 2) or a.class is null) then sysdate - 365
               when b.debut is null and a.class in (5) then sysdate - (365 * 2)
               else sysdate - (365 * 1.5)
                 end                     start_date,
             case
               when c.fin is not null then c.fin
               when c.fin is null and (a.class in (1, 2) or a.class is null) then sysdate
               when c.fin is null and a.class in (5) then sysdate - (365)
               else sysdate - (365 / 2)
                 end                     end_date,
             nvl(MAX((15 + k.tau1)), 22) tau,
             nvl(a.class, 1) class,
             nvl(a.ndaysarr, 0) ndaysarr,
             a.cha,
             seg.segment,
             a.age,
             prov_held.sdecv prov_held,
             nvl(col.collat,0) collateral
      FROM (SELECT a.cli,
                   a.ncp,
                   c.nomrest,
                   a.age,
                   TRIM(d.lib) age_name,
                   a.cha,
                   CASE
                     WHEN a.dev = '646' THEN 'RWF'
                     WHEN a.dev = '840' THEN 'USD'
                     WHEN a.dev = '978' THEN 'EUR'
                     WHEN a.dev = '826' THEN 'GBP'
                       END     currency,
                    CASE 
                        WHEN a.dev <> '646' THEN a.sde
                        ELSE 0 END balance_fcy,
                   a.sdecv     balance,
                   b.newcla    class,
                   b.ndaysarr  ndaysarr,
                   a.dev,
                   a.dco
            FROM prod.bksld a
                --    LEFT JOIN prod.misnewclassfinal b ON a.cli = b.cli
                   left join clearinguser.misnewclassfinal_his b on trim(a.cli) = trim(b.cli) and prod.month(b.finmois) = ? and prod.year(b.finmois) = ?
                   JOIN prod.bkcli c ON a.cli = c.cli
                   left join CLEARINGUSER.CLASSIFICATION_HIS cl
                     on trim(c.cli) = trim(cl.cli) and cl.month = ? and cl.year = ?
                   JOIN prod.bkage d ON a.age = d.age
            WHERE --a.cli='0023678' and
                a.dco = ?
              AND
                --a.sde<0 and
                ((a.cha LIKE '201%'
                    OR a.cha LIKE '203%'
                    OR a.cha LIKE '1220%'
                    OR a.cha LIKE '1230%'
                    OR a.cha LIKE '2190%'
                    OR a.cha IN ('291300', '291100', '291200', --
                                 '299010', '201203','291750','291760','291710','291720','291730','291740'))
                   AND a.cha <> '219010'--and cha <> '201203'--credit cards
                    )) a
             LEFT JOIN (SELECT MAX(debut) debut, ncp FROM prod.bkautc GROUP BY ncp) b ON a.ncp = b.ncp
             LEFT JOIN (SELECT MAX(fin) fin, debut, ncp FROM prod.bkautc GROUP BY ncp,
                                                                                  debut) c ON c.ncp = b.ncp
                                                                                                AND b.debut = c.debut
             LEFT JOIN prod.bkautc aut ON aut.ncp = c.ncp
                                            AND aut.debut = c.debut
                                            AND aut.fin = c.fin
             LEFT JOIN prod.bksld intsus ON a.cli = intsus.cli
                                              AND intsus.cha IN ('291115','298200','298330')
                                              AND intsus.sde>0
                                              AND intsus.dco = ?
             LEFT JOIN prod.bktau taux ON taux.dev = a.dev
                                            AND taux.dco = ?
             LEFT JOIN prod.bkaco ac ON ac.ncp = a.ncp
                                          AND ac.age = a.age
             LEFT JOIN clearinguser.cli_segment seg ON TRIM(a.cli) = TRIM(seg.cli)
             left join (select cli, SUM(sdecv) SDECV from prod.bksld where dco=?
                        and cha ='299120' and sde>0 GROUP BY CLI) prov_held on trim(prov_held.cli) = trim(a.cli)
            
             left join (select cli,sum(round(ctvmon)) collat from prod.miscolatctv group by cli) col on trim(col.cli) = trim(a.cli)
             LEFT JOIN (SELECT a.age,
                               a.lien,
                               a.datr,
                               b.cnr2,
                               CASE
                                 WHEN TRIM(n.lib2) = '-' THEN (-1) * n.tau1
                                 WHEN TRIM(n.lib2) = '+' THEN (1) * n.tau1
                                   END AS tau1
                        FROM (SELECT age, lien, MAX(datr) datr FROM prod.bkacod o GROUP BY o.age,
                                                                                           o.lien,
                                                                                           age,
                                                                                           lien) a,
                             prod.bkacod b
                               LEFT JOIN prod.bknom n ON n.cacc = b.cnr2
                                                           AND n.ctab = '021'
                        WHERE a.age = b.age
                          AND a.lien = b.lien
                          AND a.datr = b.datr) k ON k.lien = ac.lien
                                                      AND k.age = ac.age
      group by a.age_name, a.ncp, a.currency, TRIM(a.cli), a.nomrest,
                case when nvl(aut.maut * taux.tind,0.0)=0 then abs(a.balance)
             else nvl(aut.maut * taux.tind,0.0) end, a.balance, case
                                                  when b.debut is not null then b.debut
                                                  when b.debut is null and (a.class in (1, 2) or a.class is null)
                                                          then sysdate - 365
                                                  when b.debut is null and a.class in (5) then sysdate - (365 * 2)
                                                  else sysdate - (365 * 1.5) end, case
                                                                                    when c.fin is not null then c.fin
                                                                                    when c.fin is null and (a.class in (
              1,
              2) or a.class is null) then sysdate
                                                                                    when c.fin is null and a.class in (5)
                                                                                            then sysdate - (365)
                                                                                    else sysdate - (365 / 2) end,
               nvl(a.class, 1),
               nvl(a.ndaysarr, 0), a.cha, seg.segment, a.age,prov_held.sdecv,
               nvl(col.collat,0),a.ncp||a.age||a.currency,a.balance_fcy)
WHERE (balance < 0
   OR (balance >= 0
         AND (maut IS NOT NULL
                AND inter_susp <> 0
                AND inter_susp IS NOT NULL))) --and cli_id='0000746'
ORDER BY cli_id,
         ncp";

$stmt = $pdo->prepare($query);
$age_name = array();
$ncp = array();
$currency = array();
$cli_id = array();
$names = array();
$maut = array();
$balance = array();
$balance_fcy = array();
$inter_susp = array();
$start_date = array();
$end_date = array();
$tau = array();
$class = array();
$ndaysarr = array();
$net_a_provisioner = array();
$cha = array();
$segment = array();
$age = array();
$prov_held = array();
$contract_id = array();
$collateral = array();

if ($stmt->execute(array(
    $dco_month, $dco_year, $month, $year, $dco, $dco, $dco_tau, $dco
))) {
    $i = 0;
    $j = 0;
    $prev_inter = 0;
    $prev_cli = '';
    $prev_ncp = '';
    $prev_balance = '';
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

        if ($prev_inter == $row['INTER_SUSP'] && $prev_cli == $row['CLI_ID']) {
            $inter_susp[] = 0;
        } else {
            $inter_susp[] = abs($row['INTER_SUSP']);
        }
        if ($row['BALANCE'] >= 0) {
            $balance[] = 0;
            $balance_fcy[] = 0;
            if ($row['CLASS'] == '1' || $row['CLASS'] == '2') {
                $net_a_provisioner[] = abs($row['INTER_SUSP']);
            } else {
                $net_a_provisioner[] = 0 - abs($row['INTER_SUSP']);
            }
        } else {
            $balance[] = abs($row['BALANCE']);
            $balance_fcy[] = abs($row['BALANCE_FCY']);
            if ($row['CLASS'] == '1' || $row['CLASS'] == '2') {
                $net_a_provisioner[] = abs($row['BALANCE']) + abs($row['INTER_SUSP']);
            } else {
                $net_a_provisioner[] = abs($row['BALANCE']) - abs($row['INTER_SUSP']);
            }
        }

        $age_name[] = (string) trim($row['AGE_NAME']);
        $ncp[] = (string) trim($row['NCP']);
        $currency[] = (string) $row['CURRENCY'];
        $cli_id[] = (string) trim($row['CLI_ID']);
        $names[] = (string) trim($row['NAMES']);
        $maut[] = (float) $row['MAUT'];
        $start_date[] = $row['START_DATE'];
        $end_date[] = $row['END_DATE'];
        $tau[] = (float) $row['TAU'];
        $class[] = $row['CLASS'];
        $ndaysarr[] = round((float) $row['NDAYSARR'], 1);
        $cha[] = $row['CHA'];
        $segment[] = $row['SEGMENT'];
        $age[] = $row['AGE'];
        $prov_held[] = $row['PROV_HELD'];
        $contract_id[] = $row['CONTRACT_ID'];
        $collateral[] = $row['COLLATERAL'];

        if (empty(end($ndaysarr))) {
            array_pop($ndaysarr);
            $ndaysarr[] = 0;
        }

        if ($prev_ncp == $row['NCP']
            || (end($maut) == 0 && end($balance) == 0 && end($inter_susp)==0)
        )
        {
            array_pop($age_name);
            array_pop($ncp);
            array_pop($currency);
            array_pop($cli_id);
            array_pop($names);
            array_pop($maut);
            array_pop($balance);
            array_pop($balance_fcy);
            array_pop($inter_susp);
            array_pop($start_date);
            array_pop($end_date);
            array_pop($tau);
            array_pop($class);
            array_pop($ndaysarr);
            array_pop($net_a_provisioner);
            array_pop($cha);
            array_pop($segment);
            array_pop($age);
            array_pop($prov_held);
            array_pop($contract_id);
            array_pop($collateral);
        }

        $prev_cli = $row['CLI_ID'];
        $prev_inter = $row['INTER_SUSP'];
        $prev_ncp = $row['NCP'];
        $prev_balance = $row['BALANCE'];

        
    }
}


$q_delete = "DELETE FROM clearinguser.ifrs9_tloans  where doc_type=?";
$stmt_d = $pdo->prepare($q_delete);
$stmt_d->execute(array('OD'));

$number_of_rows = false;

//insert tloans
for ($n = 0; $n < sizeof($names); $n++) {
    $q_insert = "INSERT into clearinguser.ifrs9_tloans (
        LIB,
        AGE,
        CLI,
        NOMREST,
        NCP,
        CHA,
        DEV,
        SDE,
        SDECV,
        PROV_HELD,
        NEWCLA,
        NDAYSARR,
        LOAN_AMOUNT,
        INTEREST_DUE_FCY,
        INTEREST_DUE_LCY,
        CAPITAL_DUE_FCY,
        CAPITAL_DUE_LCY,
        LOAN_INCLUD_INTEREST,
        DUE_AMOUNT,
        START_DATE,
        END_DATE,
        TAU,
        TAU_CO1,
        TAU_CO2,
        TAU_CO3,
        TAU_FRA,
        LIBE,
        INSTALLMENT,
        CHA_LIB,
        CONTRACT_ID,
        SEGMENT,
        CATEGORY,
        REP_FREQ,
        DCO,
        DOC_TYPE,
        COLLATERAL,
        REGULATORY_PROV,
        INTEREST_SUSP
        ) values (
            ?,?,?,?,?,?,
            ?,?,?,?,?,?,
            ?,?,?,?,?,?,
            ?,?,?,?,?,?,
            ?,?,?,?,?,?,
            ?,?,?,?,?,?,?,?
            )";
    $interest_due_lcy = 0;//$int_imp[$n]+$comm_imp[$n]+abs($int_imp_npl[$n])+abs($comm_imp_npl[$n])+$prov_int[$n]+$agio_cdt_march[$n];
    $interest_due_fcy = 0;//$int_imp_fcy[$n]+$comm_imp_fcy[$n]+abs($int_imp_npl_fcy[$n])+abs($comm_imp_npl_fcy[$n])+$prov_int_fcy[$n]+$agio_cdt_march_fcy[$n];
    $capital_due_lcy = 0;//$cap_imp[$n]+$cap_imp_npl[$n];
    $capital_due_fcy = 0;//$cap_imp_fcy[$n]+$cap_imp_npl_fcy[$n];
    $loan_includ_interest = $balance[$n]+$interest_due_lcy+$capital_due_lcy;
    $amount_due =  $interest_due_lcy+$capital_due_lcy;

    $regulatory_prov = 0;



    $stmt = $pdo->prepare($q_insert);
    $number_of_rows = $stmt->fetchColumn(); 
    $stmt->execute(array(
        $age_name[$n],
        $age[$n],
        $cli_id[$n],
        $names[$n],
        $ncp[$n],
        $cha[$n],
        $currency[$n],
        $balance_fcy[$n],
        $balance[$n],
        $prov_held[$n],
        $class[$n],
        $ndaysarr[$n],
        $maut[$n],
        $interest_due_fcy,
        $interest_due_lcy,
        $capital_due_fcy,
        $capital_due_lcy,
        $loan_includ_interest,
        $amount_due,
        $start_date[$n],
        $end_date[$n],
        $tau[$n],
        0,//$tau_co1[$n],
        0,//$tau_co2[$n],
        0,//$tau_co3[$n],
        0,//$tau_fra[$n],
        'OVERDRAFT',//$libe[$n],
        0,//$inst[$n],
        '-',//$cha_lib[$n],
        $contract_id[$n],
        $segment[$n],
        'OVERDRAFT',//$loan_category[$n],
        'MTH',//$rep_freq[$n],  
        $dco,
        'OD',
        $collateral[$n],
        $regulatory_prov,
        $inter_susp[$n]
    ));
}

if ($stmt) 
    { 
        // it return number of rows in the table.
          
           if ($number_of_rows) 
              { 
                 printf("Number of row in the table : " . $number_of_rows); 
              } 
        // close the result. 
        $stmt->closeCursor();
    } 




// Credit radd
// CHA
$query = "SELECT *
FROM (
        SELECT a.age_name,
            a.ncp,
            a.currency,
            a.ncp || a.age || a.currency contract_id,
            TRIM(a.cli) cli_id,
            a.nomrest names,
            nvl(aut.maut * taux.tind, 0.0) maut,
            a.balance,
            a.balance_fcy,
            nvl(SUM(intsus.sdecv), 0.0) inter_susp,
            case
                when b.debut is not null then b.debut
                when b.debut is null
                and (
                    a.class in (1, 2)
                    or a.class is null
                ) then sysdate - 365
                when b.debut is null
                and a.class in (5) then sysdate - (365 * 2)
                else sysdate - (365 * 1.5)
            end start_date,
            case
                when c.fin is not null then c.fin
                when c.fin is null
                and (
                    a.class in (1, 2)
                    or a.class is null
                ) then sysdate
                when c.fin is null
                and a.class in (5) then sysdate - (365)
                else sysdate - (365 / 2)
            end end_date,
            nvl(MAX((15 + k.tau1)), 22) tau,
            nvl(a.class, 1) class,
            nvl(a.ndaysarr, 0) ndaysarr,
            a.cha,
            seg.segment,
            a.age,
            prov_held.sdecv prov_held,
            nvl(col.collat, 0) collateral
        FROM (
                SELECT a.cli,
                    a.ncp,
                    c.nomrest,
                    a.age,
                    TRIM(d.lib) age_name,
                    a.cha,
                    CASE
                        WHEN a.dev = '646' THEN 'RWF'
                        WHEN a.dev = '840' THEN 'USD'
                        WHEN a.dev = '978' THEN 'EUR'
                        WHEN a.dev = '826' THEN 'GBP'
                    END currency,
                    CASE
                        WHEN a.dev <> '646' THEN a.sde
                        ELSE 0
                    END balance_fcy,
                    a.sdecv balance,
                    b.newcla class,
                    b.ndaysarr ndaysarr,
                    a.dev,
                    a.dco
                FROM prod.bksld a
                    LEFT JOIN prod.misnewclassfinal b ON a.cli = b.cli
                    JOIN prod.bkcli c ON a.cli = c.cli
                    left join CLEARINGUSER.CLASSIFICATION_HIS cl on trim(c.cli) = trim(cl.cli)
                    and cl.month = ?
                    and cl.year = ?
                    JOIN prod.bkage d ON a.age = d.age
                WHERE --a.cli='0023678' and
                    a.dco = ?
                    AND --a.sde<0 and
                    a.cha in ('984210')
            ) a
            LEFT JOIN (
                SELECT MAX(debut) debut,
                    ncp
                FROM prod.bkautc
                GROUP BY ncp
            ) b ON a.ncp = b.ncp
            LEFT JOIN (
                SELECT MAX(fin) fin,
                    debut,
                    ncp
                FROM prod.bkautc
                GROUP BY ncp,
                    debut
            ) c ON c.ncp = b.ncp
            AND b.debut = c.debut
            LEFT JOIN prod.bkautc aut ON aut.ncp = c.ncp
            AND aut.debut = c.debut
            AND aut.fin = c.fin
            LEFT JOIN prod.bksld intsus ON a.cli = intsus.cli
            AND intsus.cha IN ('298200', '291115', '298330', '298331')
            AND intsus.dco = ?
            LEFT JOIN prod.bktau taux ON taux.dev = a.dev
            AND taux.dco = ?
            LEFT JOIN prod.bkaco ac ON ac.ncp = a.ncp
            AND ac.age = a.age
            LEFT JOIN clearinguser.cli_segment seg ON TRIM(a.cli) = TRIM(seg.cli)
            left join (
                select cli,
                    sdecv
                from prod.bksld
                where dco = ?
                    and cha = '299120'
                    and sde > 0
            ) prov_held on trim(prov_held.cli) = trim(a.cli)
            left join (
                select cli,
                    sum(round(ctvmon)) collat
                from prod.miscolatctv
                group by cli
            ) col on trim(col.cli) = trim(a.cli)
            LEFT JOIN (
                SELECT a.age,
                    a.lien,
                    a.datr,
                    b.cnr2,
                    CASE
                        WHEN TRIM(n.lib2) = '-' THEN (-1) * n.tau1
                        WHEN TRIM(n.lib2) = '+' THEN (1) * n.tau1
                    END AS tau1
                FROM (
                        SELECT age,
                            lien,
                            MAX(datr) datr
                        FROM prod.bkacod o
                        GROUP BY o.age,
                            o.lien,
                            age,
                            lien
                    ) a,
                    prod.bkacod b
                    LEFT JOIN prod.bknom n ON n.cacc = b.cnr2
                    AND n.ctab = '021'
                WHERE a.age = b.age
                    AND a.lien = b.lien
                    AND a.datr = b.datr
            ) k ON k.lien = ac.lien
            AND k.age = ac.age
        group by a.age_name,
            a.ncp,
            a.currency,
            TRIM(a.cli),
            a.nomrest,
            aut.maut * taux.tind,
            a.balance,
            case
                when b.debut is not null then b.debut
                when b.debut is null
                and (
                    a.class in (1, 2)
                    or a.class is null
                ) then sysdate - 365
                when b.debut is null
                and a.class in (5) then sysdate - (365 * 2)
                else sysdate - (365 * 1.5)
            end,
            case
                when c.fin is not null then c.fin
                when c.fin is null
                and (
                    a.class in (1, 2)
                    or a.class is null
                ) then sysdate
                when c.fin is null
                and a.class in (5) then sysdate - (365)
                else sysdate - (365 / 2)
            end,
            nvl(a.class, 1),
            nvl(a.ndaysarr, 0),
            a.cha,
            seg.segment,
            a.age,
            prov_held.sdecv,
            nvl(col.collat, 0),
            a.ncp || a.age || a.currency,
            a.balance_fcy
    )
WHERE (balance < 0
    OR (
        balance >= 0
        AND (
            maut IS NOT NULL
            AND inter_susp <> 0
            AND inter_susp IS NOT NULL
        )
    )) --and cli_id='0000746'
ORDER BY cli_id,
    ncp";

$stmt = $pdo->prepare($query);
$age_name = array();
$ncp = array();
$currency = array();
$cli_id = array();
$names = array();
$maut = array();
$balance = array();
$balance_fcy = array();
$inter_susp = array();
$start_date = array();
$end_date = array();
$tau = array();
$class = array();
$ndaysarr = array();
$net_a_provisioner = array();
$cha = array();
$segment = array();
$age = array();
$prov_held = array();
$contract_id = array();
$collateral = array();

if ($stmt->execute(array(
    $month, $year, $dco, $dco, $dco_tau, $dco
))) {
    $i = 0;
    $j = 0;
    $prev_inter = 0;
    $prev_cli = '';
    $prev_ncp = '';
    $prev_balance = '';
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

        if ($prev_inter == $row['INTER_SUSP'] && $prev_cli == $row['CLI_ID']) {
            $inter_susp[] = 0;
        } else {
            $inter_susp[] = abs($row['INTER_SUSP']);
        }
        if ($row['BALANCE'] >= 0) {
            $balance[] = 0;
            $balance_fcy[] = 0;
            if ($row['CLASS'] == '1' || $row['CLASS'] == '2') {
                $net_a_provisioner[] = abs($row['INTER_SUSP']);
            } else {
                $net_a_provisioner[] = 0 - abs($row['INTER_SUSP']);
            }
        } else {
            $balance[] = abs($row['BALANCE']);
            $balance_fcy[] = abs($row['BALANCE_FCY']);
            if ($row['CLASS'] == '1' || $row['CLASS'] == '2') {
                $net_a_provisioner[] = abs($row['BALANCE']) + abs($row['INTER_SUSP']);
            } else {
                $net_a_provisioner[] = abs($row['BALANCE']) - abs($row['INTER_SUSP']);
            }
        }

        $age_name[] = (string) trim($row['AGE_NAME']);
        $ncp[] = (string) trim($row['NCP']);
        $currency[] = (string) $row['CURRENCY'];
        $cli_id[] = (string) trim($row['CLI_ID']);
        $names[] = (string) trim($row['NAMES']);
        $maut[] = (float) $row['MAUT'];
        $start_date[] = $row['START_DATE'];
        $end_date[] = $row['END_DATE'];
        $tau[] = (float) $row['TAU'];
        $class[] = $row['CLASS'];
        $ndaysarr[] = round((float) $row['NDAYSARR'], 1);
        $cha[] = $row['CHA'];
        $segment[] = $row['SEGMENT'];
        $age[] = $row['AGE'];
        $prov_held[] = $row['PROV_HELD'];
        $contract_id[] = $row['CONTRACT_ID'];
        $collateral[] = $row['COLLATERAL'];

        if (empty(end($ndaysarr))) {
            array_pop($ndaysarr);
            $ndaysarr[] = 0;
        }

        if ($prev_ncp == $row['NCP']
            && end($inter_susp) ==0 && end($prev_balance) == $row['BALANCE']
            
        ) 
        {
            array_pop($age_name);
            array_pop($ncp);
            array_pop($currency);
            array_pop($cli_id);
            array_pop($names);
            array_pop($maut);
            array_pop($balance);
            array_pop($balance_fcy);
            array_pop($inter_susp);
            array_pop($start_date);
            array_pop($end_date);
            array_pop($tau);
            array_pop($class);
            array_pop($ndaysarr);
            array_pop($net_a_provisioner);
            array_pop($cha);
            array_pop($segment);
            array_pop($age);
            array_pop($prov_held);
            array_pop($contract_id);
            array_pop($collateral);
        }

        $prev_cli = $row['CLI_ID'];
        $prev_inter = $row['INTER_SUSP'];
        $prev_ncp = $row['NCP'];
        $prev_balance = $row['BALANCE'];
    }
}


$q_delete = "DELETE FROM clearinguser.ifrs9_tloans  where doc_type=?";
$stmt_d = $pdo->prepare($q_delete);
$stmt_d->execute(array('WF'));

$number_of_rows = false;

//insert tloans
for ($n = 0; $n < sizeof($names); $n++) {
    $q_insert = "INSERT into clearinguser.ifrs9_tloans (
        LIB,
        AGE,
        CLI,
        NOMREST,
        NCP,
        CHA,
        DEV,
        SDE,
        SDECV,
        PROV_HELD,
        NEWCLA,
        NDAYSARR,
        LOAN_AMOUNT,
        INTEREST_DUE_FCY,
        INTEREST_DUE_LCY,
        CAPITAL_DUE_FCY,
        CAPITAL_DUE_LCY,
        LOAN_INCLUD_INTEREST,
        DUE_AMOUNT,
        START_DATE,
        END_DATE,
        TAU,
        TAU_CO1,
        TAU_CO2,
        TAU_CO3,
        TAU_FRA,
        LIBE,
        INSTALLMENT,
        CHA_LIB,
        CONTRACT_ID,
        SEGMENT,
        CATEGORY,
        REP_FREQ,
        DCO,
        DOC_TYPE,
        COLLATERAL,
        REGULATORY_PROV,
        INTEREST_SUSP
        ) values (
            ?,?,?,?,?,?,
            ?,?,?,?,?,?,
            ?,?,?,?,?,?,
            ?,?,?,?,?,?,
            ?,?,?,?,?,?,
            ?,?,?,?,?,?,?,?
            )";
    $interest_due_lcy = 0;//$int_imp[$n]+$comm_imp[$n]+abs($int_imp_npl[$n])+abs($comm_imp_npl[$n])+$prov_int[$n]+$agio_cdt_march[$n];
    $interest_due_fcy = 0;//$int_imp_fcy[$n]+$comm_imp_fcy[$n]+abs($int_imp_npl_fcy[$n])+abs($comm_imp_npl_fcy[$n])+$prov_int_fcy[$n]+$agio_cdt_march_fcy[$n];
    $capital_due_lcy = 0;//$cap_imp[$n]+$cap_imp_npl[$n];
    $capital_due_fcy = 0;//$cap_imp_fcy[$n]+$cap_imp_npl_fcy[$n];
    $loan_includ_interest = $balance[$n]+$interest_due_lcy+$capital_due_lcy;
    $amount_due =  $interest_due_lcy+$capital_due_lcy;

    $regulatory_prov = 0;

    $stmt = $pdo->prepare($q_insert);
    $number_of_rows = $stmt->fetchColumn(); 
     $stmt->execute(array(
         $age_name[$n],
         $age[$n],
         $cli_id[$n],
         $names[$n],
         $ncp[$n],
         $cha[$n],
         $currency[$n],
         $balance_fcy[$n],
         $balance[$n],
         0,//$prov_held[$n],
         $class[$n],
         $ndaysarr[$n],
         $maut[$n],
         $interest_due_fcy,
         $interest_due_lcy,
         $capital_due_fcy,
         $capital_due_lcy,
         $loan_includ_interest,
         $amount_due,
         $start_date[$n],
         $end_date[$n],
         $tau[$n],
        0,//$tau_co1[$n],
        0,//$tau_co2[$n],
        0,//$tau_co3[$n],
        0,//$tau_fra[$n],
        'WRITTEN OFF',//$libe[$n],
        0,//$inst[$n],
        '-',//$cha_lib[$n],
        $contract_id[$n],
        $segment[$n],
        'WRITTEN OFF',//$loan_category[$n],
        'MTH',//$rep_freq[$n],  
        $dco,
        'WF',
        0,//$collateral[$n],
        $regulatory_prov,
        0 //$inter_susp[$n]
    ));
}

// if ($stmt) 
//     { 
//         // it return number of rows in the table.
          
//            if ($number_of_rows) 
//               { 
//                  printf("Number of row in the table : " . $number_of_rows); 
//               } 
//         // close the result. 
//         $stmt->closeCursor();
//     } 






    //insert cli_loanamount
$q_delete = "DELETE FROM clearinguser.ifrs9_cli_loanamount";
$stmt_d = $pdo->prepare($q_delete);
$stmt_d->execute(array());


$q_insert_amount = "INSERT INTO clearinguser.ifrs9_cli_loanamount
    SELECT cli, sum(LOAN_includ_interest),dco from clearinguser.ifrs9_tloans where doc_type in ('TL','OD') group by cli,dco";

$stmt2 = $pdo->prepare($q_insert_amount);
$stmt2->execute(array());


$q_delete_zero_amount = "DELETE FROM clearinguser.ifrs9_cli_loanamount where loan_amount=0";
$stmt_d_2 = $pdo->prepare($q_delete_zero_amount);
$stmt_d_2->execute(array());


//update prov_held
$q_update = "UPDATE clearinguser.ifrs9_tloans a
set a.prov_held=(
    select round(a.prov_held * (a.LOAN_includ_interest/b.loan_amount),0) from clearinguser.ifrs9_cli_loanamount b
    where trim(a.cli)=trim(b.cli) and a.dco=b.dco and a.prov_held<>0 and b.loan_amount<>0 and a.doc_type in ('TL','OD')
) where exists
(
select a.cli from clearinguser.ifrs9_tloans a
join clearinguser.ifrs9_cli_loanamount b on trim(a.cli)=trim(b.cli) and a.dco=b.dco and a.prov_held<>0 and b.loan_amount<>0 and a.doc_type in ('TL','OD')
) and a.doc_type in ('TL','OD')";
$stmt_update = $pdo->prepare($q_update);
$stmt_update->execute(array());


$pdo = null;


    print($dco);