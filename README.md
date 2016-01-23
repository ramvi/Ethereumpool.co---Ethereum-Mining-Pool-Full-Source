# Ethereumpool.co---Ethereum-Mining-Pool-Full-Source
This is full source of Ethereum Mining Pool - http://ethereumpool.co Pool has been written in php and one script in python to perform proof of work validation. Pool software is efficient, it was using only 4% cpu while handling ~250 workers. It may be quite messy but i didn't spend much time on creating this, my intention was to fastly create pool to save community from only one existing pool with 50-70% network hashrate, but later other pools pop up. So now it's even more decentralized, everybody can easily set up own pool :)

#Requirements
Recommended <a href="https://github.com/facebook/hhvmHHVM" target="_blank">HHVM</a> over php5-fpm, but php5-fpm is fine.<br>
<a href="https://mariadb.org" target="_blank">MariaDB server</a><br>
<a href="http://memcached.org" target="_blank">Memcached</a><br>
<a href="https://github.com/phpseclib/phpseclib/blob/master/phpseclib/Math/BigInteger.php" target="_blank">BigInteger for php (included in project)</a><br>
<a href="http://nginx.org" target="_blank">Nginx (Apache may be also fine, but i didn't tested)</a><br>
<a href="https://github.com/ethereum/pyethereum" target="_blank">Pyethereum</a><br>
<a href="https://github.com/ethereum/go-ethereum" target="_blank">Geth</a><br>
<a href="http://www.highcharts.com" target="_blank">Highcharts (included in project)</a><br>
 


#Setup on Linux
Install all software mentioned above.<br>
Setup your mysql server and import database scheme <pre>misc>database_scheme.sql</pre>
Now please review all source files and setup valid mysql connection details
<pre>$mysqli=mysqli_connect('Mysql_server_ip','Database_username','Database_password','Database_name') or die("Database Error");</pre>
Copy all files to server<br>
Setup nginx server blocks:
<pre>'mainpage' directory as public and if you need block /logs directory
'block_processing' locally
</pre>
Also remember to setup phpmyadmin with ssl, etc..<br>
Now move files <pre>nonce_fast.py and nonce.py</pre> from 'misc' directory to <pre>/root/pyethereum/ethereum/ (main directory of Pyethereum)</pre><br>
To maximize performance tweak configurations of memcached,mariadb,nginx,hhvm and kernel but it's not necessary to start pool.<br>

#Start Pool
<pre>screen<br>Push Enter key<br>geth --rpcaddr 127.0.0.1 --rpcport 8983 --rpc --unlock COINBASE_ADDRESS</pre>

Now start background scripts:<br>
Get Work from GETH Json RPC and cache it with memcached (reduces queries to geth rpc)
<pre>screen<br>Push Enter key<br>sudo php /usr/share/nginx/html/pool/block_processing/process_work/index.php</pre>
<br>Block Processing - this script handle block splitting and Proof of Work verification
<pre>screen<br>Push Enter key<br>sudo php /usr/share/nginx/html/pool/block_processing/index.php</pre>
<br>This script updates data to calculate predicted mining rewards
<pre>screen<br>Push Enter key<br>sudo php /usr/share/nginx/html/pool/block_processing/update_calculator/index.php</pre>
<br>Used to process internal statistics and save to database
<pre>screen<br>Push Enter key<br>sudo php /usr/share/nginx/html/pool/block_processing/stats/index.php</pre>

<br>
You can execute withdraws manually or add it as cron job
<pre>sudo php /usr/share/nginx/html/pool/block_processing/withdraw/index.php</pre>

crontab -e
<pre>* */12 * * * sudo php /usr/share/nginx/html/pool/block_processing/withdraw/index.php</pre>

This both scripts can be used to check if withdraws has been processed correctly or check if splited balance == real balance, it was mainly used while development process but it might be helpful.
<pre>
sudo php /home/www4/block_processing/withdraw_check/index.php
curl http://127.0.0.1:9846/check/</pre>

#Notes
withdraw_check and withdraw scripts saves logs in block_processing directory.<br>
If you would like to debug mining proxy (mainpage/index.php)
<pre>$logstate = true;</pre>
But don't use it on production, it's quite heavy with many workers.<br>

Make sure to setup valid permissions to allow php run python script and make sure that directory permissions are fine to save logs.
Also please review python path in php files, but '/usr/bin/python' should be fine, other paths may be also necessary to review depending on where you put files!<br>
<br>
Setting up pool revenue address and fee<br>
<pre>block_processing/index.php</pre> and make sure address exists as 'miner' in miners table.

<br><br>
You can easily access all background scripts by
<pre>
screen -ls<br>then pick one by<br>screen -x INTEGER</pre>

#Contributing
If you want to contribute, fork and pull request or open issue.


#License
Entire PHP and Python code is under The MIT License (MIT)<br>
Front-end(site theme) is used from http://themes.3rdwavemedia.com/website-templates/responsive-bootstrap-theme-web-development-agencies-devstudio/<br>
Personally i own license, so better buy license or use your own front-end.

#Donate
Bitcoin -> 1MsCcLLzaZtgEiMsigFoRJjz149mPSoFKC<br>
![alt tag](http://s16.postimg.org/xbne92mdx/image.png)<br>

Ethereum -> 0x9284e52d64d888f2aa1bb62a38f3b5259487376a
