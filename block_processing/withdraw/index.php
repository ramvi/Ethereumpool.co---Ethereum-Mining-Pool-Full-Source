<?php
error_reporting(error_reporting() & ~E_NOTICE);
$config = include('../../config.php');

include($config['BigInteger']);
$ether_wei = 1000000000000000000;

$m = new Memcached();
$m->addServer('localhost', 11211);
$m->set('state_work',1);

////////////////////////////////////////LOGS////////////////////////////////////
$file = 'withdraw_log_';
$file = '/usr/share/nginx/html/pool/block_processing/'.$file.'='.date('Y M d').'.txt';
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
$existQuery = "SELECT address,balance FROM miners WHERE balance!='0'";
$existResult = mysqli_query($mysqli,$existQuery)or die("Database Error");
while ($row=mysqli_fetch_row($existResult)){
	$payer_adr = $row[0];
	echo "\n-------------------------------------------";
	echo "\n".$payer_adr;
	echo "\nBalance: ".sprintf('%f',$row[1])." wei";
	echo "\nBalance: ".sprintf('%f',$row[1]/$ether_wei)." ether";
	$current .= "\n-------------------------------------------";
	$current .= "\n".$payer_adr;
	$current .= "\nBalance: ".sprintf('%f',$row[1])." wei";
	$current .= "\nBalance: ".sprintf('%f',$row[1]/$ether_wei)." ether";

	if ($row[1]/$ether_wei >= 0.5) {
		$escapeDot = explode('.', sprintf('%f', $row[1]));
		$balancetopay = new Math_BigInteger($escapeDot[0]);
		$free2pay = new Math_BigInteger($gasprice);
		$resultPayment = $balancetopay->subtract($free2pay);

		
		$validBigHex = bcdechex($resultPayment->toString());

		echo "HexReverse:\n\n".sprintf('%f',hexdec($validBigHex));
		$current .= "HexReverse:\n\n".sprintf('%f',hexdec($validBigHex));

		$sendValue = '0x'.$validBigHex;

		$transactionState = 0;
  		$transaction = array("from" => $coinbase, "to" => $payer_adr, "value" => $sendValue);   
		$data = array("jsonrpc" => "2.0", "method" => "eth_sendTransaction", "params" => [$transaction], "id" => 1);                                                                    
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
		$txid = $block_info_last['result'];

		echo "\n\n".$data_string;
		echo "\n\n".$result3;
		$current .= "\n\n".$data_string;
		$current .= "\n\n".$result3;

		///////////////////////////////////////////////////


		$timestamp = time();
		$tas22k = 'INSERT INTO payout_history (address, balance, time, txid, fee) VALUES ("'.$row[0].'", "'.$row[1].'", "'.$timestamp.'", "'.$txid.'", "'.$gasprice.'")';
		$query = mysqli_query($mysqli,$tas22k) or die("Database Error");
		$weisummary = $weisummary + $row[1];

		$task = "UPDATE miners SET balance='0' WHERE address='$payer_adr';";	
		$query = mysqli_query($mysqli,$task) or die("Database Error");	
		echo "\nWithdraw OK\n";
		$current .= 

		usleep(100000);
		$withdrawcount++;
	} else {
		echo "\nNot exceeded 1Ether\n";
		$current .= "\nNot exceeded 1Ether\n";
	}
}
	echo "\n\n".sprintf('%f',$weisummary).' wei';
	$current .= "\n\n".sprintf('%f',$weisummary).' wei';
	echo "\n\n".sprintf('%f',$withdrawcount).' withdraw Count';
	$current .= "\n\n".sprintf('%f',$withdrawcount).' withdraw Count';


	
    $data = array("jsonrpc" => "2.0", "method" => "eth_getBalance", "params" => [$coinbase,'latest'], "id" => 1);                                                                    
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

	$updatebalanceaddr = $balanceaddr->toString();
	$task = "UPDATE info SET balance='$updatebalanceaddr' WHERE id=1;";	
	$query = mysqli_query($mysqli,$task) or die("Database Error");	


file_put_contents($file, $current);
$m->set('state_work',0);


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
