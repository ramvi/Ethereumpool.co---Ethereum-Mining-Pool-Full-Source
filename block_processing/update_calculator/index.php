<?php
error_reporting(error_reporting() & ~E_NOTICE);
$m = new Memcached();
include('/usr/share/nginx/html/pool/BigInteger.php');
$m->addServer('localhost', 11211);

while (1) {
$ch1 = curl_init('http://www.worldcoinindex.com/apiservice/json?key=ooW49oJ669QxELeGMRpVVVK9G');
curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);                                                                      
curl_setopt($ch1, CURLOPT_HTTPHEADER, array(                                                                          
    'Content-Type: application/json',                                                                                
    'Content-Length: ' . strlen($data_string))                                                                       
    );
$result1 = curl_exec($ch1);  
$arrayjson = json_decode($result1, true); 
$array = $arrayjson['Markets'];
foreach ($array as $key => $value) {
	if ('Ethereum' == $value['Name']) {
		$result1 = $value['Price_usd'];
	}
}
echo "\nPrice:".$result1;
$firstKey = 'eth_price_current';
$m->set($firstKey,$result1);

$ch1 = curl_init('https://etherchain.org/api/basic_stats');                                                                                                                                                                                                          
curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);                                                                      
curl_setopt($ch1, CURLOPT_HTTPHEADER, array(                                                                          
    'Content-Type: application/json',                                                                                
    'Content-Length: ' . strlen($data_string))                                                                       
    );  
$result1 = curl_exec($ch1); 
echo "\n2:".substr($result1,0,256); 
$firstKey = 'basic_stats';
$m->set($firstKey,$result1);


$xx++;
echo "\nUpdating...".$xx;
sleep(300);
}
?>
