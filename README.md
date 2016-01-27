# Winze.io
>  Open source Ethereum Mining Pool

This is the full source code of the Ethereum Mining Pool Winze.io. This project builds on the code written by [karek314](https://github.com/karek314). The code is in PHP, except for one script which is in Python. The project is built to be efficient.

## Installation
* [Nginx](https://www.digitalocean.com/community/tutorials/how-to-install-and-secure-phpmyadmin-with-nginx-on-an-ubuntu-14-04-server) (Apache may be also fine, but is untested)
* [HHVM](https://www.digitalocean.com/community/tutorials/how-to-install-hhvm-with-nginx-on-ubuntu-14-04) (php5-fpm also possible)
* [MariaDB](https://www.liquidweb.com/kb/how-to-install-mariadb-5-5-on-ubuntu-14-04-lts/)
* php5-memcached
* <a href="https://github.com/ethereum/pyethereum" target="_blank">pyethereum</a>
* <a href="https://github.com/ethereum/go-ethereum/releases" target="_blank">Geth</a><br>

## Setup on Linux
1. Install all software mentioned above.<br>
1. Import database scheme <pre>misc>database_scheme.sql</pre>
1. Copy this directory to `/opt/pool/`
1. Link the website into Nginx http root: `ln -s /opt/pool/mainpage/ /usr/share/nginx/html/`
1. Setup Nginx site in `/etc/nginx/sites-enabled/*`:
<pre>'mainpage' directory as public and if you need block /logs directory 'block_processing' locally </pre>
1. Move files <pre>nonce_fast.py and nonce.py</pre> from 'misc' directory to `*/pyethereum/ethereum/` (main directory of Pyethereum)
1. Create `config.php` in the git root folder
```php
<?php
return array(
  'host' => 'localhost',
  'username' => 'root',
  'password' => '',
  'bdd' => '',
  'BigInteger' => '/usr/share/nginx/html/BigInteger.php'
);
```

### Withdraws
You can execute withdraws manually or add it as cron job
<pre>sudo php /usr/share/nginx/html/pool/block_processing/withdraw/index.php</pre>

`crontab -e`
<pre>* */12 * * * sudo php /usr/share/nginx/html/pool/block_processing/withdraw/index.php</pre>

This both scripts can be used to check if withdraws has been processed correctly or check if splited balance == real balance, it was mainly used while development process but it might be helpful.
<pre>
sudo php /home/www4/block_processing/withdraw_check/index.php
curl http://127.0.0.1:9846/check/</pre>

### Create Ethereum account
Create an Ethereum account, if you do not have one already.
`geth account new`

## Start Pool

### Geth
* Create a screen with: `screen -S geth`
* Start the Ethereum daemon: `geth --rpcaddr 127.0.0.1 --rpcport 8983 --rpc --unlock 0`
* Detach from the screen with: <kbd>Ctrl+a</kbd> <kbd>d</kbd>

###  Get Work from GETH Json RPC and cache it with memcached (reduces queries to geth rpc)
* Create a screen with: `screen -S process_work`
* `cd /opt/pool` 
* `sudo php block_processing/process_work/index.php`
* Detach from the screen with: <kbd>Ctrl+a</kbd> <kbd>d</kbd>

### Block Processing - this script handle block splitting and Proof of Work verification
* Create a screen with: `screen -S block_processing`
* `cd /opt/pool` 
* `sudo php block_processing/index.php`
* Detach from the screen with: <kbd>Ctrl+a</kbd> <kbd>d</kbd>

### This script updates data to calculate predicted mining rewards
* Create a screen with: `screen -S update_calculator`
* `cd /opt/pool` 
* `sudo php block_processing/update_calculator/index.php`
* Detach from the screen with: <kbd>Ctrl+a</kbd> <kbd>d</kbd>

### Used to process internal statistics and save to database
* Create a screen with: `screen -S stats`
* `cd /opt/pool` 
* `sudo php block_processing/stats/index.php`
* Detach from the screen with: <kbd>Ctrl+a</kbd> <kbd>d</kbd>

## Notes
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
