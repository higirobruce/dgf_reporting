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
$pdo = new PDO('oci:dbname=192.168.0.20:1521/cgbk', 'BRUCE', 'BRUCE123');
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
$q_dco_tau = $pdo->prepare("SELECT TO_CHAR(max(dco),'DD-MON-YYYY') d from prod.bktau where prod.month(dco)=? and prod.year(dco)=?");

$q_dco->execute(array($month, $year));
$q_dco_tau->execute(array($month, $year));

$r = $q_dco->fetch(PDO::FETCH_ASSOC);
$r_tau = $q_dco_tau->fetch(PDO::FETCH_ASSOC);
$dco = $r['D'];
$dco_tau = $r_tau['D'];

// CHA
$query = "SELECT * from (
    select
    a.age_name,
    a.ncp,
    a.currency,
    trim(a.cli) cli_id,
    a.nomrest names,
    aut.maut * taux.tind maut,
    a.balance,
    sum(intsus.sdecv) inter_susp,
    b.debut start_date,
    c.fin end_date,
    max((15+k.tau1)) tau,
    a.class,
    a.ndaysarr,a.cha
    from (
    select a.cli,a.ncp,c.nomrest,a.age,trim(d.lib) age_name,a.cha,

        case when a.dev='646' then 'RWF'
        when a.dev = '840' then 'USD'
        when a.dev = '978' then 'EUROS'
        when a.dev = '826' then 'GBP'

        end currency,
        a.sdecv balance, b.newcla class, b.ndaysarr,a.dev,a.dco from prod.bksld a
        left join PROD.MISNEWCLASSFINAL b on a.cli = b.cli
        join prod.bkcli c on a.cli = c.cli
        join prod.bkage d on c.age = d.age
        where --a.cli='0023678' and
        a.dco=? and
        --a.sde<0 and
        ((a.cha like '201%' or a.cha like '203%' or a.cha like '1220%' or a.cha like '1230%' or a.cha like '2190%' or a.cha in (
        '291300',
        '291100',
        '291200',
        --
        '299010',
        '201203'
        )) and a.cha <> '219010'--and cha <> '201203'--credit cards
        )
        ) a

    left join (Select max(debut) debut, ncp from prod.bkautc group by ncp) b on a.ncp=b.ncp
    left join (Select max(fin) fin,debut, ncp from prod.bkautc group by ncp,debut) c on c.ncp = b.ncp and b.debut=c.debut
    left join prod.bkautc aut on aut.ncp = c.ncp and aut.debut = c.debut and aut.fin=c.fin
    left join prod.bksld intsus on a.cli = intsus.cli and intsus.cha in ('298200','291115','298330','298331') and intsus.dco=?
    left join prod.bktau taux on taux.dev = a.dev and taux.dco = ?
    Left Join prod.Bkaco Ac On Ac.Ncp=a.Ncp and ac.age=a.age
    left join (
    SELECT a.age,a.lien,a.datr,b.cnr2,
        case when trim(n.lib2) = '-' then (-1)*n.tau1
        when trim(n.lib2) = '+' then (1)*n.tau1 end as tau1
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
                AND   a.datr = b.datr
        ) k on k.lien = Ac.LIEN and k.age = ac.age group by a.age_name, a.ncp, a.currency, trim(a.cli), a.cli,
a.cli, a.cli, a.nomrest, a.balance, aut.maut * taux.tind,
b.debut, c.fin, a.class, a.ndaysarr,a.cha
)

where balance<0 or (

balance>=0 and (maut is not null and inter_susp<>0 and inter_susp is not null)
) order by cli_id,ncp";

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
$cha = array();

if ($stmt->execute(array(
    $dco, $dco, $dco_tau,
))) {
    $i = 0;
    $j = 0;
    $prev_inter = 0;
    $prev_cli = '';
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

        if ($prev_inter == $row['INTER_SUSP'] && $prev_cli == $row['CLI_ID']) {
            $inter_susp[] = 0;
        } else {
            $inter_susp[] = abs($row['INTER_SUSP']);
        }
        if ($row['BALANCE'] >= 0) {
            $balance[] = 0;
            if ($row['CLASS'] == '1' || $row['CLASS'] == '2') {
                $net_a_provisioner[] = abs($row['INTER_SUSP']);
            } else {
                $net_a_provisioner[] = 0 - abs($row['INTER_SUSP']);
            }
        } else {
            $balance[] = abs($row['BALANCE']);
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

        $prev_cli = $row['CLI_ID'];
        $prev_inter = $row['INTER_SUSP'];
    }
}

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

$objPHPExcel->setActiveSheetIndex(0)
    ->fromArray(
        array_chunk($age_name, 1),
        null,
        'A6'
    );
unset($age_name);

$objPHPExcel->setActiveSheetIndex(0)
    ->fromArray(
        array_chunk($cha, 1),
        null,
        'B6'
    );
unset($cha);


$objPHPExcel->setActiveSheetIndex(0)
    ->fromArray(
        array_chunk($ncp, 1),
        null,
        'C6'
    );
unset($ncp);

$objPHPExcel->setActiveSheetIndex(0)
    ->fromArray(
        array_chunk($currency, 1),
        null,
        'D6'
    );
unset($currency);

$objPHPExcel->setActiveSheetIndex(0)
    ->fromArray(
        array_chunk($cli_id, 1),
        null,
        'E6'
    );
unset($cli_id);

$objPHPExcel->setActiveSheetIndex(0)
    ->fromArray(
        array_chunk($names, 1),
        null,
        'F6'
    );
unset($names);

$objPHPExcel->setActiveSheetIndex(0)
    ->fromArray(
        array_chunk($maut, 1),
        null,
        'G6'
    );
unset($maut);

$objPHPExcel->setActiveSheetIndex(0)
    ->fromArray(
        array_chunk($balance, 1),
        null,
        'H6'
    );
unset($balance);

$objPHPExcel->setActiveSheetIndex(0)
    ->fromArray(
        array_chunk($inter_susp, 1),
        null,
        'I6'
    );
unset($inter_susp);

$objPHPExcel->setActiveSheetIndex(0)
    ->fromArray(
        array_chunk($start_date, 1),
        null,
        'Q6'
    );
unset($start_date);

$objPHPExcel->setActiveSheetIndex(0)
    ->fromArray(
        array_chunk($end_date, 1),
        null,
        'R6'
    );
unset($end_date);

$objPHPExcel->setActiveSheetIndex(0)
    ->fromArray(
        array_chunk($tau, 1),
        null,
        'L6'
    );
unset($tau);

$objPHPExcel->setActiveSheetIndex(0)
    ->fromArray(
        array_chunk($net_a_provisioner, 1),
        null,
        'M6'
    );
unset($net_a_provisioner);

$objPHPExcel->setActiveSheetIndex(0)
    ->fromArray(
        array_chunk($class, 1),
        null,
        'N6'
    );
unset($class);

$objPHPExcel->setActiveSheetIndex(0)
    ->fromArray(
        array_chunk($ndaysarr, 1),
        null,
        'O6'
    );
unset($ndaysarr);

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
$objPHPExcel->getActiveSheet()->setCellValue('A1', 'Overdrafts as of the end of ' . $months_names[$month - 1] . ' ' . $year . '.');

// Redirect output to a client’s web browser (Excel2007)
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Overdrafts ' . $months_names[$month - 1] . '-' . $year . '.xlsx');
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
