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

//dco
if (isset($_POST['month'])) {
    $month = $_POST['month'];
    $year = $_POST['year'];
} else {
    $month = 6;
    $year = 2018;
}

$q_dco = $pdo->prepare("SELECT TO_CHAR(max(dco),'DD-MON-YYYY') d from prod.bksld where cha like '205%' and prod.month(dco)=? and prod.year(dco)=?");

$q_dco->execute(array($month, $year));

$r = $q_dco->fetch(PDO::FETCH_ASSOC);
$dco = $r['D'];

// CHA
$query = "SELECT
main.lib LIB,
main.age,
main.cli,
main.nomrest,
main.ncp,
main.cha,
main.dev,
main.sdecv,
main.cap_imp,
main.int_imp,
main.comm_imp,
main.cap_imp_npl,
main.com_imp_npl,
main.int_imp_npl,
main.prov_int,
main.agio_cdt_march,
main.newcla,
main.ndaysarr,
nvl(main.loan_amount * exRate.tind, aut.maut * exRate.tind) LOAN_AMOUNT,
nvl(main.start_date,aut.debut) START_DATE,
nvl(main.end_date,aut.fin) END_DATE,
nvl(main.tau, k.tau) TAU,
nvl(nvl(main.LIBE,aut.LIBT),'AUTRES CREDITS M.T') LIBE,
inst.map * exRate.tind installment,
chap.lib cha_lib from (
    select
    distinct
    ag.lib,
    main.*
    ,
    dos_2.loan_amount
    ,dos_2.start_date,
    dos_2.end_date, dos_2.tau, dos_2.LIBE
    from (
        select * from (select
distinct
b.age,b.cli,b.nomrest,nvl(chap_his.ncp,a.ncp) ncp,nvl(chap_his.cha_old,a.cha) cha,a.dev,a.sdecv,cap.sdecv cap_imp,interet.sdecv int_imp,
commis.sdecv comm_imp,cap_npl.sdecv cap_imp_npl, com_npl.sdecv com_imp_npl, int_npl.sdecv int_imp_npl ,cl.newcla,
cl.ndaysarr,prov_int.sdecv prov_int,agio_cd_march.sdecv agio_cdt_march
from prod.bksld a
left join prod.bkcli b on trim(a.cli) = trim(b.cli)
left join clearinguser.npl_cha_chang_his chap_his on a.ncp = chap_his.ncp and trim(chap_his.cha_old)<>'219010'

left join (SELECT cli,sum(sdecv) sdecv from prod.bksld where dco=?
and cha in ('219200','229200','239200','249200') and sde<0
group by cli) prov_int on trim(prov_int.cli) = trim(a.cli)

left join (SELECT cli,sum(sdecv) sdecv from prod.bksld where dco=?
and cha in ('290610','290610','290614','290620','290630','290640','290670',
'290680','290690','290691') and sde<0
group by cli) cap on cap.cli = a.cli

left join (SELECT cli,sum(sdecv) sdecv from prod.bksld where dco=?
and cha in ('290910','290914','290920','290930','290940','290970','290980',
'290990','290991') and sde<0
group by cli) interet on interet.cli = a.cli

left join (SELECT cli,sum(sdecv) sdecv from prod.bksld where dco=?
and cha in ('290810','290814','290820','290830','290840','290870','290880',
'290890','290891') and sde<0
group by cli) commis on commis.cli = a.cli

left join (SELECT cli,sum(sdecv) sdecv from prod.bksld where dco=?
and cha in ('291460','291560','291360') and sde<0
group by cli) cap_npl on cap_npl.cli = a.cli

left join (SELECT cli,sum(sdecv) sdecv from prod.bksld where dco=?
and cha in ('291480','291580','291380') and sde<0
group by cli) com_npl on com_npl.cli = a.cli

left join (SELECT cli,sum(sdecv) sdecv from prod.bksld where dco=?
and cha in ('291490','291590','291390') and sde<0
group by cli) int_npl on int_npl.cli = a.cli

left join (SELECT a.cha,trim(chap_his.cha_old) cha2, a.cli,sum(a.sdecv) sdecv from prod.bksld a
left join clearinguser.npl_cha_chang_his chap_his on trim(a.ncp) = trim(chap_his.ncp) and trim(chap_his.cha_old) = '219010'
where a.dco=?
--and (a.cha in ('219010') or chap_his.cha_old in ('219010')) --and a.sde<0 and
group by a.cli,a.cha,chap_his.cha_old ) agio_cd_march on agio_cd_march.cli = a.cli and (agio_cd_march.cha='219010' or agio_cd_march.cha2='219010')

left join PROD.MISNEWCLASSFINAL cl on trim(a.cli) = trim(cl.cli)
where a.dco=? and
    (a.cha in ('211100','211110','211120','211180','211190',
        '213915','215110','215120','215140','216110',
        '216115','217110','217120','217130','217140',
        '217170','217180','217190','217210','217300',
        '218000','222110','222155','222210','222220',
        '222270','222280','222310','225110','225114',
        '226110','227000','231010','231090','231091',
        '235190','235191','235800','241010','241014',
        '241020','241040','241070','241080','241090',
        '241091','241110','241120','241140','241170',
        '241180','241190','241191','241610','241900',
        '242010','243000','291450','291550','213122','222180','222120'
        )
      or (a.cha in ('291340','291330','291320',
                    '291410',
                    '291430','291440',
                    '291510','291530','291540',
                    '291610','291120','291220')
            and trim(chap_his.cha_old) not in ('219200','229200','239200','249200','291340','291330','291320',
                    '291410','291430','291440','291510','291530',
                    '291540','291610','291120','291220')
        )
    )
)
    ) main
    join prod.bkage ag on ag.age = main.age
    left join
    (
    select
    distinct
    cpt.ncp,dossier.eve,
    dossier.mon loan_amount
    ,dossier.dmep start_date,
    dossier.ddec end_date, dossier.tau_int tau, typrt.LIBE
    from prod.bkcptprt cpt
    join prod.mis_bkdosprteom dos on dos.eve=cpt.eve and dos.ave=cpt.ave and dos.ctr in ('1','5') and dos.eta='VA'
    left join (select cli,typ,max(ddec) ddec from prod.mis_bkdosprteom
        where ctr in ('1','5') and eta='VA' group by cli, typ) dos_1 on dos_1.cli = dos.cli and dos_1.typ = dos.typ
    left join (select cli,typ,max(dmep) dmep,ddec from prod.mis_bkdosprteom
        where ctr in ('1','5') and eta='VA' group by cli, typ, ddec) dos_3 on dos_3.cli = dos.cli and dos_3.typ = dos.typ and dos_3.ddec=dos_1.ddec
    left join PROD.MIS_BKDOSPRTEOM dos_2 on dos_2.ddec = dos_1.ddec and dos_2.cli=dos_1.cli
        and dos_2.ctr in ('1','5') and dos_2.eta='VA' and dos_2.typ = dos_1.typ
        and dos_2.eve=dos.eve and dos_2.ave=dos.ave and dos_2.dmep=dos_3.dmep
    left join prod.bktyprt typrt on typrt.typ = dos.typ
    join prod.bkdosprt dossier on dossier.eve = cpt.eve and dossier.eve = cpt.eve
    where cpt.nat='004'
    ) dos_2 on main.ncp=dos_2.ncp
) main
left join (Select dev, max(dco) dco, tind from prod.bktau where prod.month(dco)=? and prod.year(dco)=? group by dev,tind) exRate on exRate.dev = main.dev
left join (Select ncp,maut,debut,fin, 'CREDIT MARCHE' LIBT from prod.bkautc) aut on main.ncp = aut.ncp
Left Join prod.Bkaco Ac On Ac.Ncp=main.Ncp
left join (
    SELECT a.lien,max((15+n.tau1)) tau
    FROM
        (
            SELECT age,lien,MAX(datr) datr
            FROM
                prod.bkacod o
            GROUP BY o.age,o.lien,age,lien
        ) a,prod.bkacod b
        LEFT JOIN prod.bknom n ON n.cacc = b.cnr2
        AND n.ctab = '021'
    WHERE
        a.age = b.age
        AND   a.lien = b.lien
        AND   a.datr = b.datr group by a.lien
    ) k on k.lien = Ac.LIEN
left join (
    select a.*,g.TOT_ECH map from (
        select a.cli,c.ncp,max(a.eve) eve
        from prod.bkdosprt a inner join prod.bkcptprt k on a.age=k.age and a.eve=k.eve and a.ave=k.ave
        and k.nat='004' and a.ctr<>9
        inner join prod.bkcom c on k.age=c.age and k.dev=c.dev and k.ncp=c.ncp
        inner join prod.bkcli p on a.cli=p.cli
        inner join prod.bktyprt z on a.typ=z.typ
        group by a.cli, c.ncp
    ) a
    inner join prod.bkdosprt b on a.eve = b.eve
    inner join prod.bkechprt g on b.age=g.age and b.eve=g.eve and b.ave=g.ave and b.dech=g.num

) inst on main.ncp = inst.ncp

left join clearinguser.npl_cha_chang_his chap_his on main.ncp = chap_his.ncp and trim(chap_his.cha_old)<>'219010'

left join prod.bkchap chap on main.cha = chap.cha or  trim(chap.cha) = trim(chap_his.cha_old)

where (sdecv<0 or
(
    sdecv >= 0 and (
            cap_imp is not null or int_imp is not null or comm_imp is not null or cap_imp_npl is not null or
            com_imp_npl is not null or int_imp_npl is not null or prov_int is not null
        ) and (
            cap_imp <>0 or int_imp <>0 or comm_imp <>0 or cap_imp_npl <>0 or
            com_imp_npl <>0 or int_imp_npl <>0 or prov_int <>0
    )
) )
-- and main.cli='0000746'

order by main.cli,main.sdecv,main.ncp desc";

$stmt = $pdo->prepare($query);
$arr = array();
$ret = array();
$lib = array();
$age = array();
$cha = array();
$cli = array();
$ncp = array();
$nomrest = array();
$dev = array();
$mon = array();
$sdecv = array();
$map = array();
$cap_imp = array();
$cap_imp_npl = array();
$int_imp = array();
$int_imp_npl = array();
$comm_imp = array();
$comm_imp_npl = array();
$newcla = array();
$loan_amount = array();
$start_date = array();
$end_date = array();
$tau = array();
$libe = array();
$ndaysarr = array();
$inst = array();
$cha_lib = array();
$prov_int = array();
$agio_cdt_march = array();

if ($stmt->execute(array(
    $dco, $dco, $dco,
    $dco, $dco, $dco,
    $dco, $dco, $dco,
    $month, $year,
))) {
    $i = 0;
    $j = 0;
    $prev_ncp = '';
    $prev_cli = '';
    $prev_res = 0;
    $prev_cap = 0;
    $prev_cap_npl = 0;
    $prev_com = 0;
    $prev_com_npl = 0;
    $prev_capn = 0;
    $prev_int = 0;
    $prev_int_npl = 0;
    $prev_amount = 0;
    $prev_prov_int = 0;
    $prev_agio = 0;
    $dont_set = false;
    $unset = 0;
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

        if ((($prev_cli == (string) trim($row['CLI']) && $prev_res == $row['SDECV'] && $prev_ncp == $row['NCP']) || $row['SDECV'] > 0) || is_null($row['SDECV'])) {
            $sdecv[] = 0;
            $dont_set = true;
        } else {
            $sdecv[] = abs((float) $row['SDECV']);
            $dont_set = false;
        }

        if (($prev_cli == (string) trim($row['CLI']) && $prev_cap == $row['CAP_IMP']) || is_null($row['CAP_IMP'])) {
            $cap_imp[] = 0;
            $dont_set = true;
        } else {
            $cap_imp[] = abs((float) $row['CAP_IMP']);
            $dont_set = false;
        }

        if (($prev_cli == (string) trim($row['CLI']) && $prev_prov_int == $row['PROV_INT']) || is_null($row['PROV_INT'])) {
            $prov_int[] = 0;
            $dont_set = true;
        } else {
            $prov_int[] = abs((float) $row['PROV_INT']);
            $dont_set = false;
        }

        if (($prev_cli == (string) trim($row['CLI']) && $prev_int == $row['INT_IMP']) || is_null($row['INT_IMP'])) {
            $int_imp[] = 0;
            $dont_set = true;
        } else {
            $int_imp[] = abs((float) $row['INT_IMP']);
            $dont_set = false;
        }

        if (($prev_cli == (string) trim($row['CLI']) && $prev_int_npl == $row['INT_IMP_NPL']) || is_null($row['INT_IMP_NPL'])) {
            $int_imp_npl[] = 0;
            $dont_set = true;
        } else {
            $int_imp_npl[] = abs((float) $row['INT_IMP_NPL']);
            $dont_set = false;
        }

        if (($prev_cli == (string) trim($row['CLI']) && $prev_com == $row['COMM_IMP']) || is_null($row['COMM_IMP'])) {
            $comm_imp[] = 0;
            $dont_set = true;
        } else {
            $comm_imp[] = abs((float) $row['COMM_IMP']);
            $dont_set = false;
        }

        if (($prev_cli == (string) trim($row['CLI']) && $prev_com_npl == $row['COM_IMP_NPL']) || is_null($row['COM_IMP_NPL'])) {
            $comm_imp_npl[] = 0;
            $dont_set = true;
        } else {
            $comm_imp_npl[] = abs((float) $row['COM_IMP_NPL']);
            $dont_set = false;
        }

        if (($prev_cli == (string) trim($row['CLI']) && $prev_capn == $row['CAP_IMP_NPL']) || is_null($row['CAP_IMP_NPL'])) {
            $cap_imp_npl[] = 0;
            $dont_set = true;
        } else {
            $cap_imp_npl[] = abs((float) $row['CAP_IMP_NPL']);
            $dont_set = false;
        }

        if (($prev_cli == (string) trim($row['CLI']) && $prev_agio == $row['AGIO_CDT_MARCH']) || is_null($row['AGIO_CDT_MARCH'])) {
            $agio_cdt_march[] = 0;
            $dont_set = true;
        } else {
            $agio_cdt_march[] = abs((float) $row['AGIO_CDT_MARCH']);
            $dont_set = false;
        }

        if (($prev_cli == (string) trim($row['CLI']) && $prev_amount == $row['LOAN_AMOUNT']) || is_null($row['LOAN_AMOUNT'])) {
            $loan_amount[] = 0;
            $dont_set = true;
        } else {
            $loan_amount[] = abs((float) $row['LOAN_AMOUNT']);
            $dont_set = false;
        }

        $lib[] = (string) trim($row['LIB']);
        $age[] = (double) $row['AGE'];
        $cli[] = (string) trim($row['CLI']);
        $ncp[] = (string) $row['NCP'];
        $nomrest[] = (string) trim($row['NOMREST']);
        $dev[] = (int) $row['DEV'];
        $cha[] = $row['CHA'];
        $newcla[] = $row['NEWCLA'];

        $ndaysarr[] = $row['NDAYSARR'];
        $inst[] = $row['INSTALLMENT'];
        $cha_lib[] = $row['CHA_LIB'];

        $start_date[] = $row['START_DATE'];
        $end_date[] = $row['END_DATE'];
        $tau[] = $row['TAU'];
        $libe[] = trim($row['LIBE']);

        if ($dont_set == true) {
            array_pop($sdecv);
            array_pop($cap_imp);
            array_pop($prov_int);
            array_pop($int_imp);
            array_pop($int_imp_npl);
            array_pop($comm_imp);
            array_pop($comm_imp_npl);
            array_pop($cap_imp_npl);
            array_pop($agio_cdt_march);
            array_pop($loan_amount);
            array_pop($lib);
            array_pop($age);
            array_pop($cli);
            array_pop($ncp);
            array_pop($nomrest);
            array_pop($dev);
            array_pop($cha);
            array_pop($newcla);
            array_pop($ndaysarr);
            array_pop($inst);
            array_pop($cha_lib);
            array_pop($start_date);
            array_pop($end_date);
            array_pop($tau);
            array_pop($libe);
        }

        $prev_amount = (float) $row['LOAN_AMOUNT'];
        $prev_ncp = (string) $row['NCP'];
        $prev_cli = (string) trim($row['CLI']);
        $prev_res = (float) $row['SDECV'];
        $prev_cap = (float) $row['CAP_IMP'];
        $prev_com = (float) $row['COMM_IMP'];
        $prev_com_npl = (float) $row['COM_IMP_NPL'];
        $prev_int = (float) $row['INT_IMP'];
        $prev_int_npl = (float) $row['INT_IMP_NPL'];
        $prev_capn = (float) $row['CAP_IMP_NPL'];
        $prev_prov_int = (float) $row['PROV_INT'];
        $prev_agio = (float) $row['AGIO_CDT_MARCH'];
    }
}

// Create new PHPExcel object
$objReader = PHPExcel_IOFactory::createReader('Excel2007');
$objPHPExcel = $objReader->load("templates/template_loans_ifrs.xlsx");

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
        array_chunk($lib, 1),
        null,
        'A6'
    );
unset($lib);

$objPHPExcel->setActiveSheetIndex(0)
    ->fromArray(
        array_chunk($age, 1),
        null,
        'B6'
    );
unset($age);

$objPHPExcel->setActiveSheetIndex(0)
    ->fromArray(
        array_chunk($cli, 1),
        null,
        'C6'
    );
unset($cli);

$objPHPExcel->setActiveSheetIndex(0)
    ->fromArray(
        array_chunk($nomrest, 1),
        null,
        'D6'
    );
unset($nomrest);

$objPHPExcel->setActiveSheetIndex(0)
    ->fromArray(
        array_chunk($ncp, 1),
        null,
        'E6'
    );
unset($ncp);

$objPHPExcel->setActiveSheetIndex(0)
    ->fromArray(
        array_chunk($cha, 1),
        null,
        'F6'
    );
unset($cha);

$objPHPExcel->setActiveSheetIndex(0)
    ->fromArray(
        array_chunk($dev, 1),
        null,
        'G6'
    );
unset($dev);

$objPHPExcel->setActiveSheetIndex(0)
    ->fromArray(
        array_chunk($sdecv, 1),
        null,
        'H6'
    );
unset($sdecv);

$objPHPExcel->setActiveSheetIndex(0)
    ->fromArray(
        array_chunk($cap_imp, 1),
        null,
        'I6'
    );
unset($cap_imp);

$objPHPExcel->setActiveSheetIndex(0)
    ->fromArray(
        array_chunk($int_imp, 1),
        null,
        'J6'
    );
unset($int_imp);

$objPHPExcel->setActiveSheetIndex(0)
    ->fromArray(
        array_chunk($comm_imp, 1),
        null,
        'K6'
    );
unset($comm_imp);

$objPHPExcel->setActiveSheetIndex(0)
    ->fromArray(
        array_chunk($cap_imp_npl, 1),
        null,
        'L6'
    );
unset($cap_imp_npl);

$objPHPExcel->setActiveSheetIndex(0)
    ->fromArray(
        array_chunk($int_imp_npl, 1),
        null,
        'M6'
    );
unset($int_imp_npl);

$objPHPExcel->setActiveSheetIndex(0)
    ->fromArray(
        array_chunk($comm_imp_npl, 1),
        null,
        'N6'
    );
unset($comm_imp_npl);

$objPHPExcel->setActiveSheetIndex(0)
    ->fromArray(
        array_chunk($prov_int, 1),
        null,
        'O6'
    );
unset($prov_int);

$objPHPExcel->setActiveSheetIndex(0)
    ->fromArray(
        array_chunk($agio_cdt_march, 1),
        null,
        'P6'
    );
unset($agio_cdt_march);

$objPHPExcel->setActiveSheetIndex(0)
    ->fromArray(
        array_chunk($newcla, 1),
        null,
        'R6'
    );
unset($newcla);

$objPHPExcel->setActiveSheetIndex(0)
    ->fromArray(
        array_chunk($ndaysarr, 1),
        null,
        'S6'
    );
unset($ndaysarr);

$objPHPExcel->setActiveSheetIndex(0)
    ->fromArray(
        array_chunk($loan_amount, 1),
        null,
        'T6'
    );
unset($loan_amount);

$objPHPExcel->setActiveSheetIndex(0)
    ->fromArray(
        array_chunk($start_date, 1),
        null,
        'AA6'
    );
unset($start_date);

$objPHPExcel->setActiveSheetIndex(0)
    ->fromArray(
        array_chunk($end_date, 1),
        null,
        'AB6'
    );
unset($end_date);

$objPHPExcel->setActiveSheetIndex(0)
    ->fromArray(
        array_chunk($tau, 1),
        null,
        'W6'
    );
unset($tau);

$objPHPExcel->setActiveSheetIndex(0)
    ->fromArray(
        array_chunk($libe, 1),
        null,
        'X6'
    );

$objPHPExcel->setActiveSheetIndex(0)
    ->fromArray(
        array_chunk($inst, 1),
        null,
        'Y6'
    );
unset($inst);

$objPHPExcel->setActiveSheetIndex(0)
    ->fromArray(
        array_chunk($cha_lib, 1),
        null,
        'Z6'
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
$objPHPExcel->getActiveSheet()->setCellValue('A1', 'TERM LOANS as of the end of ' . $months_names[$month - 1] . ' ' . $year . '.');

// Redirect output to a client’s web browser (Excel2007)
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Term Loans ' . $months_names[$month - 1] . ' ' . $year . '.xlsx');
header('Cache-Control: max-age=0');
// If you're serving to IE 9, then the following may be needed
header('Cache-Control: max-age=1');

// If you're serving to IE over SSL, then the following may be needed
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
header('Pragma: public'); // HTTP/1.0

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save('php://output');
exit;
