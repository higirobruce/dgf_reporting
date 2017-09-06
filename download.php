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
$objPHPExcel = $objReader->load("templates/template_3.xlsx");


// Set document properties
$objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
							 ->setLastModifiedBy("Maarten Balliauw")
							 ->setTitle("Office 2007 XLSX Test Document")
							 ->setSubject("Office 2007 XLSX Test Document")
							 ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
							 ->setKeywords("office 2007 openxml php")
							 ->setCategory("Test result file");


// CHA
$query = "SELECT c.tcli,c.cha,c.DEV,count(*) numbers,sum(c.sdecv) sums,sum(c.sde) sde from (
    select i.tcli,a.cha,a.dev,a.sdecv,a.sde from prod.bksld  a 
    left join prod.bkcli i on i.cli=a.cli
     where  (
         cha like ? or 
         cha like ? or 
         cha like ? or 
         cha like ? or 
         cha like ? or 
         cha like ? or 
         cha like ? or 
         cha like ? or 
         cha=? or 
         cha=? or 
         cha like ?) 
    and sde>? 
    and cha not like ? 
    and cha not like ? 
    and cha not like ? 
    and cha not like ?
    and cha not like ?  
    and a.dco= ?
    
         UNION ALL
       select  i.tcli,a.cha,a.dev,a.sdecv,a.sde from prod.bksld a
      left join prod.bkcli i on i.cli=a.cli
       where (
           cha like ? or 
           cha like ? or 
           cha like ? or 
           cha like ?)
    and cha!=? 
    and cha!=? 
    and a.dco=? 
    AND a.sde<>? ) c group by c.tcli,c.cha,c.dev";

$stmt = $pdo->prepare($query); 
$arr = array();
$ret = array();
if(isset($_POST['ndt2']))
{
    $dco = $_POST['ndt2'];
}else{
    $dco = '30/6/2017';
}

$cha_cli = array();
$dev = array();
$cha = array();
$numbers = array();
$sums = array();
$sde7 = array();
$data = array();
if ($stmt->execute(array(
    '20%','12%','18%','21%','22%','23%','24%','25%',
    '148635','148636','290%','0','208%','219%','229%',
    '239%','249%',$dco,'27%','28%','14%','208%','148635',
    '148636',$dco,0
))){
    $i = 0;
    $j = 0;
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $cha_cli[] = (int)$row['TCLI'];
        $cha[] = (string)$row['CHA'];
        $dev[] = (int)$row['DEV'];
        $numbers[] = (int)$row['NUMBERS'];
        $sums[] = (float)$row['SUMS'];  
        $sde7[] =  (int)$row['SDE'];      
    }
}


//CURRENCY
$query = "SELECT c.tcli, c.dev, l.tind, sum(c.num) num, sum(c.sde) sde, sum(c.sdecv) sdecv 
                from (select i.tcli,a.dev,count(a.sdecv) num,sum(a.sde) sde,sum(a.sdecv) sdecv 
                    from prod.bksld a left join prod.bkcli i on i.cli=a.cli
                        where  (cha like ? or cha like ? or cha like ? or cha like ?
                        or cha like ? or cha like ? or cha like ? or cha like ?
                        or cha=? or cha=? or cha like ?) and sde > ? 
                        and cha not like ? and cha not like ? and cha not like ? 
                        and cha not like ? and cha not like ?  and a.dco= ?
                    group by i.tcli,a.dev UNION ALL
                select i.tcli,a.dev,count(a.sdecv) num,sum(a.sde) sde,sum(a.sdecv) sdecv 
                    from prod.bksld a left join prod.bkcli i on i.cli=a.cli
                        where (cha like ? or cha like ? or cha like ? or cha like ?)
                        and cha!=? and cha!=? and a.dco=?  and a.sde<>?
                        group by i.tcli,a.dev) c
                        join prod.bktau l on l.dev=c.dev and l.dco=?
                        group by c.tcli,c.dev,l.tind";

$stmt = $pdo->prepare($query); 
$arr = array();
$ret = array();
if(isset($_POST['ndt2']))
{
    $dco = $_POST['ndt2'];
    $date = $_POST['newdate'];

}else{
    $dco = '30/6/2017';
}

$tcli = array();
$dev1 = array();
$tind = array();
$num = array();
$sde = array();
$sdecv = array();
if ($stmt->execute(array(
    '20%','12%','18%','21%','22%','23%','24%','25%', '148635', '148636','290%',
    '0','208%','219%','229%','239%','249%',$dco,'27%','28%','14%','208%','148635',
    '148636',$dco,0,$dco
))){
    $i = 0;
    $j = 0;
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $tcli[] = (int)$row['TCLI'];
        $dev1[] = (int)$row['DEV'];
        $tind[] = (int)$row['TIND'];
        $num[] = (int)$row['NUM'];            
        $sde[] = (float)$row['SDE'];            
        $sdecv[] = (int)$row['SDECV'];            
    }
}


//PUBLIC INSTITUTIONS
// $query = "SELECT distinct i.cli,c.dev,i.nomrest,C.SDECV,C.SDE FROM CLEARINGUSER.NCP_PUBLIC P
//         JOIN prod.BKSLD C ON C.NCP=P.NCP 
//         join prod.bkcli i on i.cli=c.cli
//         WHERE C.DCO=?  and c.sde> ? order by 2";

// $stmt = $pdo->prepare($query); 
// $arr = array();
// $ret = array();
// if(isset($_POST['ndt2']))
// {
//     $dco = $_POST['ndt2'];
//     $date = $_POST['newdate'];

// }else{
//     $dco = '30/6/2017';
// }

// $cli = array();
// $dev2 = array();
// $nomrest = array();
// $sdecv2 = array();
// $sde2 = array();
// if ($stmt->execute(array($dco, 0))){
// $i = 0;
// $j = 0;
//     while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
//         $cli[] = (string)$row['CLI'];
//         $dev2[] = (int)$row['DEV'];
//         $nomrest[] = (string)$row['NOMREST'];
//         $sdecv2[] = (int)$row['SDECV'];
//         $sde2[] = (float)$row['SDE'];       
//     }
// }


//INSURANCE COMPANIES
// $query = "SELECT distinct i.cli,c.dev,i.nomrest,sum(c.sdecv) sdecv, sum(C.SDE) sde FROM CLEARINGUSER.NCP_ASSURANCE P
//         JOIN prod.BKSLD C ON C.NCP=P.NCP 
//         join prod.bkcli i on i.cli=c.cli
//         WHERE C.DCO=?  and c.sde>?
//         group by i.cli,c.dev,i.nomrest
//         order by 2";

// $stmt = $pdo->prepare($query); 
// $arr = array();
// $ret = array();
// if(isset($_POST['ndt2']))
// {
//     $dco = $_POST['ndt2'];
//     $date = $_POST['newdate'];

// }else{
//     $dco = '30/6/2017';
// }

// $cli3 = array();
// $dev3 = array();
// $nomrest3 = array();
// $sdecv3 = array();
// $sde3 = array();
// if ($stmt->execute(array($dco, 0))){
//     $i = 0;
//     $j = 0;
//     while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
//         $cli3[] = (string)$row['CLI'];
//         $dev3[] = (int)$row['DEV'];
//         $nomrest3[] = (string)$row['NOMREST'];
//         $sdecv3[] = (int)$row['SDECV'];
//         $sde3[] = (float)$row['SDE'];       
//     }
// }



//FINANCLIAL INSTITUTIONS RWF
$query = "SELECT count(*) NUM, sum(b.sdecv) TOT from prod.bksld b
            inner join clearinguser.ncp_financiere p on b.ncp = p.ncp
            where b.sdecv > ? and b.dco = ? and b.dev = ?";

$stmt = $pdo->prepare($query); 
$arr = array();
$ret = array();
if(isset($_POST['ndt2']))
{
    $dco = $_POST['ndt2'];
    $date = $_POST['newdate'];

}else{
    $dco = '30/6/2017';
}

$financial_num_rwf = 0;
$financial_total_rwf = 0;
if ($stmt->execute(array(0,$dco,'646'))){
    $i = 0;
    $j = 0;
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $financial_num_rwf = $row['NUM'];
        $financial_total_rwf = $row['TOT'];
          
    }
}
//Depositors
$query = "SELECT count(distinct b.cli) num from prod.bksld b
inner join clearinguser.ncp_financiere p on b.ncp = p.ncp
where b.sde > ? and b.dco = ? and b.dev = ?";

$stmt = $pdo->prepare($query); 
$arr = array();
$ret = array();
if(isset($_POST['ndt2']))
{
$dco = $_POST['ndt2'];
$date = $_POST['newdate'];

}else{
$dco = '30/6/2017';
}

$financial_depositors_rwf = 0;
if ($stmt->execute(array(0,$dco,'646'))){
$i = 0;
$j = 0;
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
$financial_depositors_rwf = $row['NUM'];
}
}


//FINANCLIAL INSTITUTIONS FOREIGN CURRENCY
$query = "SELECT count(*) NUM, sum(b.sdecv) TOT from prod.bksld b
inner join clearinguser.ncp_financiere p on b.ncp = p.ncp
where b.sdecv > ? and b.dco = ? and b.dev <> ?";

$stmt = $pdo->prepare($query); 
$arr = array();
$ret = array();
if(isset($_POST['ndt2']))
{
    $dco = $_POST['ndt2'];
    $date = $_POST['newdate'];

    }else{
    $dco = '30/6/2017';
}

$financial_num_foreign = 0;
$financial_total_foreign = 0;
if ($stmt->execute(array(0,$dco,'646'))){
    $i = 0;
    $j = 0;
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $financial_num_foreign = $row['NUM'];
        $financial_total_foreign = $row['TOT'];
    }
}
//Depositors
$query = "SELECT count(distinct b.cli) num from prod.bksld b
inner join clearinguser.ncp_financiere p on b.ncp = p.ncp
where b.sde > ? and b.dco = ? and b.dev <> ?";

$stmt = $pdo->prepare($query); 
$arr = array();
$ret = array();
if(isset($_POST['ndt2']))
{
$dco = $_POST['ndt2'];
$date = $_POST['newdate'];

}else{
$dco = '30/6/2017';
}

$financial_depositors_foreign = 0;
if ($stmt->execute(array(0,$dco,'646'))){
$i = 0;
$j = 0;
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
$financial_depositors_foreign = $row['NUM'];
}
}




//PUBLIC INST RWF
$query = "SELECT count(*) NUM, sum(b.sdecv) TOT from prod.bksld b
inner join clearinguser.ncp_public p on b.ncp = p.ncp
where b.sdecv > ? and b.dco = ? and b.dev = ?";

$stmt = $pdo->prepare($query); 
$arr = array();
$ret = array();
if(isset($_POST['ndt2']))
{
$dco = $_POST['ndt2'];
$date = $_POST['newdate'];

}else{
$dco = '30/6/2017';
}

$public_num_rwf = 0;
$public_total_rwf = 0;
if ($stmt->execute(array(0,$dco,'646'))){
$i = 0;
$j = 0;
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
$public_num_rwf = $row['NUM'];
$public_total_rwf = $row['TOT'];

}
}
//Depositors
$query = "SELECT count(distinct b.cli) num from prod.bksld b
inner join clearinguser.ncp_public p on b.ncp = p.ncp
where b.sde > ? and b.dco = ? and b.dev = ?";

$stmt = $pdo->prepare($query); 
$arr = array();
$ret = array();
if(isset($_POST['ndt2']))
{
$dco = $_POST['ndt2'];
$date = $_POST['newdate'];

}else{
$dco = '30/6/2017';
}

$public_depositors_rwf = 0;
if ($stmt->execute(array(0,$dco,'646'))){
$i = 0;
$j = 0;
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
$public_depositors_rwf = $row['NUM'];
}
}



//PUBLIC INST FOREIGN
$query = "SELECT count(*) NUM, sum(b.sdecv) TOT from prod.bksld b
inner join clearinguser.ncp_public p on b.ncp = p.ncp
where b.sdecv > ? and b.dco = ? and b.dev <> ?";

$stmt = $pdo->prepare($query); 
$arr = array();
$ret = array();
if(isset($_POST['ndt2']))
{
$dco = $_POST['ndt2'];
$date = $_POST['newdate'];

}else{
$dco = '30/6/2017';
}

$public_num_foreign = 0;
$public_total_foreign = 0;
if ($stmt->execute(array(0,$dco,'646'))){
$i = 0;
$j = 0;
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
$public_num_foreign = $row['NUM'];
$public_total_foreign = $row['TOT'];

}
}
//Depositors
$query = "SELECT count(distinct b.cli) num from prod.bksld b
inner join clearinguser.ncp_public p on b.ncp = p.ncp
where b.sde > ? and b.dco = ? and b.dev <> ?";

$stmt = $pdo->prepare($query); 
$arr = array();
$ret = array();
if(isset($_POST['ndt2']))
{
$dco = $_POST['ndt2'];
$date = $_POST['newdate'];

}else{
$dco = '30/6/2017';
}

$public_depositors_foreign = 0;
if ($stmt->execute(array(0,$dco,'646'))){
$i = 0;
$j = 0;
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
$public_depositors_foreign = $row['NUM'];
}
}


//INSURANCE INST RWF
$query = "SELECT count(*) NUM, sum(b.sdecv) TOT from prod.bksld b
inner join clearinguser.ncp_assurance p on b.ncp = p.ncp
where b.sdecv > ? and b.dco = ? and b.dev = ?";

$stmt = $pdo->prepare($query); 
$arr = array();
$ret = array();
if(isset($_POST['ndt2']))
{
$dco = $_POST['ndt2'];
$date = $_POST['newdate'];

}else{
$dco = '30/6/2017';
}

$assurance_num_rwf = 0;
$assurance_total_rwf = 0;
if ($stmt->execute(array(0,$dco,'646'))){
$i = 0;
$j = 0;
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
$assurance_num_rwf = $row['NUM'];
$assurance_total_rwf = $row['TOT'];

}
}
//Depositors
$query = "SELECT count(distinct b.cli) num from prod.bksld b
inner join clearinguser.ncp_assurance p on b.ncp = p.ncp
where b.sde > ? and b.dco = ? and b.dev = ?";

$stmt = $pdo->prepare($query); 
$arr = array();
$ret = array();
if(isset($_POST['ndt2']))
{
$dco = $_POST['ndt2'];
$date = $_POST['newdate'];

}else{
$dco = '30/6/2017';
}

$assurance_depositors_rwf = 0;
if ($stmt->execute(array(0,$dco,'646'))){
$i = 0;
$j = 0;
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
$assurance_depositors_rwf = $row['NUM'];
}
}


//INSURANCE INST FOREIGN
$query = "SELECT count(*) NUM, sum(b.sdecv) TOT from prod.bksld b
inner join clearinguser.ncp_assurance p on b.ncp = p.ncp
where b.sdecv > ? and b.dco = ? and b.dev <> ?";

$stmt = $pdo->prepare($query); 
$arr = array();
$ret = array();
if(isset($_POST['ndt2']))
{
$dco = $_POST['ndt2'];
$date = $_POST['newdate'];

}else{
$dco = '30/6/2017';
}

$assurance_num_foreign = 0;
$assurance_total_foreign = 0;
if ($stmt->execute(array(0,$dco,'646'))){
$i = 0;
$j = 0;
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
$assurance_num_foreign = $row['NUM'];
$assurance_total_foreign = $row['TOT'];

}
}
//Depositors
$query = "SELECT count(distinct b.cli) num from prod.bksld b
inner join clearinguser.ncp_assurance p on b.ncp = p.ncp
where b.sde > ? and b.dco = ? and b.dev <> ?";

$stmt = $pdo->prepare($query); 
$arr = array();
$ret = array();
if(isset($_POST['ndt2']))
{
$dco = $_POST['ndt2'];
$date = $_POST['newdate'];

}else{
$dco = '30/6/2017';
}

$assurance_depositors_foreign = 0;
if ($stmt->execute(array(0,$dco,'646'))){
$i = 0;
$j = 0;
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
$assurance_depositors_foreign = $row['NUM'];
}
}


//SHAREHOLDERS
$query = "SELECT c.cli,a.dev,c.nomrest,a.ncp,sum(a.sdecv) sdecv from prod.bksld a
join prod.bkcli c on c.cli=a.cli
where ( 
    a.cha like ? or 
    a.cha like ? or 
    a.cha like ? or 
    a.cha like ? or  
    a.cha like ? or 
    a.cha like ?  or 
    a.cha like ? or 
    a.cha=? or 
    a.ncp=? or 
    a.cha like ? or 
    a.cha like ? or 
    a.cha like ?  )  
    and a.dco=? 
    and a.sde>?
AND a.cli in ('0000431','0000435','0001017','0002106','0008037','0009936','0009937',
'0009938','0009939','0009940','0014541','0022149','0025277','0027734',
'0028117','0029306','0030351','0030426','0030449','0030518','0030554',
'0030692','0031003','0032173','0032382','0035901','0049691','0049696',
'0049699','0049958','0050164','0054124','0050170','0051136','0054124',
'0054130','0054251','0054256','0054263','0054291','0054401','0054406',
'0054407','0054408','0054433','0061290','0073293')
group by c.cli,c.nomrest,a.ncp,a.dev order by 1 desc";

$stmt = $pdo->prepare($query); 
$arr = array();
$ret = array();
if(isset($_POST['ndt2']))
{
    $dco = $_POST['ndt2'];
    $date = $_POST['newdate'];

}else{
    $dco = '30/6/2017';
}

$cli5 = array();
$dev5 = array();
$nomrest5 = array();
$sdecv5 = array();
$ncp5 = array();
if ($stmt->execute(array(
    '201%','208%','209%','203%','1220%','1230%',
    '148%','271200','00002091006','204%','205%','2811%',
    $dco,0
)))
    {
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $cli5[] = (string)$row['CLI'];
        $dev5[] = (int)$row['DEV'];
        $nomrest5[] = (string)$row['NOMREST'];
        $sdecv5[] = (int)$row['SDECV'];
        $ncp5[] = (string)$row['NCP'];       
    }
}



//Quarter
$query = "SELECT typ_client,cat_client,count(c.sde) num,sum(c.sde) sde,sum(c.sdecv) sdecv from (
    select  case 
       when a.sdecv<=? then 'under 500 000'
       when a.sdecv>?  and a.sdecv<=? then 'between 500 001 and 600 000'
       when a.sdecv>?  and a.sdecv<=? then 'between 600 001 and 700 000'
       when a.sdecv>? then 'more than 700 000'
       end cat_client,
       case 
       when i.tcli=? then 'P'
       when i.tcli=? then 'E'
       when i.tcli=? then 'P'
       else  'E' 
       end  typ_client
       ,a.dev, a.sde,a.sdecv from prod.bksld  a 
    left join prod.bkcli i on i.cli=a.cli
     where  (
         cha like ? or 
         cha like ? or 
         cha like ? or 
         cha like ? or 
         cha like ? or 
         cha like ? or 
         cha like ? or 
         cha like ? or 
         cha=? or 
         cha=? or 
         cha like ?) 
    and sde>? 
    and cha not like ? 
    and cha not like ? 
    and cha not like ? 
    and cha not like ?
    and cha not like ?  
    and a.dco=? 
    and a.sdecv>?
      -- group by i.tcli,a.dev
         UNION ALL
       select 
       case 
       when  a.sdecv<=? then 'under 500 000'
       when a.sdecv>? and a.sdecv<=? then 'between 500 001 and 600 000'
       when a.sdecv>?  and a.sdecv<=? then 'between 600 001 and 700 000'
       when a.sdecv>? then 'more than 700 000'
       end cat_client,
       case 
       when i.tcli=? then 'P'
       when i.tcli=? then 'E'
       when i.tcli=? then 'P'
       else  'E'  
       end typ_client
      ,a.dev, a.sde,a.sdecv from prod.bksld a
      left join prod.bkcli i on i.cli=a.cli
       where (
           cha like ? or 
           cha like ? or 
           cha like ? or 
           cha like ?)
    and cha!=? 
    and cha!=? 
    and a.dco=? 
    and a.sdecv>?
     -- group by i.tcli,a.dev
    ) c group by typ_client,cat_client";

$stmt = $pdo->prepare($query); 
$arr = array();
$ret = array();
if(isset($_POST['ndt2']))
{
    $dco = $_POST['ndt2'];
    $date = $_POST['newdate'];

}else{
    $dco = '30/6/2017';
}

$typ_client = array();
$cat_client = array();
$num6 = array();
$sde6 = array();
$sdecv6 = array();
if ($stmt->execute(array(
    500000,500000,600000,600000,700000,700000,
    1,2,3,'20%','12%','18%','21%','22%','23%',
    '24%','25%','148635','148636','290%','0',
    '208%','219%','229%','239%','249%',$dco,0,
    500000,500000,600000,600000,700000,700000,
    1,2,3,'27%','28%','14%','208%','148635','148636',
    $dco,0
)))
    {
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $typ_client[] = (string)$row['TYP_CLIENT'];
        $cat_client[] = (string)$row['CAT_CLIENT'];
        $num6[] = (int)$row['NUM'];
        $sde6[] = (float)$row['SDE'];
        $sdecv6[] = (int)$row['SDECV'];       
    }
}



//REGISTER
$query = "SELECT distinct a.cli,f.ncp,a.nomrest,a.sext gender,
            a.nid id_passport,a.dna DoB,a.viln,a.depn,t.num phone,
            m.email,g.lib branch from prod.bkcli a 
        join prod.bksld c on c.cli=a.cli and c.dco=?
        left join prod.bktelcli t on t.cli=a.cli and t.typ=?
        left join prod.bkemacli m on m.cli=a.cli and t.typ=?
        join prod.bkage g on g.age=a.age
        join clearinguser.ncp_cli f on trim(f.cli)=trim(a.cli)";

$stmt = $pdo->prepare($query); 
$arr = array();
$ret = array();
if(isset($_POST['ndt2']))
{
    $dco = $_POST['ndt2'];
    $date = $_POST['newdate'];

}else{
    $dco = '30/6/2017';
}

$cli7 = array();
$ncp7 = array();
$nomrest7 = array();
$gender = array();
$id_passport = array();
$dob = array();
$viln = array();
$depn = array();
$phone = array();
$email = array();
$branch = array();
if ($stmt->execute(array($dco,'002','001')))
    {
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $cli7[] = (string)$row['CLI'];
        $ncp7[] = (string)$row['NCP'];
        $nomrest7[] = (string)$row['NOMREST'];
        $gender[] = (string)$row['GENDER'];
        $id_passport[] = (string)$row['ID_PASSPORT'];
        $dob[] = (string)$row['DOB'];
        $viln[] = (string)$row['VILN'];
        $depn[] = (string)$row['DEPN'];
        $phone[] = (string)$row['PHONE'];
        $email[] = (string)$row['EMAIL'];
        $branch[] = (string)$row['BRANCH'];     
    }
}


$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('B4', 'REPORT AS AT '.$_POST['day'].'/'.$_POST['month'].'/'.$_POST['year']);

$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('B26', 'Date: '.date('d/m/Y'));

//CHA Table
// $objPHPExcel->setActiveSheetIndex(7)
// ->fromArray(
//     array_chunk($cha, 1),  
//     NULL,       
//     'A3'
// );
// $objPHPExcel->setActiveSheetIndex(7)
// ->fromArray(
//     array_chunk($dev, 1),
//     NULL,
//     'B3'
// );
// $objPHPExcel->setActiveSheetIndex(7)
// ->fromArray(
//     array_chunk($numbers, 1),
//     NULL,
//     'C3'
// );
// $objPHPExcel->setActiveSheetIndex(7)
// ->fromArray(
//     array_chunk($sums, 1),
//     NULL,        // Array values with this value will not be set
//     'D3'
// );

//CURRENCY Table
$objPHPExcel->setActiveSheetIndex(7)
->fromArray(
    array_chunk($tcli, 1),
    NULL,
    'F3'
);
$objPHPExcel->setActiveSheetIndex(7)
->fromArray(
    array_chunk($dev1, 1),
    NULL,
    'G3'
);
$objPHPExcel->setActiveSheetIndex(7)
->fromArray(
    array_chunk($tind, 1),
    NULL,
    'H3'
);
$objPHPExcel->setActiveSheetIndex(7)
->fromArray(
    array_chunk($num, 1),
    NULL,        // Array values with this value will not be set
    'I3' 
);
$objPHPExcel->setActiveSheetIndex(7)
->fromArray(
    array_chunk($sde, 1),
    NULL,        // Array values with this value will not be set
    'J3'
);
$objPHPExcel->setActiveSheetIndex(7)
->fromArray(
    array_chunk($sdecv, 1),
    NULL,        // Array values with this value will not be set
    'K3'
);

//PUBLIC INSTITUTIONS Table
// $objPHPExcel->setActiveSheetIndex(7)
// ->fromArray(
//     array_chunk($cli, 1),
//     NULL,        // Array values with this value will not be set
//     'M3'
// );
// $objPHPExcel->setActiveSheetIndex(7)
// ->fromArray(
//     array_chunk($dev2, 1),
//     NULL,        // Array values with this value will not be set
//     'N3'
// );
// $objPHPExcel->setActiveSheetIndex(7)
// ->fromArray(
//     array_chunk($nomrest, 1),
//     NULL,        // Array values with this value will not be set
//     'O3'
// );
// $objPHPExcel->setActiveSheetIndex(7)
// ->fromArray(
//     array_chunk($sdecv2, 1),
//     NULL,        // Array values with this value will not be set
//     'P3'
// );
// $objPHPExcel->setActiveSheetIndex(7)
// ->fromArray(
//     array_chunk($sde2, 1),
//     NULL,        // Array values with this value will not be set
//     'Q3'
// );


//INSURANCE COMPANIES
// $objPHPExcel->setActiveSheetIndex(7)
// ->fromArray(
//     array_chunk($cli3, 1),
//     NULL,        // Array values with this value will not be set
//     'S3'
// );
// $objPHPExcel->setActiveSheetIndex(7)
// ->fromArray(
//     array_chunk($dev3, 1),
//     NULL,        // Array values with this value will not be set
//     'T3'
// );
// $objPHPExcel->setActiveSheetIndex(7)
// ->fromArray(
//     array_chunk($nomrest3, 1),
//     NULL,        // Array values with this value will not be set
//     'U3'
// );
// $objPHPExcel->setActiveSheetIndex(7)
// ->fromArray(
//     array_chunk($sdecv3, 1),
//     NULL,        // Array values with this value will not be set
//     'V3'
// );
// $objPHPExcel->setActiveSheetIndex(7)
// ->fromArray(
//     array_chunk($sde3, 1),
//     NULL,        // Array values with this value will not be set
//     'W3'
// );



//PUBLIC Table

$objPHPExcel->setActiveSheetIndex(0)
->setCellValue('C13',$public_total_rwf/1000);
$objPHPExcel->setActiveSheetIndex(0)
->setCellValue('D13',$public_num_rwf);
$objPHPExcel->setActiveSheetIndex(0)
->setCellValue('E13',$public_depositors_rwf);

$objPHPExcel->setActiveSheetIndex(1)
->setCellValue('C13',$public_total_foreign/1000);
$objPHPExcel->setActiveSheetIndex(1)
->setCellValue('D13',$public_num_foreign);
$objPHPExcel->setActiveSheetIndex(1)
->setCellValue('E13',$public_depositors_foreign);




//FINANCIAL Table
$objPHPExcel->setActiveSheetIndex(0)
->setCellValue('C14',$financial_total_rwf/1000);
$objPHPExcel->setActiveSheetIndex(0)
->setCellValue('D14',$financial_num_rwf);
$objPHPExcel->setActiveSheetIndex(0)
->setCellValue('E14',$financial_depositors_rwf);


$objPHPExcel->setActiveSheetIndex(1)
->setCellValue('C14',$financial_total_foreign/1000);
$objPHPExcel->setActiveSheetIndex(1)
->setCellValue('D14',$financial_num_foreign);
$objPHPExcel->setActiveSheetIndex(1)
->setCellValue('E14',$financial_depositors_foreign);




//INSURANCE Table
$objPHPExcel->setActiveSheetIndex(0)
->setCellValue('C15',$assurance_total_rwf/1000);
$objPHPExcel->setActiveSheetIndex(0)
->setCellValue('D15',$assurance_num_rwf);
$objPHPExcel->setActiveSheetIndex(0)
->setCellValue('E15',$assurance_depositors_rwf);


$objPHPExcel->setActiveSheetIndex(1)
->setCellValue('C15',$assurance_total_foreign/1000);
$objPHPExcel->setActiveSheetIndex(1)
->setCellValue('D15',$assurance_num_foreign);
$objPHPExcel->setActiveSheetIndex(1)
->setCellValue('E15',$assurance_depositors_foreign);




// $objPHPExcel->setActiveSheetIndex(7)
// ->fromArray(
//     array_chunk($cli4, 1),
//     NULL,        // Array values with this value will not be set
//     'Y3'
// );
// $objPHPExcel->setActiveSheetIndex(7)
// ->fromArray(
//     array_chunk($dev4, 1),
//     NULL,        // Array values with this value will not be set
//     'Z3'
// );
// $objPHPExcel->setActiveSheetIndex(7)
// ->fromArray(
//     array_chunk($nomrest4, 1),
//     NULL,        // Array values with this value will not be set
//     'AA3'
// );
// $objPHPExcel->setActiveSheetIndex(7)
// ->fromArray(
//     array_chunk($ncp, 1),
//     NULL,        // Array values with this value will not be set
//     'AB3'
// );
// $objPHPExcel->setActiveSheetIndex(7)
// ->fromArray(
//     array_chunk($sdecv4, 1),
//     NULL,        // Array values with this value will not be set
//     'AC3'
// );


//SHAREHOLDERS
$objPHPExcel->setActiveSheetIndex(7)
->fromArray(
    array_chunk($cli5, 1),
    NULL,        // Array values with this value will not be set
    'AE3'
);
$objPHPExcel->setActiveSheetIndex(7)
->fromArray(
    array_chunk($dev5, 1),
    NULL,        // Array values with this value will not be set
    'AF3'
);
$objPHPExcel->setActiveSheetIndex(7)
->fromArray(
    array_chunk($nomrest5, 1),
    NULL,        // Array values with this value will not be set
    'AG3'
);
$objPHPExcel->setActiveSheetIndex(7)
->fromArray(
    array_chunk($ncp5, 1),
    NULL,        // Array values with this value will not be set
    'AH3'
);
$objPHPExcel->setActiveSheetIndex(7)
->fromArray(
    array_chunk($sdecv5, 1),
    NULL,        // Array values with this value will not be set
    'AI3'
);


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



//CHA2
$objPHPExcel->setActiveSheetIndex(7)
->fromArray(
    array_chunk($cha_cli, 1),  
    NULL,       
    'AQ3'
);
$objPHPExcel->setActiveSheetIndex(7)
->fromArray(
    array_chunk($cha, 1),  
    NULL,       
    'AR3'
);
$objPHPExcel->setActiveSheetIndex(7)
->fromArray(
    array_chunk($dev, 1),
    NULL,
    'AS3'
);
$objPHPExcel->setActiveSheetIndex(7)
->fromArray(
    array_chunk($numbers, 1),
    NULL,
    'AT3'
);
$objPHPExcel->setActiveSheetIndex(7)
->fromArray(
    array_chunk($sums, 1),
    NULL,        // Array values with this value will not be set
    'AU3'
);
$objPHPExcel->setActiveSheetIndex(7)
->fromArray(
    array_chunk($sde7, 1),
    NULL,        // Array values with this value will not be set
    'AV3'
);

//Register
$objPHPExcel->setActiveSheetIndex(6)
->fromArray(
    array_chunk($cli7, 1),
    NULL,        // Array values with this value will not be set
    'A8'
);

$objPHPExcel->setActiveSheetIndex(6)
->fromArray(
    array_chunk($ncp7, 1),
    NULL,        // Array values with this value will not be set
    'B8'
);
$objPHPExcel->setActiveSheetIndex(6)
->fromArray(
    array_chunk($nomrest7, 1),
    NULL,        // Array values with this value will not be set
    'C8'
);
$objPHPExcel->setActiveSheetIndex(6)
->fromArray(
    array_chunk($gender, 1),
    NULL,        // Array values with this value will not be set
    'D8'
);
$objPHPExcel->setActiveSheetIndex(6)
->fromArray(
    array_chunk($id_passport, 1),
    NULL,        // Array values with this value will not be set
    'E8'
);
$objPHPExcel->setActiveSheetIndex(6)
->fromArray(
    array_chunk($dob, 1),
    NULL,        // Array values with this value will not be set
    'F8'
);
$objPHPExcel->setActiveSheetIndex(6)
->fromArray(
    array_chunk($viln, 1),
    NULL,        // Array values with this value will not be set
    'G8'
);
$objPHPExcel->setActiveSheetIndex(6)
->fromArray(
    array_chunk($depn, 1),
    NULL,        // Array values with this value will not be set
    'H8'
);
$objPHPExcel->setActiveSheetIndex(6)
->fromArray(
    array_chunk($phone, 1),
    NULL,        // Array values with this value will not be set
    'I8'
);
$objPHPExcel->setActiveSheetIndex(6)
->fromArray(
    array_chunk($email, 1),
    NULL,        // Array values with this value will not be set
    'J8'
);
$objPHPExcel->setActiveSheetIndex(6)
->fromArray(
    array_chunk($branch, 1),
    NULL,        // Array values with this value will not be set
    'K8'
);

// Rename worksheet



// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);


// Redirect output to a client’s web browser (Excel2007)
header('Access-Control-Allow-Origin: *');
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
