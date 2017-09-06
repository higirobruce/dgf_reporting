

<?php 
    header('Access-Control-Allow-Origin: *');
    $pdo = new PDO('oci:dbname=192.168.0.20:1521/cgbk', 'BHIGIRO', 'ABC123456');  
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);  
?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css" integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M" crossorigin="anonymous">
        <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js" integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4" crossorigin="anonymous"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js" integrity="sha384-h0AbiXch4ZDo7tp9hKZ4TsHbi047NrKGLO3SEJAg45jXxnGIfYzk4Si90RDIqNm1" crossorigin="anonymous"></script>
        <title>Reports</title>
        <script>
            function mydate()
            {
                //alert("");
                document.getElementById("dt").hidden=false;
                document.getElementById("ndt").hidden=true;
                mydate1()
            }
            function mydate1()
            {
                d=new Date(document.getElementById("dt").value);
                dt=d.getDate();
                mn=d.getMonth();
                mn++;
                yy=d.getFullYear();
                document.getElementById("ndt").value=dt+"/"+mn+"/"+yy
                document.getElementById("ndt").hidden=false;
                document.getElementById("dt").hidden=true;
            }
        </script>
    </head>
    <body>
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <a class="navbar-brand" href="#">BK Reports</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav mr-auto">
                <li class="nav-item active">
                    <a class="nav-link" href="#">Home <span class="sr-only">(current)</span></a>
                </li>
                </ul>
            </div>
        </nav>

        <div class="container">
            <?php
            
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
            if(isset($_POST['dt']))
            {
                $dco = $_POST['ndt'];
            }else{
                $dco = '31/7/2017';
            }
            
            $dev = '124';
            if ($stmt->execute(array(
                '20%','12%','18%','21%','22%','23%','24%','25%', '148635', '148636','290%',
                '0','208%','219%','229%','239%','249%',$dco,'27%','28%','14%','208%','148635',
                '148636',$dco,0,$dco
            ))){

                ?>
                <div class="row">
                    <div class="col-md-4">
                        <br>
                        <br>
                        <form action="<?php echo $_SERVER['PHP_SELF']?>" method="POST">
                            <div class="form-group">
                                <label for="">Select the end date</label>
                                <!-- <input type="date" name="date" class="form-control"> -->
                                <input type="date" id="dt" name="dt" onchange="mydate1();" class="form-control" />
                                <input type="text" id="ndt" name="ndt" onclick="mydate();" class="form-control" hidden />
                                <input type="button" Value="Date" onclick="mydate();" hidden />
                            </div>
                            <div class="form-group">
                                <button class="btn btn-primary btn-block" type="submit">Submit</button>
                            </div>
                        </form>
                    </div>

                    <div class="col-md-8">
                        <br><br>
                        <h4>Preview 
                            <?php 
                            if(isset($_POST['dt'])){
                                print_r($_POST['ndt']);
                            }
                            ?>
                        </h4>
                        <table class="table">
                            <tr>
                                <th>TCLI</th>
                                <th>DEV</th>
                                <th>TIND</th>
                                <th>NUM</th>
                                <th>SDE</th>
                                <th>SDECV</th>
                            </tr>

                            <?php
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                // print_r('<pre>');
                                // print_r($row['AGE']);
                                // print_r('<pre>');
                                // $s->age = $row['AGE'];
                                // array_push($ret, $s);
                                // $arr[] = $row;
                                //array_push($arr, $row);
                                //echo json_encode($arr);
                                echo '
                                    <tr *ngFor="let r of results">
                                        <td>'.$row['TCLI'].'</td>
                                        <td>'.$row['DEV'].'</td>
                                        <td>'.$row['TIND'].'</td>
                                        <td>'.$row['NUM'].'</td>
                                        <td>'.$row['SDE'].'</td>
                                        <td>'.$row['SDECV'].'</td>
                                    </tr>

                                ';
                            }
                            
                        }
                        ?>
                            
                            
                        </table>
                    </div>
                </div>            

        </div>
    </body>
    </html>
<?php
    function getAll(){
        $query = "SELECT * FROM prod.bksld";
        
        $stmt = $pdo->prepare($query); 
        $arr = array();
        $ret = array();
        if ($stmt->execute()) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                print_r('<pre>');
                print_r($row['AGE']);
                print_r('<pre>');
                $s->age = $row['AGE'];
                array_push($ret, $s);
                // $arr[] = $row;
                //array_push($arr, $row);
                //echo json_encode($arr);
            }
            
        }
    }
?> 

