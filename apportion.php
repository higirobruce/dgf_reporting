

<?php


//Database Connection
$pdo = new PDO('oci:dbname=192.168.0.20:1521/cgbk', 'CLEARINGUSER2017', 'CLEARINGUSER2017coge');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

//Null loan amount
$q_update_loan_amount = "UPDATE clearinguser.ifrs9_tloans 
set loan_amount= 0 
where loan_amount is null";
$stmt_update_loan_amount = $pdo->prepare($q_update_loan_amount);
$stmt_update_loan_amount->execute(array());

//insert cli_loanamount
$q_delete = "DELETE FROM clearinguser.ifrs9_cli_loanamount";
$stmt_d = $pdo->prepare($q_delete);
$stmt_d->execute(array());


$q_insert_amount = "INSERT INTO clearinguser.ifrs9_cli_loanamount
    SELECT cli, sum(LOAN_includ_interest+loan_amount),dco from clearinguser.ifrs9_tloans group by cli,dco";
$stmt2 = $pdo->prepare($q_insert_amount);
$stmt2->execute(array());


$q_delete_zero_amount = "DELETE FROM clearinguser.ifrs9_cli_loanamount where loan_amount=0";
$stmt_d_2 = $pdo->prepare($q_delete_zero_amount);
$stmt_d_2->execute(array());

//update collateral
$q_update_collateral = "UPDATE clearinguser.ifrs9_tloans a
set a.collateral=(
    select round(a.collateral * ((a.LOAN_includ_interest+a.loan_amount)/b.loan_amount),0) from clearinguser.ifrs9_cli_loanamount b
    where trim(a.cli)=trim(b.cli)
) where exists
(
select a.cli from clearinguser.ifrs9_tloans a
join clearinguser.ifrs9_cli_loanamount b on trim(a.cli)=trim(b.cli))";
$stmt_update_collateral = $pdo->prepare($q_update_collateral);
$stmt_update_collateral->execute(array());


//insert cli_loanamount
$q_delete = "DELETE FROM clearinguser.ifrs9_cli_loanamount";
$stmt_d = $pdo->prepare($q_delete);
$stmt_d->execute(array());


$q_insert_amount = "INSERT INTO clearinguser.ifrs9_cli_loanamount
    SELECT cli, sum(LOAN_includ_interest+loan_amount),dco from clearinguser.ifrs9_tloans where doc_type not in ('OF','WF') group by cli,dco";
$stmt2 = $pdo->prepare($q_insert_amount);
$stmt2->execute(array());


$q_delete_zero_amount = "DELETE FROM clearinguser.ifrs9_cli_loanamount where loan_amount=0";
$stmt_d_2 = $pdo->prepare($q_delete_zero_amount);
$stmt_d_2->execute(array());


//update prov_held
$q_update = "UPDATE clearinguser.ifrs9_tloans a
set a.prov_held=(
    select round(a.prov_held * ((a.LOAN_includ_interest+a.loan_amount)/b.loan_amount),0) from clearinguser.ifrs9_cli_loanamount b
    where trim(a.cli)=trim(b.cli) and a.dco=b.dco and a.prov_held<>0 and b.loan_amount<>0 and a.doc_type not in ('OF','WF')
) where exists
(
select a.cli from clearinguser.ifrs9_tloans a
join clearinguser.ifrs9_cli_loanamount b on trim(a.cli)=trim(b.cli) and a.dco=b.dco and a.prov_held<>0 and b.loan_amount<>0 and a.doc_type not in ('OF','WF')
) and a.doc_type not in ('OF','WF')
and a.prov_held<>0";
$stmt_update = $pdo->prepare($q_update);
$stmt_update->execute(array());


//----------------
//insert cli_loanamount
$q_delete = "DELETE FROM clearinguser.ifrs9_cli_loanamount";
$stmt_d = $pdo->prepare($q_delete);
$stmt_d->execute(array());


$q_insert_amount = "INSERT INTO clearinguser.ifrs9_cli_loanamount
    SELECT cli, sum(LOAN_includ_interest+loan_amount),dco from clearinguser.ifrs9_tloans where doc_type='OF' group by cli,dco";
$stmt2 = $pdo->prepare($q_insert_amount);
$stmt2->execute(array());


$q_delete_zero_amount = "DELETE FROM clearinguser.ifrs9_cli_loanamount where loan_amount=0";
$stmt_d_2 = $pdo->prepare($q_delete_zero_amount);
$stmt_d_2->execute(array());



// update prov_held
$q_update = "UPDATE clearinguser.ifrs9_tloans a
set a.prov_held=(
    select round(a.prov_held * ((a.LOAN_includ_interest+a.loan_amount)/b.loan_amount),0) from clearinguser.ifrs9_cli_loanamount b
    where trim(a.cli)=trim(b.cli) and a.dco=b.dco and a.prov_held<>0 and b.loan_amount<>0 and a.doc_type in ('OF')
) where exists
(
select a.cli from clearinguser.ifrs9_tloans a
join clearinguser.ifrs9_cli_loanamount b on trim(a.cli)=trim(b.cli) and a.dco=b.dco and a.prov_held<>0 and b.loan_amount<>0 and a.doc_type in ('OF')
) and a.doc_type in ('OF')
and a.prov_held<>0";
$stmt_update = $pdo->prepare($q_update);
$stmt_update->execute(array());

//Null collateral
$q_update_collateral_2 = "UPDATE clearinguser.ifrs9_tloans 
set collateral= 0 
where collateral is null";
$stmt_update_collateral_2 = $pdo->prepare($q_update_collateral_2);
$stmt_update_collateral_2->execute(array());


//Prov_held
$q_update_prov_held = "UPDATE clearinguser.ifrs9_tloans 
set prov_held= 0 
where prov_held is null";
$stmt_update_prov_held = $pdo->prepare($q_update_prov_held);
$stmt_update_prov_held->execute(array());


//Interest_susp_calc
$q_update_int_susp = "UPDATE clearinguser.ifrs9_tloans 
set interest_susp_calc= 0 
where interest_susp_calc is null";
$stmt_update_int_susp = $pdo->prepare($q_update_int_susp);
$stmt_update_int_susp->execute(array());

//Loan Include Interest
$q_update = "UPDATE clearinguser.ifrs9_tloans 
set loan_includ_interest = sdecv + interest_due_lcy + capital_due_lcy";
$stmt_d = $pdo->prepare($q_update);
$stmt_d->execute(array());


//Net_risk
$q_update_net_risk = "UPDATE clearinguser.ifrs9_tloans 
set net_risk= 0 
where net_risk is null";
$stmt_update_net_risk = $pdo->prepare($q_update_net_risk);
$stmt_update_net_risk->execute(array());


//Net risk
$q_update_net_risk2 = "UPDATE clearinguser.ifrs9_tloans 
set net_risk= loan_includ_interest - collateral - interest_susp
where loan_includ_interest - collateral - interest_susp >=0";
$stmt_update_net_risk_2 = $pdo->prepare($q_update_net_risk2);
$stmt_update_net_risk_2->execute(array());


$q_update_net_risk_3 = "UPDATE clearinguser.ifrs9_tloans 
set net_risk=0
where loan_includ_interest - collateral - interest_susp <0";
$stmt_update_net_risk_3 = $pdo->prepare($q_update_net_risk_3);
$stmt_update_net_risk_3->execute(array());



//Grace Period
$q_delete_grace_p = "DELETE FROM clearinguser.grace_period_temp";
$stmt_d_grace_p = $pdo->prepare($q_delete_grace_p);
$stmt_d_grace_p->execute(array());


$q_insert_amount = "INSERT INTO clearinguser.grace_period_temp
    select distinct cli,trim(eve)||trim(ave)||trim(ope)||trim(age) contract_id,abs(DMEP-DPEC) grace_period_months from prod.bkdosprt";
$stmt2 = $pdo->prepare($q_insert_amount);
$stmt2->execute(array());



$q_update_grace_p = "UPDATE clearinguser.ifrs9_tloans a
set a.grace_period_months=(
    select b.grace_period_months from clearinguser.grace_period_temp b
    where trim(a.cli)=trim(b.cli) and a.contract_id=b.contract_id
) where exists
(
select a.cli from clearinguser.ifrs9_tloans a
join clearinguser.grace_period_temp b on trim(a.cli)=trim(b.cli) and a.contract_id=b.contract_id
)";
$stmt_update_grace_p = $pdo->prepare($q_update_grace_p);
$stmt_update_grace_p->execute(array());


//Regulatory provisions
$q_update_1 = "UPDATE clearinguser.ifrs9_tloans a
set a.regulatory_prov = 0";
$stmt_update = $pdo->prepare($q_update_1);
$stmt_update->execute(array());


$q_update_2 = "UPDATE clearinguser.ifrs9_tloans a
set a.regulatory_prov = 0.01 * loan_includ_interest where newcla in (1,'1') and doc_type not in ('WF')";
$stmt_update = $pdo->prepare($q_update_2);
$stmt_update->execute(array());


$q_update_3 = "UPDATE clearinguser.ifrs9_tloans a
set a.regulatory_prov = 0.03 * loan_includ_interest where newcla in (2,'2') and doc_type not in ('WF')";
$stmt_update = $pdo->prepare($q_update_3);
$stmt_update->execute(array());


$q_update_4 = "UPDATE clearinguser.ifrs9_tloans a
set a.regulatory_prov = 0.2 * net_risk where newcla in (3,'3') and doc_type not in ('WF')";
$stmt_update = $pdo->prepare($q_update_4);
$stmt_update->execute(array());


$q_update_5 = "UPDATE clearinguser.ifrs9_tloans a
set a.regulatory_prov = 0.5 * net_risk where newcla in (4,'4') and doc_type not in ('WF')";
$stmt_update = $pdo->prepare($q_update_5);
$stmt_update->execute(array());


$q_update_6 = "UPDATE clearinguser.ifrs9_tloans a
set a.regulatory_prov = net_risk where newcla in (5,6,7,'5','6','7') and doc_type not in ('WF')";
$stmt_update = $pdo->prepare($q_update_6);
$stmt_update->execute(array());


$q_update_7 = "UPDATE clearinguser.ifrs9_tloans a
set a.regulatory_prov = 0 where (doc_type<>'WF' and newcla not in (1,2,3,4,5,'1','2','3','4','5')) or doc_type in ('WF')";
$stmt_update = $pdo->prepare($q_update_7);
$stmt_update->execute(array());

// Chapitre 90 & 92
$q_update_8 = "UPDATE clearinguser.ifrs9_tloans a
set a.regulatory_prov = 0 where (cha like ? or cha like ? )";
$stmt_update = $pdo->prepare($q_update_8);
$stmt_update->execute(array('90%', '92%'));



//Installments
$q_delete_installments = "DELETE from clearinguser.contract_installments";
$stmt_delete_installments = $pdo->prepare($q_delete_installments);
$stmt_delete_installments->execute(array());

$q_insert_installments = "INSERT into clearinguser.contract_installments 
select b.eve||b.ave||b.ope||b.age contract_id, max(tot_ech) installment from prod.bkechprt a
join prod.bkdosprt b on a.ave=b.ave and a.eve=b.eve and b.ctr<>9 and a.ctr=9
group by b.eve||b.ave||b.ope||b.age";
$stmt_insert_installments = $pdo->prepare($q_insert_installments);
$stmt_insert_installments->execute(array());


$q_update_installments = "UPDATE clearinguser.ifrs9_tloans a
set a.installment=(
    select b.installment from clearinguser.contract_installments b
    where a.contract_id=b.contract_id
) where exists
(
select a.cli from clearinguser.ifrs9_tloans a
join clearinguser.contract_installments b on a.contract_id=b.contract_id
)";
$stmt_update_installments = $pdo->prepare($q_update_installments);
$stmt_update_installments->execute(array());



//Tot number of Installments
$q_del_t_n_inst = "DELETE from clearinguser.tot_n_installements";
$stmt_del_t_n_inst = $pdo->prepare($q_del_t_n_inst);
$stmt_del_t_n_inst->execute(array());

$q_insert_tot_n_inst = "INSERT into clearinguser.tot_n_installements
select b.eve||b.ave||b.ope||b.age contract_id,  
count(*) tot_number_installments from prod.bkechprt a
join prod.bkdosprt b on a.ave=b.ave and a.eve=b.eve
group by b.eve||b.ave||b.ope||b.age";
$stmt_insert_tot_n_inst = $pdo->prepare($q_insert_tot_n_inst);
$stmt_insert_tot_n_inst->execute(array());


$q_update_n_installments = "UPDATE clearinguser.ifrs9_tloans a
set a.NUM_OF_INSTALMENTS=(
    select b.tot_number_installments from clearinguser.tot_n_installements b
    where a.contract_id=b.contract_id
) where exists
(
select a.cli from clearinguser.ifrs9_tloans a
join clearinguser.tot_n_installements b on a.contract_id=b.contract_id
)";
$stmt_update_n_installments = $pdo->prepare($q_update_n_installments);
$stmt_update_n_installments->execute(array());


//Tot number of paid Installments
$q_del_t_n_inst = "DELETE from clearinguser.tot_n_installements";
$stmt_del_t_n_inst = $pdo->prepare($q_del_t_n_inst);
$stmt_del_t_n_inst->execute(array());

$q_insert_tot_n_inst = "INSERT into clearinguser.tot_n_installements
select b.eve||b.ave||b.ope||b.age contract_id,  
count(*) tot_number_installments from prod.bkechprt a
join prod.bkdosprt b on a.ave=b.ave and a.eve=b.eve and a.eta='VA' and a.ctr=9
group by b.eve||b.ave||b.ope||b.age";
$stmt_insert_tot_n_inst = $pdo->prepare($q_insert_tot_n_inst);
$stmt_insert_tot_n_inst->execute(array());


$q_update_n_installments = "UPDATE clearinguser.ifrs9_tloans a
set a.TOTAL_INSTALMENTS_PAID=(
    select b.tot_number_installments from clearinguser.tot_n_installements b
    where a.contract_id=b.contract_id
) where exists
(
select a.cli from clearinguser.ifrs9_tloans a
join clearinguser.tot_n_installements b on a.contract_id=b.contract_id
)";
$stmt_update_n_installments = $pdo->prepare($q_update_n_installments);
$stmt_update_n_installments->execute(array());



//Tot number of outstanding Installments
$q_del_t_n_inst = "DELETE from clearinguser.tot_n_installements";
$stmt_del_t_n_inst = $pdo->prepare($q_del_t_n_inst);
$stmt_del_t_n_inst->execute(array());

$q_insert_tot_n_inst = "INSERT into clearinguser.tot_n_installements
select b.eve||b.ave||b.ope||b.age contract_id,  
count(*) tot_number_installments from prod.bkechprt a
join prod.bkdosprt b on a.ave=b.ave and a.eve=b.eve and a.eta='VA' and a.ctr<>9
group by b.eve||b.ave||b.ope||b.age";
$stmt_insert_toto_n_inst = $pdo->prepare($q_insert_tot_n_inst);
$stmt_insert_toto_n_inst->execute(array());


$q_update_n_installments = "UPDATE clearinguser.ifrs9_tloans a
set a.TOTAL_INSTALMENTS_OUTSTANDING=(
    select b.tot_number_installments from clearinguser.tot_n_installements b
    where a.contract_id=b.contract_id
) where exists
(
select a.cli from clearinguser.ifrs9_tloans a
join clearinguser.tot_n_installements b on a.contract_id=b.contract_id
)";
$stmt_update_n_installments = $pdo->prepare($q_update_n_installments);
$stmt_update_n_installments->execute(array());



//Tot number of Installments in arrears
$q_del_t_n_inst = "DELETE from clearinguser.tot_n_installements";
$stmt_del_t_n_inst = $pdo->prepare($q_del_t_n_inst);
$stmt_del_t_n_inst->execute(array());

$q_insert_tot_n_inst = "INSERT into clearinguser.tot_n_installements
select b.eve||b.ave||b.ope||b.age contract_id,  
count(*) tot_number_installments from prod.bkechprt a
join prod.bkdosprt b on a.ave=b.ave and a.eve=b.eve and a.eta='IM' and a.ctr='8'
group by b.eve||b.ave||b.ope||b.age";
$stmt_insert_toto_n_inst = $pdo->prepare($q_insert_tot_n_inst);
$stmt_insert_toto_n_inst->execute(array());


$q_update_n_installments = "UPDATE clearinguser.ifrs9_tloans a
set a.INSTALMENTS_IN_ARREARS=(
    select b.tot_number_installments from clearinguser.tot_n_installements b
    where a.contract_id=b.contract_id
) where exists
(
select a.cli from clearinguser.ifrs9_tloans a
join clearinguser.tot_n_installements b on a.contract_id=b.contract_id
)";
$stmt_update_n_installments = $pdo->prepare($q_update_n_installments);
$stmt_update_n_installments->execute(array());




//Date Past due
$q_del_date_past_due = "DELETE from clearinguser.contracts_date_past_due";
$stmt_del_date_past_due = $pdo->prepare($q_del_date_past_due);
$stmt_del_date_past_due->execute(array());

$q_insert_date_past_due = "INSERT into clearinguser.contracts_date_past_due
select b.eve||b.ave||b.ope||b.age contract_id, to_char(max(dva),'DD-MON-YYYY') date_past_due from prod.bkechprt a
join prod.bkdosprt b on a.ave=b.ave and a.eve=b.eve 
where a.ctr=8
group by b.eve||b.ave||b.ope||b.age";
$stmt_insert_date_past_due = $pdo->prepare($q_insert_date_past_due);
$stmt_insert_date_past_due->execute(array());


$q_update_date_past_due = "UPDATE clearinguser.ifrs9_tloans a
set a.DATE_PAST_DUE=(
    select b.DATE_PAST_DUE from clearinguser.contracts_date_past_due b
    where a.contract_id=b.contract_id
) where exists
(
select a.cli from clearinguser.ifrs9_tloans a
join clearinguser.contracts_date_past_due b on a.contract_id=b.contract_id
)";
$stmt_update_date_past_due = $pdo->prepare($q_update_date_past_due);
$stmt_update_date_past_due->execute(array());


$q_clean_zero = "DELETE from clearinguser.ifrs9_tloans
where SDECV=0 and INTEREST_DUE_LCY=0
and REGULATORY_PROV=0 and PROV_HELD=0 and INTEREST_SUSP=0";
$stmt_clean_zero = $pdo->prepare($q_clean_zero);
$stmt_clean_zero->execute(array());


$response = new stdClass();


$response->result=true;
$response->message = 'Successful';


echo json_encode($response);



?>