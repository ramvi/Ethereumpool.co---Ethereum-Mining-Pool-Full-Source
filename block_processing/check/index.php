<?php
error_reporting(error_reporting() & ~E_NOTICE);
include('/usr/share/nginx/html/pool/BigInteger.php');
$config = include('../../config.php');
$ether_wei = 1000000000000000000;

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
	$coinbase = $addrr[0];


	$data = array("jsonrpc" => "2.0", "method" => "eth_gasPrice", "id" => 73);                                                                    
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
	$gasprice = hexdec($block_info_last['result']);

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

	echo "\nWallet:".$coinbase;
	echo "\nGas:".$gasprice;
	echo "\n\n";
	
$mysqli=mysqli_connect($config['host'], $config['username'], $config['password'], $config['bdd']) or die("Database Error");
$existQuery = "SELECT address,balance FROM miners WHERE balance!='0'";
$existResult = mysqli_query($mysqli,$existQuery)or die("Database Error");
$total = new Math_BigInteger(0);
while ($row=mysqli_fetch_row($existResult)){
	$payer_adr = $row[0];
	$balanceforthisaddr = new Math_BigInteger($row[1]);
	$ether = floatval($balanceforthisaddr->toString())/$ether_wei;
	echo "\n".$payer_adr;
	echo "\nBalance: ".$balanceforthisaddr.' wei';
	echo "\nBalance: ".$ether.' Ether';
	echo "\n-------------------------------------------";
	$total = $total->add($balanceforthisaddr);
}
echo "\n\nBalance1:".$balanceaddr->toString();
echo "\nBalance2:".$total->toString()."\n\n";
$ether1 = floatval($balanceaddr->toString())/$ether_wei;
$ether2 = floatval($total->toString())/$ether_wei;
echo "\n\nBalance1:".$ether1;
echo "\nBalance2:".$ether2;

$block_coins_size = $balanceaddr->subtract($total);
$ethedssr1 = floatval($block_coins_size->toString())/$ether_wei;
echo "\n\nBalance1:".$block_coins_size->toString();
echo "\nBalance2:".$ethedssr1;


if ($balanceaddr > $total) {
	echo "\nCorrect - balance valid\n\n";
} else {
	echo "\n!!! Incorrect - balance invalid !!!\n\n";
}


?>