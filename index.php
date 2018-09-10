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
                                <form action="download_loans_ifrs.php" method="POST">
                                    <div class="row form-group">
                                        <div class="col-md-6">
                                            <label for="ndt2">Select the month</label>
                                            <select class="form-control" name="month" id="month">
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
                                            <select class="form-control" name="year" id="year">
                                                <option value="2011">2011</option>
                                                <option value="2012">2012</option>
                                                <option value="2013">2013</option>
                                                <option value="2014">2014</option>
                                                <option value="2015">2015</option>
                                                <option value="2016">2016</option>
                                                <option value="2017">2017</option>
                                                <option value="2018">2018</option>
                                                <option value="2019">2019</option>
                                                <option value="2020">2020</option>
                                            </select>
                                        </div>
                                    </div>
                                    <button class="btn btn-default btn-block" name="download">Download to EXCEL</button>
                            
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
                                <form action="download_ods_ifrs.php" method="POST">
                                    <div class="row form-group">
                                        <div class="col-md-6">
                                            <label for="ndt2">Select the month</label>
                                            <select class="form-control" name="month" id="month">
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
                                            <select class="form-control" name="year" id="year">
                                                <option value="2011">2011</option>
                                                <option value="2012">2012</option>
                                                <option value="2013">2013</option>
                                                <option value="2014">2014</option>
                                                <option value="2015">2015</option>
                                                <option value="2016">2016</option>
                                                <option value="2017">2017</option>
                                                <option value="2018">2018</option>
                                                <option value="2019">2019</option>
                                                <option value="2020">2020</option>
                                            </select>
                                        </div>
                                    </div>
                                    <button class="btn btn-danger btn-block" name="download">Download to EXCEL</button>
                            
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
                                <form action="download_offbs_ifrs.php" method="POST">
                                    <div class="row form-group">
                                        <div class="col-md-6">
                                            <label for="ndt2">Select the month</label>
                                            <select class="form-control" name="month" id="month">
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
                                            <select class="form-control" name="year" id="year">
                                                <option value="2011">2011</option>
                                                <option value="2012">2012</option>
                                                <option value="2013">2013</option>
                                                <option value="2014">2014</option>
                                                <option value="2015">2015</option>
                                                <option value="2016">2016</option>
                                                <option value="2017">2017</option>
                                                <option value="2018">2018</option>
                                                <option value="2019">2019</option>
                                                <option value="2020">2020</option>
                                            </select>
                                        </div>
                                    </div>
                                    <button class="btn btn-default btn-block" name="download">Download to EXCEL</button>
                            
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
