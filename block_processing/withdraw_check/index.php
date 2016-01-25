<?php
error_reporting(error_reporting() & ~E_NOTICE);
$config = include('../../config.php');

include($config['BigInteger']);

$ether_wei = 1000000000000000000;


////////////////////////////////////////LOGS////////////////////////////////////
$file = 'withdraw_checker_log_';
$file = '/opt/pool/block_processing/'.$file.'='.date('Y M d').'.txt';
if(!file_exists($file)) 
{ 
  $fh = fopen($file, 'w');
  fclose($fh); 
}

$current = file_get_contents($file);
///////////////////////////////////////////////////////////////////////////////


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
	$gasprice = 1000000000000000;


$mysqli=mysqli_connect($config['host'], $config['username'], $config['password'], $config['bdd']) or die("Database Error");
$existQuery = "SELECT time,txid,balance FROM payout_history";
$existResult = mysqli_query($mysqli,$existQuery)or die("Database Error");
while ($row=mysqli_fetch_row($existResult)){
	$txid = $row[1];
	$balance = $row[2];
	$time = $row[0];
	$data = array("jsonrpc" => "2.0", "method" => "eth_getTransactionByHash", "params" => [$txid], "id" => 1);                                                                   
	$data_string = json_encode($data);  

	$ch1 = curl_init('http://127.0.0.1:8983');                                                                      
	curl_setopt($ch1, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
	curl_setopt($ch1, CURLOPT_POSTFIELDS, $data_string);                                                                  
	curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);                                                                      
	curl_setopt($ch1, CURLOPT_HTTPHEADER, array(                                                                          
   		 'Content-Type: application/json',                                                                                
    	'Content-Length: ' . strlen($data_string))                                                                       
	);
	$czas = gmdate("Y-M-d  h:i:s", $time);
	$check = time()-$time;                                                                                                                
	if ($check < 5000) {
		echo "\n-------------------------------------------";
		echo "\n".$txid;
		echo "\n".$czas;
		echo "\nBalance: ".sprintf('%f',$balance)." wei";
		echo "\nBalance: ".sprintf('%f',$balance)." ether";
		$current .= "\n-------------------------------------------";
		$current .= "\n".$txid;
		$current .= "\n".$czas;
		$current .= "\nBalance: ".sprintf('%f',$balance)." wei";
		$current .= "\nBalance: ".sprintf('%f',$balance)." ether";
		$result3 = curl_exec($ch1);
		$checkjson = json_decode($result3, true); 
		echo "\n".$result3;
		$current .= "\n".$result3;
		$withdrawcount++;
		$jsonarray = $checkjson['result'];
		$blockNumber = $jsonarray['blockNumber'];
		echo "\n".$blockNumber;
		$current .= "\n".$blockNumber;
		if (!$blockNumber || $blockNumber == 'null') {
			echo "\n".'ERROR';
			$current .= "\n".'ERROR';
		} else {
			echo "\n".'OK';
			$current .= "\n".'OK';
		}
	}
}

echo "\n\n".sprintf('%f',$withdrawcount).' withdraw Count';
$current .= "\n\n".sprintf('%f',$withdrawcount).' withdraw Count';
file_put_contents($file, $current);



function bcdechex($dec) {
    $hex = '';
    do {    
        $last = bcmod($dec, 16);
        $hex = dechex($last).$hex;
        $dec = bcdiv(bcsub($dec, $last), 16);
    } while($dec>0);
    return $hex;
}

?>