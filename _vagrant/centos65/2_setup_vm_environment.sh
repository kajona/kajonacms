#!/usr/bin/env bash
# vagrant startup config script
# STB 04/2014
###############################
# CentOS #
#
## Set variables
PHPINI=/etc/php.ini
PHPINI2=/etc/php.ini_
HTTPCONF=/etc/httpd/conf/httpd.conf
HTTPCONF2=/etc/httpd/conf/httpd.conf_
###############################


## Fancy! Do some wild things to enable error logging and adjust upload size in php!
sed 's/memory_limit = 128M/memory_limit = 256M/g' $PHPINI > $PHPINI2
cp $PHPINI2 $PHPINI

sed 's/error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT/error_reporting = E_ALL | E_STRICT/g' $PHPINI > $PHPINI2
cp $PHPINI2 $PHPINI

sed 's/display_errors = Off/display_errors = On/g' $PHPINI > $PHPINI2
cp $PHPINI2 $PHPINI

sed 's/display_startup_errors = Off/display_startup_errors = On/g' $PHPINI > $PHPINI2
cp $PHPINI2 $PHPINI

sed 's/upload_max_filesize = 2M/upload_max_filesize = 32M/g' $PHPINI > $PHPINI2
cp $PHPINI2 $PHPINI


#xdebug stuff
echo "zend_extension=/usr/lib64/php/modules/xdebug.so " >> $PHPINI
echo "xdebug.remote_enable=On " >> $PHPINI
echo "xdebug.remote_connect_back=1 " >> $PHPINI
echo "xdebug.remote_host=localhost " >> $PHPINI
echo "xdebug.remote_port=9000 " >> $PHPINI
echo "xdebug.remote_handler=dbgp " >> $PHPINI
echo "xdebug.overload_var_dump = 0 " >> $PHPINI

echo "; STB-CONTROL: php.ini was modified " >> $PHPINI


## Fancy again! Enable mod_rewrite by setting AllowOverride to ALL
sed 's/AllowOverride None/AllowOverride All/g'  $HTTPCONF >$HTTPCONF2
cp $HTTPCONF2 $HTTPCONF

service httpd restart

## make Fedora's bash handy
echo "alias vi='vim'" >>/etc/bashrc
echo "alias la='ls -la'" >>/etc/bashrc
echo "alias ..='cd ..'" >>/etc/bashrc

echo "PS1=\"\[\033[0;36m\]\u@\h:\w$\[\033[0m\] \"" >>/etc/bashrc


echo
echo
echo "!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!"
echo "Now you can 'vagrant ssh' into the new machine!"
echo "For Kajona: Please remember to change your database settings in project/system/config.php !!"
echo
echo "SSL/https is activated, available via IP-Address of the machine or via port forwarding on port 8443."
echo "Use https://localhost:8443"
echo "!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!"
echo
echo


