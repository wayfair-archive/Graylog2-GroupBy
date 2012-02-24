<?php
    require 'lib/groupby.php';
    require 'lib/config.php';

?>
<html>
    <head>
        <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css"/>
        <script type="text/javascript" src="https://www.google.com/jsapi"></script>
    </head>
    <body>
        <div class="navbar navbar-fixed-top">
            <div class="navbar-inner">
                <div class="container">
                    <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </a>
                    <a class="brand" href="#">Graylog2 GroupBy</a>
                    <div class="nav-collapse">
                        <ul class="nav">
                            <li class="active"><a href="<?php echo BASE; ?>">Home</a></li>
                            <li class="inactive"><a href="<?php echo BASE; ?>/?details=true">Just Graphs</a></li>
                        </ul>
                    </div><!--/.nav-collapse -->
                </div>
            </div>
        </div>

        <br/>
        <section id="home" style="padding-top:60px">
            <div class="container">
                <div class="span10">
                    <form class="well" method="post">
                        <label>Hostname (? and * work)</label>
                        <input type="text" class="input-xlarge" value="<?php echo $hostname; ?>" name="hostname"/>
                        <label>Time range</label>
                        <input type="text" class="input-xlarge" value="<?php echo $timeframe_start; ?>" name="timeframe_start"/>
                        <input type="text" class="input-xlarge" value="<?php echo $timeframe_end; ?>" name="timeframe_end"/>
                        <label>Text to group on</label>
                        <textarea class="input-xlarge span8" rows="3" name="grouping"><?php echo $grouping ?></textarea>
                        <button type="submit" class="btn">Search</button>
                    </form>
                </div>
            </div>
        </section>
    </body>

    <?php
        $msg_search = str_replace('*', ' ', $grouping);
        $words = preg_split("/\s+/", $msg_search, 0, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
        $query = generate_query($hostname, $timeframe_start, $timeframe_end, $words, FALSE);
        $results = do_query($query, FALSE);
        $shorturl = shorten_url($_POST);

        $r = $results['hits']['hits'];
        if (!isset($_GET['details'])) {
            echo "<b>FOUND: ".count($results['hits']['hits'])."</b><br/><a href='".BASE."/?su=".$shorturl."'> Short Url</a></br></br>\n";
    ?>

        <div class="container">
            <div class="span8">
                <table class="table table-striped table-bordered table-condensed">
                <?php
                    $seen = array();
                    $seenval = array();
                    foreach ($r as $result) {
                        $hash = md5($result['_source']['message']);
                        if (isset($seen[$hash])) {
                            $seen[$hash]['value']++;
                            $seenval[$hash]++;
                            $seen[$hash]['time'] = $result['_source']['created_at'];
                        } else {
                            $seen[$hash]['value'] = 1;
                            $seenval[$hash] = 1;
                            $seen[$hash]['message'] = $result['_source']['message'];
                            $seen[$hash]['host'] = $result['_source']['host'];
                            $seen[$hash]['time'] = $result['_source']['created_at'];
                        }
                    }
                    arsort($seenval);
                    foreach ($seenval as $hash => $v) {
                        $result = $seen[$hash];
                        $dup = $_POST;
                        $dup['grouping'] = $result['message'];
                        $tempshort = shorten_url($dup);
                        echo print_row(array(
                            strftime('%T',$result['time']),
                            $result['host'],
                            "<a href=\"".BASE."/?details=true&su=".urlencode($tempshort)."\">".substr($dup['grouping'], 0,800)."</a>",
                            $result['value'],
                        ),4)."\n";
                        unset($dup);
                    }
                ?>
                </table>
            </div>
        </div>
    <?php
         } else {
             echo "<b>FOUND: ".count($results['hits']['hits'])."</b><br/><a href='".BASE."/?details=true&su=".$shorturl."'> Short Url</a></br></br>\n";
             echo "<div class=\"container\">Error You Searched For:<br/><div class=\"span12\"><code>".$grouping."</code></div></div><br/>";
             $hosts = array();
             $times = array();
             foreach ($r as $result) {
                 $hostname = $result['_source']['host'];
                 $time = $result['_source']['created_at'] * 1000;
                 if (isset ($hosts[$hostname])) {
                     $hosts[$hostname]++;
                 } else {
                     $hosts[$hostname] = 1;
                 }
                 if (isset ($times[$time])) {
                     $times[$time]++;
                 } else {
                     $times[$time] = 1;
                }
             }
    ?>
        <script type="text/javascript">
            google.load("visualization", "1", {packages:["corechart"]});
            google.setOnLoadCallback(drawChart);
            function drawChart() {
                var data = new google.visualization.DataTable();
                data.addColumn('string', 'Hostname');
                data.addColumn('number', 'Errors');
                data.addRows([
                <?php
                    foreach ($hosts as $host => $count) {
                        echo "['$host', $count],";
                    }
                ?>
                    ]);
                var options = {
                    width: 450, height: 300,
                    title: 'Errors Per Host'
                };

                var chart = new google.visualization.PieChart(document.getElementById('chart_div'));
                chart.draw(data, options);
            }

        </script>

        <script type="text/javascript">
            google.load("visualization", "1", {packages:["corechart"]});
            google.setOnLoadCallback(drawChart2);
            function drawChart2() {
                var data = new google.visualization.DataTable();
                data.addColumn('datetime', 'Time');
                data.addColumn('number', 'Errors');
                data.addRows([
                <?php
                    foreach ($times as $time => $count) {
                        echo "[new Date(".$time."), $count],\n";
                    }
                ?>
                    ]);
                var options = {
                    //width: 450, height: 300,
                    title: 'Errors By Time'
                };

                var chart = new google.visualization.LineChart(document.getElementById('chart_div2'));
                chart.draw(data, options);
            }

        </script>

        <div class="container">
            <div class="span12">
                <div class="span5" id="chart_div">
                </div>
                <div class="span5" id="chart_div2">
                </div>
            </div>
        </div>
    <?php
            foreach ($r as $result) {

#                $dup = $_POST;
#                $dup['grouping'] = $result['_source']['message'];
#                $tempshort = shorten_url($dup);
#                echo print_row(array(
#                    strftime('%T',$result['_source']['created_at']),
#                    $result['_source']['host'],
#                    "<a href=\"/groupby/?details=true&su=".urlencode($tempshort)."\">".substr($dup['grouping'], 0,300)."</a>",
#                ),3)."\n";
#                # $result['_source']['message']
#                unset($dup);
            }
        }
    ?>
        </table>
    </div>

</html>
