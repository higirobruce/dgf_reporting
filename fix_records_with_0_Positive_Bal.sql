select * from 
clearinguser.ifrs9_tloans 
where cli not in (

select cli from (select count(ncp) c, cli from clearinguser.ifrs9_tloans 
group by cli
) where c>1)
and sdecv = 0;



select * from 
clearinguser.ifrs9_tloans 
where loan_includ_interest =0 and prov_held>0;


select * from 
clearinguser.ifrs9_tloans 
where cli='0000793'