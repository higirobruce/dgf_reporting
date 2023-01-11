<?php

// $a = [2, 3, 4, 5, 6];
// end($a);
// echo prev($a);

// $dureem = 20; // durée d'emprunt en mois
// $tauxa = 8.5 / 100; // taux annuel
// $tauxm = $tauxa / 12; // taux périodique mensuel
// $coef = 1 + $tauxm;
// $proposition_mensualites = 1900000 * $tauxm * (pow($coef, $dureem) / (pow($coef, $dureem) - 1)); // 1500000 est le capital emprunté

// $proposition_mensualites += 148000; // 750 correspondant à l'assurance

// for ($taux = 8.5; $taux <= 9.000; $taux += 0.001) {
//     $total = 0;

//     for ($nb_mois = 1; $nb_mois <= 20; $nb_mois++) {
//         $total += ($proposition_mensualites / pow((1 + ($taux / 100)), ($nb_mois / 12)));
//     }

//     if ($total > 1900000) {
//         $teg = $taux;
//         break;
//     }
// }
// echo '<br>teg:' . $teg;


$s = 0;

for($i=1;$i<=10;$i++){
    
    $s= $i+$s;
}

echo $s;




<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");



$response->result=true;
$response->message = 'Successful';


echo json_encode($response);