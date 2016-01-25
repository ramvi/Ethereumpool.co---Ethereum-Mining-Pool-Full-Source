<?php
error_reporting(error_reporting() & ~E_NOTICE);
$config = include('../../config.php');

include($config['BigInteger']);

$m = new Memcached();
$m->addServer('localhost', 11211);

if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
  $_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
}

$real_visitor_ip = $_SERVER["REMOTE_ADDR"];
$real_visitor_ip_mem = $m->get($real_visitor_ip);
if (!$real_visitor_ip_mem) {
    $m->set($real_visitor_ip,'true',1);
} else{
   die('too many request from your ip to this endpoint');
}

$ether_wei = 1000000000000000000;
$cacheTime = 5;
$avg_stats = 4;

  $mysqli=mysqli_connect($config['host'], $config['username'], $config['password'], $config['bdd']) or die("Database Error");
  $task = "SELECT count(1) FROM blocks";
  $response = mysqli_query($mysqli,$task)or die("Database Error");
  $row = mysqli_fetch_row($response);
  $minedblocks = $row[0];

  $result = mysqli_query($mysqli,"select count(1) FROM shares");
  $sharesCount = mysqli_fetch_array($result);
  $sharesCountTotal = $sharesCount[0];

  $data = array("jsonrpc" => "2.0", "method" => "eth_accounts", "params" => [], "id" => 64);                                                                    
  $data_string = json_encode($data);  
  $ch1 = curl_init('http://127.0.0.1:8983');                                                                      
  curl_setopt($ch1, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
  curl_setopt($ch1, CURLOPT_POSTFIELDS, $data_string);                                                                  
  curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);                                                                      
  curl_setopt($ch1, CURLOPT_HTTPHEADER, array(                                                                          
    'Content-Type: application/json',                                                                                
    'Content-Length: ' . strlen($data_string))                                                                       
  );                                                                                                                   
    $result1 = curl_exec($ch1);

  $block_info_last = json_decode($result1, true); 
  $addrr = $block_info_last['result'];

    
$ch1 = curl_init('http://api.etherscan.io/api?module=account&action=balance&address='.$addrr[0].'&tag=latest');                                                                                                                                          
curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);                                                                            
$result3 = curl_exec($ch1);

if (!$result3) {
  $data = array("jsonrpc" => "2.0", "method" => "eth_getBalance", "params" => [$addrr[0],'latest'], "id" => 1);                                                                    
  $data_string = json_encode($data);  
  $ch1 = curl_init('http://127.0.0.1:8983');                                                                      
  curl_setopt($ch1, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
  curl_setopt($ch1, CURLOPT_POSTFIELDS, $data_string);                                                                  
  curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);                                                                      
  curl_setopt($ch1, CURLOPT_HTTPHEADER, array(                                                                          
    'Content-Type: application/json',                                                                                
    'Content-Length: ' . strlen($data_string))                                                                       
  );        
  $result3 = curl_exec($ch1);
    $block_info_last = json_decode($result3, true); 
    $escapeDot = explode('.', sprintf('%f', hexdec($block_info_last['result'])));
    $balanceaddr = new Math_BigInteger($escapeDot[0]);
  } else {
    $block_info_last = json_decode($result3, true); 
    $balanceaddr = new Math_BigInteger($block_info_last['result']);
  }

  $ether = floatval($balanceaddr->toString())/$ether_wei;

  $data = array("jsonrpc" => "2.0", "method" => "eth_getBlockByNumber", "params" => ["latest", true], "id" => "1");                                                                    
  $data_string = json_encode($data);  
  $ch1 = curl_init('http://127.0.0.1:8983');                                                                      
  curl_setopt($ch1, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
  curl_setopt($ch1, CURLOPT_POSTFIELDS, $data_string);                                                                  
  curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);                                                                      
  curl_setopt($ch1, CURLOPT_HTTPHEADER, array(                                                                          
    'Content-Type: application/json',                                                                                
    'Content-Length: ' . strlen($data_string))                                                                       
  );                                                                                                                                                                                                                               
  $result1 = curl_exec($ch1);
  $block_info_last = json_decode($result1, true); 
  $block_info_lasdsdst = $block_info_last['result'];
  $blockstamp = hexdec($block_info_lasdsdst['timestamp']);
  $data = array("jsonrpc" => "2.0", "method" => "eth_getBalance", "params" => [$block_info_last['miner'],'latest'], "id" => 1);                                                                    
  $data_string = json_encode($data);  
  $ch1 = curl_init('http://127.0.0.1:8983');                                                                      
  curl_setopt($ch1, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
  curl_setopt($ch1, CURLOPT_POSTFIELDS, $data_string);                                                                  
  curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);                                                                      
  curl_setopt($ch1, CURLOPT_HTTPHEADER, array(                                                                          
    'Content-Type: application/json',                                                                                
    'Content-Length: ' . strlen($data_string))                                                                       
  );                                                                                                                                                                                                                                  
  $result3 = curl_exec($ch1);

  $block_info_last = json_decode($result3, true); 
  $balanceaddr22 = $block_info_last['result'];


$existQuery = "SELECT userid,hashrate,val_timestamp,user FROM stats ORDER BY id DESC LIMIT 2000;";
$existResult = mysqli_query($mysqli,$existQuery)or die("Database Error");
$workers_count=0;
$hashrate_real = 0;
$hashrate_est = 0;
$miner_payouts = array();
$miner_payouts_checker = array();
while ($row=mysqli_fetch_row($existResult)){
    $cur_time = time();
    $difference = $cur_time - $row[2];
    if ($difference < 120) {
        $newArr = array($row[0],$row[1]);
        $workers_count++;
        $user_userid = $row[0].$row[3];
        $miner_adr = $row[3];
        if (!isset($miner_payouts["'$miner_adr'"])) {
            $miner_payouts["'$miner_adr'"] = $row[1];
            $miner_payouts_checker["'$user_userid'"] = $user_userid;
            $hashrate_real = $hashrate_real + $row[1];
            $hashrate_est = $hashrate_est + $row[0];
        } else {
            if (!isset($miner_payouts_checker["'$user_userid'"])) {
              $miner_hashrate_fix = $miner_payouts["'$miner_adr'"] + $row[1];
              $miner_payouts["'$miner_adr'"] = $miner_hashrate_fix;
              $miner_payouts_checker["'$user_userid'"] = $user_userid;
              $hashrate_real = $hashrate_real + $row[1];
              $hashrate_est = $hashrate_est + $row[0];
            } else {
              //already been
            }
        }
    }
}
foreach ($miner_payouts as $key => $value) {
  $key = str_replace('"', '', $key);
  $key = str_replace("'", '', $key);
  $value = $value;
  $mhash_rl_temp = number_format((float)$value/1000000, 2, '.', '');
  $activeminers = $activeminers.'<br>&nbsp;&nbsp;&nbsp;&nbsp;<a href="/stats/miner/?address='.$key.'">'.$key.'</a> running at:'.$mhash_rl_temp.' MHash';
  $minerCount++;
}


echo '<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->  
<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->  
<!--[if !IE]><!--> <html lang="en"> <!--<![endif]-->  
<head>
    <title>Statistics - EthPool.utocat.com</title>
    <!-- Meta -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="EthPool.utocat.com is stable, transparent and fair mining pool with low fee and great support! Just switch your rig to us, and see it on yourself :)">
    <meta name="author" content="EthPool.utocat.com">
    <meta property="og:type"               content="website" />
    <meta property="og:title"              content="EthPool.utocat.com - Ethereum Mining Pool"/>
    <meta property="og:description"        content="EthPool.utocat.com is stable, transparent and fair mining pool with low fee and great support! Just switch your rig to us, and see it on yourself :)"/>
    <link rel="shortcut icon" href="../favicon.ico">  
    <meta name="keywords" content="eth,gpu,mining,mine,ethereum,calculator,profitability,profit,how,to,ether,ethers">
    <link href="http://fonts.googleapis.com/css?family=Merriweather+Sans:700,300italic,400italic,700italic,300,400" rel="stylesheet" type="text/css">
    <link href="http://fonts.googleapis.com/css?family=Russo+One" rel="stylesheet" type="text/css">
    <link href="http://fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,600italic,700italic,800italic,400,300,600,700,800" rel="stylesheet" type="text/css">
    <!-- Global CSS -->
    <link rel="stylesheet" href="../assets/plugins/bootstrap/css/bootstrap.min.css">   
    <!-- Plugins CSS -->    
    <link rel="stylesheet" href="../assets/plugins/font-awesome/css/font-awesome.css">
    <link rel="stylesheet" href="../assets/plugins/elegant_font/css/style.css">
    <!-- Theme CSS -->
    <link id="theme-style" rel="stylesheet" href="../assets/css/styles-2.css">
    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    <style>
    .button-fill {
  text-align: center;
  background: #ccc;
  display: inline-block;
  position: relative;
  text-transform: uppercase;
  margin: 8px;
}
.button-fill.grey {
  background: #444B54;
  color: white;
}
.button-fill.orange .button-inside {
  color: #f26b43;
}
.button-fill.orange .button-inside.full {
  border: 1px solid #f26b43;
}
.button-text {
  padding: 0 25px;
  line-height: 56px;
  letter-spacing: .1em;
}
.button-inside {
  width: 0px;
  height: 54px;
  margin: 0;
  float: left;
  position: absolute;
  top: 1px;
  left: 50%;
  line-height: 54px;
  color: #445561;
  background: #fff;
  text-align: center;
  overflow: hidden;
  -webkit-transition: width 0.5s, left 0.5s, margin 0.5s;
  -moz-transition: width 0.5s, left 0.5s, margin 0.5s;
  -o-transition: width 0.5s, left 0.5s, margin 0.5s;
  transition: width 0.5s, left 0.5s, margin 0.5s;
}
.button-inside.full {
  width: 100%;
  left: 0%;
  top: 0;
  margin-right: -50px;
  border: 1px solid #445561;
}
.inside-text {
  text-align: center;
  position: absolute;
  right: 50%;
  letter-spacing: .1em;
  text-transform: uppercase;
  -webkit-transform: translateX(50%);
  -moz-transform: translateX(50%);
  -ms-transform: translateX(50%);
  transform: translateX(50%);
}
</style>
</head> 
<body class="blog-home-page">   
    <div class="header-wrapper header-wrapper-blog-home">
        <!-- ******HEADER****** --> 
        <header id="header" class="header navbar-fixed-top">  
            <div class="container">       
                <h1 class="logo">
                    <a href="../"><img id="logo" src="/assets/images/logo.png">ethpool.<span class="highlight">utocat</span>.com</a>
                </h1><!--//logo-->
                <nav class="main-nav navbar-right" role="navigation">
                    <div class="navbar-header">
                        <button class="navbar-toggle" type="button" data-toggle="collapse" data-target="#navbar-collapse">
                            <span class="sr-only">Toggle navigation</span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                        </button><!--//nav-toggle-->
                    </div><!--//navbar-header-->
                    <div id="navbar-collapse" class="navbar-collapse collapse">
                        <ul class="nav navbar-nav">
                            <li class="nav-item"><a href="..">Home</a></li>
                            <li class="active nav-item"><a href="../stats">Stats</a></li>
                            <li class="nav-item"><a href="../charts">Charts</a></li>
                            <li class="nav-item"><a href="../stats/miner/">Miner Stats</a></li>              
                            <li class="nav-item last"><a href="../how">How to Mine?</a></li>
                            <li class="nav-item last"><a href="mailto:laurent@utocat.com">Support</a></li>
                        </ul><!--//nav-->
                    </div><!--//navabr-collapse-->
                </nav><!--//main-nav-->
            </div><!--//container-->
        </header><!--//header-->   
        
    <!-- ******Contact Section****** --> 
    <section class="contact-section section">
        <div class="container">
        <h2 class="title text-center"><br>Statistics</h2>
            <p class="intro text-left"></p>
             <p class="intro text-left"><font color="F22613"></p></font>
            <form id="contact-form" class="contact-form form" method="post" action="push.php">                    
                <div class="row text-left">
                    <div class="contact-form-inner col-md-8 col-sm-12 col-xs-12 col-md-offset-2 col-sm-offset-0 xs-offset-0">';                                                                                   

    $hashrate_real = $hashrate_real;
    $mhash_rl = number_format((float)$hashrate_real/1000000, 2, '.', '');
    $mhash_rl_g = number_format((float)$hashrate_real/1000000000, 2, '.', '');
    $ether_in_wei = str_replace('.', '', $ether);

    echo '<center>';
    echo '<a href="/charts"><div class="button-fill grey" style="width:94%"><div class="button-text">'.$mhash_rl.' MHash/s</b></div><div class="button-inside"><div class="inside-text">'.$mhash_rl_g.' GHash/s</div></div></div></a>';
    echo '<a href="http://etherscan.io/address/'.$addrr[0].'" target="_blanklank"><div class="button-fill grey" style="width:94%"><div class="button-text">'.$ether.' ethers</b></div><div class="button-inside"><div class="inside-text">'.$ether_in_wei.' wei</div></div></div></a>';
    echo '<a href="http://etherscan.io/address/'.$addrr[0].'" target="_blanklank"><div class="button-fill grey" style="width:94%"><div class="button-text"><font size="2">'.$addrr[0].'</b></font></div><div class="button-inside"><div class="inside-text"><font size="1">http://etherscan.io/address/'.$addrr[0].'</font></div></div></div></a>';
    echo '<a href="#"><div class="button-fill grey" style="width:94%"><div class="button-text">'.$sharesCountTotal.'</b></div><div class="button-inside"><div class="inside-text"><font size="2">Shares submited since last block: '.$sharesCountTotal.'</font></div></div></div></a>';
    echo '<a href="#"><div class="button-fill grey" style="width:94%"><div class="button-text">'.$minedblocks.'</b></div><div class="button-inside"><div class="inside-text"><font size="2">Mined blocks and uncles: '.$minedblocks.'</font></div></div></div></a>';
    echo '<a href="#"><div class="button-fill grey" style="width:46%"><div class="button-text">'.$minerCount.'</b></div><div class="button-inside"><div class="inside-text"><font size="1.5">Active Miners: '.$minerCount.'</font></div></div></div></a>';
    echo '<a href="#"><div class="button-fill grey" style="width:46%"><div class="button-text">'.intval($workers_count/$avg_stats).'</b></div><div class="button-inside"><div class="inside-text"><font size="1.5">Active Workers: '.intval($workers_count/$avg_stats).'</font></div></div></div></a>';
    
    echo '</center>';

  echo '<b><br>&nbsp;&nbsp;&nbsp;&nbsp;Active miners:</b>';
  echo $activeminers;
  echo '<br><br><b>Mined Blocks / Uncles (last 50):</b><br>';
  $existQuery = "SELECT blockid FROM blocks ORDER BY id DESC LIMIT 50;";
  $existResultMinersss = mysqli_query($mysqli,$existQuery)or die("Database Error");
  while ($row=mysqli_fetch_row($existResultMinersss)){
      echo '<a href="http://etherscan.io/block/'.$row[0].'" target="_blank">'.$row[0].'</a>, ';
      $xd++;
      if ($xd == 9) {
        echo '<br>';
        $xd = 0;
      }
  }

  echo '        <br><br>
            <!--//row-->
                    </div>
                </div><!--//row-->
                <div id="form-messages"></div>
            </form><!--//contact-form-->
        </div><!--//container-->
    </section><!--//contact-section-->
    
            
   <!-- ******FOOTER****** --> 
    <footer class="footer">
        <div class="footer-content">
            <div class="container">
                <div class="row">
                    <div class="footer-col col-md-3 col-sm-4 links-col">
                        <div class="footer-col-inner">
                            <h3 class="sub-title">Quick Links</h3>
                            <ul class="list-unstyled">
                                <li><a href="..">Home</a></li>
                                <li><a href="../stats">Pool statistics</a></li>
                                <li><a href="../charts">Charts</a></li>
                                <li><a href="../stats/miner/">Miner statistics</a></li>
                                <li><a href="../how">How to start mine?</a></li>                        
                                <li><a href="mailto:laurent@utocat.com">Support</a></li>
                            </ul>
                        </div><!--//footer-col-inner-->
                    </div><!--//foooter-col-->
                     <div class="footer-col col-md-6 col-sm-8 blog-col">
                                <br>
                            </div><!--//foooter-col--> 
                    <div class="footer-col col-md-3 col-sm-12 contact-col">
                        <div class="footer-col-inner">
                            <h3 class="sub-title"></h3>
                            <p class="intro"></p>
                            <div class="row">
                                <p class="adr clearfix col-md-12 col-sm-4">
                                    <span class="adr-group">
                                    </span>
                                </p>
                            </div> 
                        </div><!--//footer-col-inner-->            
                    </div><!--//foooter-col-->   
                </div>   
            </div>        
        </div><!--//footer-content-->
    
 
    <!-- Main Javascript -->          
    <script  type="text/javascript" src="../assets/plugins/jquery-1.11.2.min.js"></script>
    <script  type="text/javascript" src="../assets/plugins/jquery-migrate-1.2.1.min.js"></script>
    <script  type="text/javascript" src="../assets/plugins/bootstrap/js/bootstrap.min.js"></script> 
    <script  type="text/javascript" src="../assets/plugins/bootstrap-hover-dropdown.min.js"></script>       
    <script  type="text/javascript" src="../assets/plugins/back-to-top.js"></script>             
    <script  type="text/javascript" src="../assets/plugins/jquery-placeholder/jquery.placeholder.js"></script>                                                                  
    <script  type="text/javascript" src="../assets/plugins/jquery-match-height/jquery.matchHeight-min.js"></script>     
    <script  type="text/javascript" src="../assets/plugins/FitVids/jquery.fitvids.js"></script>
    <script  type="text/javascript" src="../assets/js/main.js"></script>     
    
    <!-- Form Validation -->
    <script  type="text/javascript" src="../assets/plugins/jquery.validate.min.js"></script> 
    <script  type="text/javascript" src="../assets/js/form-validation-custom.js"></script> 
    
    <!-- Form iOS fix -->
    <script  type="text/javascript" src="../assets/plugins/isMobile/isMobile.min.js"></script>
    <script  type="text/javascript" src="../assets/js/form-mobile-fix.js"></script>  
    <script>
        $(".button-fill").hover(function () {
        $(this).children(".button-inside").addClass("full");
        }, function() {
        $(this).children(".button-inside").removeClass("full");
        });
    </script>

</body>
</html>';


?>
