<?php
error_reporting(error_reporting() & ~E_NOTICE);
$config = include('../../config.php');

include($config['BigInteger']);


$mysqli=mysqli_connect($config['host'], $config['username'], $config['password'], $config['bdd']) or die("Database Error");
$xx = 0;
echo "\nStart";
while (1) {
$start_time = time();
$xx++;
$count_active = 0;
$miner_id = array();
$miner_hash = array();
$miner_stamp = array();
$miner_payouts_checker = array();

////POOL
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
        $user_userid = $row[0].$row[3];
        $miner_adr = $row[3];
        if (!isset($miner_payouts["'$miner_adr'"])) {
            $miner_payouts["'$miner_adr'"] = $row[1];
            $miner_payouts_checker["'$user_userid'"] = $user_userid;
            $hashrate_real = $hashrate_real + $row[1];
            $hashrate_est = $hashrate_est + $row[0];
            $workers_count++;
        } else {
            if (!isset($miner_payouts_checker["'$user_userid'"])) {
              $miner_hashrate_fix = $miner_payouts["'$miner_adr'"] + $row[1];
              $miner_payouts["'$miner_adr'"] = $miner_hashrate_fix;
              $miner_payouts_checker["'$user_userid'"] = $user_userid;
              $hashrate_real = $hashrate_real + $row[1];
              $hashrate_est = $hashrate_est + $row[0];
              $workers_count++;
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
  $count_active++;
  $add2Stats = "INSERT INTO miner_hashrate (miner, hashrate, val_timestamp) VALUES ('$key', '$value', '$cur_time')";
  $querydone = mysqli_query($mysqli,$add2Stats) or die("Database Error 3");
}

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
$result3 = curl_exec($ch1); // < GET Balance from independent node since locally forks may happen and return invalid (higher) response


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

  
  $ether_wei = 1000000000000000000;
  $ether = floatval($balanceaddr->toString())/$ether_wei;



$db_users_count = 0;
$users_data = '';
$existQuery = "SELECT address,balance FROM miners";
$existResult = mysqli_query($mysqli,$existQuery)or die("Database Error");
while ($row=mysqli_fetch_row($existResult)){
  $val1 = $row[0];
  $val2 = $row[1];
  $newnew_balance = new Math_BigInteger($val2);
  $ether_user = floatval($newnew_balance->toString())/$ether_wei;
  $users_data = $users_data.' '."('$val1', '$ether_user', '$cur_time'),";
  $db_users_count++;
}
$users_data = substr($users_data, 0, -1);
$add2Stats = "INSERT INTO miner_balance (miner, value, var_timestamp) VALUES".$users_data;
$querydone = mysqli_query($mysqli,$add2Stats) or die('erros miner_balance');



$add2Stats = "INSERT INTO pool_hashrate (hashrate, val_timestamp) VALUES ('$hashrate_real', '$cur_time')";
$querydone = mysqli_query($mysqli,$add2Stats) or die("Database Error 0");

$add2Stats = "INSERT INTO pool_balance (value, var_timestamp) VALUES ('$ether', '$cur_time')";
$querydone = mysqli_query($mysqli,$add2Stats) or die("Database Error 0");

$add2Stats = "INSERT INTO pool_miners (value, var_timestamp) VALUES ('$count_active', '$cur_time')";
$querydone = mysqli_query($mysqli,$add2Stats) or die("Database Error 0");

$add2Stats = "INSERT INTO pool_workers (value, var_timestamp) VALUES ('$workers_count', '$cur_time')";
$querydone = mysqli_query($mysqli,$add2Stats) or die("Database Error 0");

$end_time = time();

$took = $end_time - $start_time;
$time_sleep = 60-$took;
if ($time_sleep < 1) {
  $time_sleep = 1;
}

echo "\nAdding...".$xx.' took:'.$took.' sleep:'.$time_sleep.' Active miners > '.$count_active.' workers > '.$workers_count.' hashrate -> '.$hashrate_real.'  balance -> '.$ether.' users detected: '.$db_users_count;
sleep($time_sleep);
}
?>