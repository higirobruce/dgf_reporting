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

$q = $pdo->prepare("SELECT sum(sdecv) tot from prod.bksld where cha like '205%' and prod.month(dco)=? and prod.year(dco)=?");

$q->execute(array($month,$year));

$r = $q->fetch(PDO::FETCH_ASSOC);



$q_data_exists = $pdo->prepare("SELECT sum(sdecv) tot from clearinguser.ifrs9_tloans where prod.month(dco)=? and prod.year(dco)=?");

$q_data_exists->execute(array($month,$year));

$r_data_exists = $q_data_exists->fetch(PDO::FETCH_ASSOC);

$response = new stdClass();

if($r['TOT']>0){

    $response->status = 'Success';
    $response->error = false;
    $response->message = 'Data available. Go ahead!';
    
    if($r_data_exists['TOT']>0){

        $response->status = 'Error';
        $response->error = true;
        $response->message = 'Contloan Data already available for the selected period.';
        $response->error_type = 2;
    } else {
    
        $response->status = 'Success';
        $response->error = false;
        $response->message = 'Go ahead!';
    }
} else {
    $response->status = 'Error';
    $response->error = true;
    $response->message = 'No data for the selected Period!';
    $response->error_type = 1;
}




echo json_encode($response);