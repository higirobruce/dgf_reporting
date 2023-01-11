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

//Database Connection
$pdo = new PDO('oci:dbname=192.168.0.20:1521/cgbk', 'CLEARINGUSER2017', 'CLEARINGUSER2017coge');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

//dco

if (isset($_POST['hidden_month_of'])) {
    $month = $_POST['hidden_month_of'];
    $year = $_POST['hidden_year_of'];
} else {
    $month = 6;
    $year = 2018;
}

$q_dco = $pdo->prepare("SELECT TO_CHAR(max(dco),'DD-MON-YYYY') d, prod.month(max(dco)) mon from prod.bksld where cha like '205%' and prod.month(dco)=? and prod.year(dco)=?");

$q_dco->execute(array($month, $year));

$r = $q_dco->fetch(PDO::FETCH_ASSOC);
$dco = $r['D'];
$dco_month = $r['MON'];

// CHA
$query = "SELECT distinct a.age,
    a.cha,
    A.NCP,
    a.ncp || a.age_code || a.currency contract_id,
    a.caution_id,
    a.cli,
    a.names,
    b.tpc caution_type,
    z.libe caution_type_lib,
    a.beneficiary,
    a.currency,
    b.mon amount,
    sum(a.balance_lcy) balance,
    sum(a.balance_fcy) balance_fcy,
    nvl(b.dco, sysdate -365) contract_start_date,
    nvl(b.dech, sysdate) value_date,
    chap.lib cha_lib,
    a.age_code,
    nvl(classif.newcla, 1) newcla,
    nvl(classif.ndaysarr, 0) ndaysarr,
    a.prov_held,
    a.collateral_val --a.class_risk,
    --a.collateral
from (
        select trim(d.lib) age,
            A.NCP,
            max(c.eve) caution_id,
            b.cli,
            b.nomrest names,
            c.ben beneficiary,
            case
                when a.dev = '646' then 'RWF'
                when a.dev = '840' then 'USD'
                when a.dev = '978' then 'EUR'
            end as currency,
            a.sdecv balance_lcy,
            case when a.dev<>'646' then a.sde 
            else 0 end balance_fcy,
            cla.newcla class_risk,
            TO_CHAR(TRIM(col_type.lib)) Collateral,
            a.cha,
            a.age age_code,
            nvl(prov_held.sdecv, 0) prov_held,
            nvl(col.collat, 0) collateral_val
        from prod.bksld a
            join prod.bkcli b on a.cli = b.cli
            left join prod.bkcau c on a.ncp = c.ncpe
            and c.dco <= ? --and rownum<=1
            left join prod.MISNEWCLASSFINAL cla on cla.CLI = a.cli
            left join CLEARINGUSER.CLASSIFICATION_HIS cl on trim(a.cli) = trim(cl.cli)
            and cl.month = ?
            and cl.year = ?
            left join (
                select cli,
                    sdecv
                from prod.bksld
                where dco = ?
                    and cha = '299120'
                    and sde > 0
            ) prov_held on prov_held.cli = a.cli
            left join (
                select cli,
                    WMSYS.WM_CONCAT(trim(lib)) lib
                from (
                        select distinct bgar.cli,
                            bnatg.LIB lib
                        from prod.bkgar bgar,
                            prod.bknatg bnatg
                        where bgar.cnat = bnatg.cnat
                    )
                group by cli
            ) col_type on col_type.cli = a.cli
            left join (
                select cli,
                    sum(round(ctvmon)) collat
                from prod.miscolatctv
                group by cli
            ) col on trim(col.cli) = trim(a.cli)
            join prod.bkage d on d.age = b.age
        where a.cha -- in ('925221','924100','924110','924111','924120',
            -- '924121','924130','924131','924200','924210',
            -- '924211','924400','924910','924911','925210',
            -- '925211','925220','925221','925230','925231',
            -- '925900','925910','925911','902100')
            in (
                '924100','924110','924111','924120','924121','924130',
                '924131','924200','924210','924211','924400','924910',
                '924911','925200','925210','925211','925220','925221',
                '925230','925231','925900','925910','925911','902100'
            )
            and a.dco = ?
            and a.sde < 0
        group by trim(d.lib),
            A.NCP,
            b.cli,
            b.nomrest,
            c.ben,
            case
                when a.dev = '646' then 'RWF'
                when a.dev = '840' then 'USD'
                when a.dev = '978' then 'EUR'
            end,
            a.sdecv,
            case when a.dev<>'646' then a.sde 
            else 0 end,
            cla.newcla,
            TO_CHAR(TRIM(col_type.lib)),
            a.cha,
            a.age,
            nvl(prov_held.sdecv, 0),
            nvl(col.collat, 0) --and a.sde<0
            --and a.cli='0054188'
    ) a
    left join (
        select eve,
            ncpe
        from prod.bkcau
    ) cau_unique on cau_unique.eve = a.caution_id
    left join prod.bkcau b on b.eve = cau_unique.eve
    and b.ncpe = cau_unique.ncpe
    left join prod.bktycau z on b.tpc = z.typ
    left join prod.bkchap chap on a.cha = chap.cha
    -- left join prod.misnewclassfinal classif on trim(classif.cli) = trim(a.cli)
    left join clearinguser.misnewclassfinal_his  classif on trim(a.cli) = trim(classif.cli) and prod.month(classif.finmois) = ? and prod.year(classif.finmois)=?
where
    --a.cli <> '0053833'
    --and A.CLI <> '0069537'AND 
    a.balance_lcy <> 0 --and a.cli='0000746'
group by a.age,
    a.cha,
    A.NCP,
    a.ncp || a.age_code || a.currency,
    a.caution_id,
    a.cli,
    a.names,
    b.tpc,
    z.libe,
    a.beneficiary,
    a.currency,
    b.mon,
    nvl(b.dco, sysdate -365),
    nvl(b.dech, sysdate),
    chap.lib,
    a.age_code,
    nvl(classif.newcla, 1),
    nvl(classif.ndaysarr, 0),
    a.prov_held,
    a.collateral_val
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
$balance_fcy = array();
$contract_start_date = array();
$value_date = array();
$cha_lib = array();
$age_code = array();
$class = array();
$daysarr = array();
$prov_held = array();
$contract_id = array();
$collateral = array();

if ($stmt->execute(array(
    $dco, $month, $year, $dco, $dco, $month, $year
))) {
    $i = 0;
    $j = 0;
    $prev_balance = 0;
    $prev_cli = '';
    $prev_ncp = '';
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if ($prev_ncp == $row['NCP'] && $prev_balance == $row['BALANCE']) {
        } else {
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
            $balance_fcy[] = abs($row['BALANCE_FCY']);
            $contract_start_date[] = $row['CONTRACT_START_DATE'];
            $value_date[] = $row['VALUE_DATE'];
            $cha_lib[] = $row['CHA_LIB'];
            $age_code[] = $row['AGE_CODE'];
            $class[] = $row['NEWCLA'];
            $daysarr[] = $row['NDAYSARR'];
            $prov_held[] = $row['PROV_HELD'];
            $contract_id[] = $row['CONTRACT_ID'];
            $collateral[] = $row['COLLATERAL_VAL'];
        }

        $prev_balance = $row['BALANCE'];
        $prev_cli = $row['CLI'];
        $prev_ncp = $row['NCP'];
    }
}



$q_delete = "DELETE FROM clearinguser.ifrs9_tloans  where doc_type=?";
$stmt_d = $pdo->prepare($q_delete);
$stmt_d->execute(array('OF'));

$number_of_rows = false;

//insert tloans
for ($n = 0; $n < sizeof($cli); $n++) {
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
    $inter_susp = 0;

    $regulatory_prov = 0;

    

    $stmt = $pdo->prepare($q_insert);
    $number_of_rows = $stmt->fetchColumn(); 
    $stmt->execute(array(
        $age[$n],
        $age_code[$n],
        $cli[$n],
        $names[$n],
        $ncp[$n],
        $cha[$n],
        $currency[$n],
        $balance_fcy[$n],
        $balance[$n],
        0,//$prov_held[$n],
        $class[$n],
        $daysarr[$n],
        $amount[$n],
        $interest_due_fcy,
        $interest_due_lcy,
        $capital_due_fcy,
        $capital_due_lcy,
        $loan_includ_interest,
        $amount_due,
        $contract_start_date[$n],
        $value_date[$n],
        0,//$tau[$n],
        0,//$tau_co1[$n],
        0,//$tau_co2[$n],
        0,//$tau_co3[$n],
        0,//$tau_fra[$n],
        'OFFBALANCESHEET',//$libe[$n],
        0,//$inst[$n],
        $cha_lib[$n],
        $contract_id[$n],
        '-',//$segment[$n],
        'OFFBALANCESHEET',//$loan_category[$n],
        'MTH',//$rep_freq[$n],  
        $dco,
        'OF',
        0,//$collateral[$n],
        $regulatory_prov,
        0 //$inter_susp
    ));
}


$pdo = null;


print($dco);