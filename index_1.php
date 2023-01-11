<?php

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="icon" href="images/logo.png" type="image/x-icon" />
    <link rel="stylesheet" href="styles/style.css">
    <link rel="stylesheet" href="bower_components/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css" />

    <script src="bower_components/jquery/dist/jquery.min.js"></script>
    <script type="text/javascript" src="bower_components/moment/min/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js" integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js" integrity="sha384-h0AbiXch4ZDo7tp9hKZ4TsHbi047NrKGLO3SEJAg45jXxnGIfYzk4Si90RDIqNm1" crossorigin="anonymous"></script>
    <script type="text/javascript" src="bower_components/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {

            let month_tl = document.getElementById('month_tl').value;
            let year_tl = document.getElementById('year_tl').value;

            document.getElementById('hidden_month_tl').value = month_tl;
            document.getElementById('hidden_year_tl').value = year_tl;

            let month_od = document.getElementById('month_od').value;
            let year_od = document.getElementById('year_od').value;

            document.getElementById('hidden_month_od').value = month_od;
            document.getElementById('hidden_year_od').value = year_od;

            let month_of = document.getElementById('month_of').value;
            let year_of = document.getElementById('year_of').value;

            document.getElementById('hidden_month_of').value = month_of;
            document.getElementById('hidden_year_of').value = year_of;


        });

        function updateMonthYear() {
            let month_tl = document.getElementById('month_tl').value;
            let year_tl = document.getElementById('year_tl').value;

            document.getElementById('hidden_month_tl').value = month_tl;
            document.getElementById('hidden_year_tl').value = year_tl;

            let month_od = document.getElementById('month_od').value;
            let year_od = document.getElementById('year_od').value;

            document.getElementById('hidden_month_od').value = month_od;
            document.getElementById('hidden_year_od').value = year_od;

            let month_of = document.getElementById('month_of').value;
            let year_of = document.getElementById('year_of').value;

            document.getElementById('hidden_month_of').value = month_of;
            document.getElementById('hidden_year_of').value = year_of;


            console.log(month_tl);
            console.log(year_tl);
            console.log(month_od);
            console.log(year_od);
            console.log(month_of);
            console.log(year_of);
        }
    </script>
    <title>Loan Book</title>

</head>

<body>
    <nav class="navbar navbar-default">
        <div class="container-fluid">
            <div class="navbar-header">
                <a class="navbar-brand" href="#">
                    <img alt="Brand" src="images/icon.png" style="width:110px;height:30px">
                </a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="row">
            <h2 class="text-center"><u>IFRS9 Raw Data</u></h2>
        </div>
        <div class="row">

            <div class="col-md-4">
                <br>
                <br>
                <div class="panel panel-default">
                    <div class="text-center panel-heading">
                        <strong>Term Loans</strong>
                    </div>
                    <div class="panel-body">
                        <form action="download_loans_ifrs.php" method="GET">
                            <div class="row form-group">
                                <div class="col-md-6">
                                    <label for="ndt2">Select the month</label>
                                    <select class="form-control" onchange="updateMonthYear()" name="month_tl" id="month_tl">
                                        <option value="1">January</option>
                                        <option value="2">February</option>
                                        <option value="3">March</option>
                                        <option value="4">April</option>
                                        <option value="5">May</option>
                                        <option value="6">June</option>
                                        <option value="7">July</option>
                                        <option value="8">August</option>
                                        <option value="9">September</option>
                                        <option value="10">October</option>
                                        <option value="11">November</option>
                                        <option value="12">December</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="ndt2">Select the year</label>
                                    <select class="form-control" onchange="updateMonthYear()" name="year_tl" id="year_tl">
                                        <option value=<?php echo date('Y') ?>><?php echo date('Y') ?></option>
                                        <option value=<?php echo date('Y') - 1 ?>><?php echo date('Y') - 1 ?></option>
                                        <option value=<?php echo date('Y') - 2 ?>><?php echo date('Y') - 2 ?></option>
                                        <option value=<?php echo date('Y') - 3 ?>><?php echo date('Y') - 3 ?></option>
                                        <option value=<?php echo date('Y') - 4 ?>><?php echo date('Y') - 4 ?></option>
                                        <option value=<?php echo date('Y') - 5 ?>><?php echo date('Y') - 5 ?></option>
                                        <option value=<?php echo date('Y') - 6 ?>><?php echo date('Y') - 6 ?></option>
                                        <option value=<?php echo date('Y') - 7 ?>><?php echo date('Y') - 7 ?></option>
                                        <option value=<?php echo date('Y') - 8 ?>><?php echo date('Y') - 8 ?></option>
                                        <option value=<?php echo date('Y') - 9 ?>><?php echo date('Y') - 9 ?></option>
                                        <option value=<?php echo date('Y') - 10 ?>><?php echo date('Y') - 10 ?></option>
                                    </select>
                                </div>
                            </div>
                            <button class="btn btn-default btn-block" name="download">Download to EXCEL</button>

                            <div class="container">
                                <div class="row">

                                </div>
                            </div>

                        </form>
                        <br>
                        <form action="insert_loans_ifrs.php" method="GET">
                            <input type="hidden" id="hidden_month_tl" name="hidden_month_tl">
                            <input type="hidden" id="hidden_year_tl" name="hidden_year_tl">
                            <button class="btn btn-default btn-block" name="save">Save TL - CONTLOAN data</button>

                            <div class="container">
                                <div class="row">

                                </div>
                            </div>

                        </form>
                    </div>
                </div>

            </div>

            <div class="col-md-4">
                <br>
                <br>
                <div class="panel panel-danger">
                    <div class="text-center panel-heading">
                        <strong>Overdrafts</strong>
                    </div>
                    <div class="panel-body">
                        <form action="download_ods_ifrs.php" method="GET">
                            <div class="row form-group">
                                <div class="col-md-6">
                                    <label for="ndt2">Select the month</label>
                                    <select class="form-control" onchange="updateMonthYear()" name="month_od" id="month_od">
                                        <option value="1">January</option>
                                        <option value="2">February</option>
                                        <option value="3">March</option>
                                        <option value="4">April</option>
                                        <option value="5">May</option>
                                        <option value="6">June</option>
                                        <option value="7">July</option>
                                        <option value="8">August</option>
                                        <option value="9">September</option>
                                        <option value="10">October</option>
                                        <option value="11">November</option>
                                        <option value="12">December</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="ndt2">Select the year</label>
                                    <select class="form-control" onchange="updateMonthYear()" name="year_od" id="year_od">
                                        <option value=<?php echo date('Y') ?>><?php echo date('Y') ?></option>
                                        <option value=<?php echo date('Y') - 1 ?>><?php echo date('Y') - 1 ?></option>
                                        <option value=<?php echo date('Y') - 2 ?>><?php echo date('Y') - 2 ?></option>
                                        <option value=<?php echo date('Y') - 3 ?>><?php echo date('Y') - 3 ?></option>
                                        <option value=<?php echo date('Y') - 4 ?>><?php echo date('Y') - 4 ?></option>
                                        <option value=<?php echo date('Y') - 5 ?>><?php echo date('Y') - 5 ?></option>
                                        <option value=<?php echo date('Y') - 6 ?>><?php echo date('Y') - 6 ?></option>
                                        <option value=<?php echo date('Y') - 7 ?>><?php echo date('Y') - 7 ?></option>
                                        <option value=<?php echo date('Y') - 8 ?>><?php echo date('Y') - 8 ?></option>
                                        <option value=<?php echo date('Y') - 9 ?>><?php echo date('Y') - 9 ?></option>
                                        <option value=<?php echo date('Y') - 10 ?>><?php echo date('Y') - 10 ?></option>
                                    </select>
                                </div>
                            </div>
                            <button class="btn btn-danger btn-block" name="download">Download to EXCEL</button>

                            <div class="container">
                                <div class="row">

                                </div>
                            </div>

                        </form>
                        <br>
                        <form action="insert_ods_ifrs.php" method="GET">
                            <input type="hidden" id="hidden_month_od" name="hidden_month_od">
                            <input type="hidden" id="hidden_year_od" name="hidden_year_od">
                            <button class="btn btn-default btn-block" name="save">Save OD - CONTLOAN data</button>

                            <div class="container">
                                <div class="row">

                                </div>
                            </div>

                        </form>
                    </div>
                </div>

            </div>

            <div class="col-md-4">
                <br>
                <br>
                <div class="panel panel-default">
                    <div class="text-center panel-heading">
                        <strong>Off Balance-sheet</strong>
                    </div>
                    <div class="panel-body">
                        <form action="download_offbs_ifrs.php" method="GET">
                            <div class="row form-group">
                                <div class="col-md-6">
                                    <label for="ndt2">Select the month</label>
                                    <select class="form-control" onchange="updateMonthYear()" name="month_of" id="month_of">
                                        <option value="1">January</option>
                                        <option value="2">February</option>
                                        <option value="3">March</option>
                                        <option value="4">April</option>
                                        <option value="5">May</option>
                                        <option value="6">June</option>
                                        <option value="7">July</option>
                                        <option value="8">August</option>
                                        <option value="9">September</option>
                                        <option value="10">October</option>
                                        <option value="11">November</option>
                                        <option value="12">December</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="ndt2">Select the year</label>
                                    <select class="form-control" onchange="updateMonthYear()" name="year_of" id="year_of">
                                        <option value=<?php echo date('Y') ?>><?php echo date('Y') ?></option>
                                        <option value=<?php echo date('Y') - 1 ?>><?php echo date('Y') - 1 ?></option>
                                        <option value=<?php echo date('Y') - 2 ?>><?php echo date('Y') - 2 ?></option>
                                        <option value=<?php echo date('Y') - 3 ?>><?php echo date('Y') - 3 ?></option>
                                        <option value=<?php echo date('Y') - 4 ?>><?php echo date('Y') - 4 ?></option>
                                        <option value=<?php echo date('Y') - 5 ?>><?php echo date('Y') - 5 ?></option>
                                        <option value=<?php echo date('Y') - 6 ?>><?php echo date('Y') - 6 ?></option>
                                        <option value=<?php echo date('Y') - 7 ?>><?php echo date('Y') - 7 ?></option>
                                        <option value=<?php echo date('Y') - 8 ?>><?php echo date('Y') - 8 ?></option>
                                        <option value=<?php echo date('Y') - 9 ?>><?php echo date('Y') - 9 ?></option>
                                        <option value=<?php echo date('Y') - 10 ?>><?php echo date('Y') - 10 ?></option>
                                    </select>
                                </div>
                            </div>
                            <button class="btn btn-default btn-block" name="download">Download to EXCEL</button>

                            <div class="container">
                                <div class="row">

                                </div>
                            </div>

                        </form>
                        <br>
                        <form action="insert_offbs_ifrs.php" method="GET">
                            <input type="hidden" id="hidden_month_of" name="hidden_month_of">
                            <input type="hidden" id="hidden_year_of" name="hidden_year_of">
                            <button class="btn btn-default btn-block" name="save">Save OFB - CONTLOAN data</button>

                            <div class="container">
                                <div class="row">

                                </div>
                            </div>

                        </form>
                    </div>
                </div>

            </div>

        </div>
        <footer class="text-center" style="padding-top: 150px">
            &copy Cogebanque <?php echo date('Y') ?>
        </footer>
    </div>
</body>

</html>
<?php
?>