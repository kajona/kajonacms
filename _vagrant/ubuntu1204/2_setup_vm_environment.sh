#!/usr/bin/env bash
# vagrant startup config script
# STB 04/2014
###############################


## Fancy! Do some wild things to enable error logging and adjust upload size in php!
sed 's/memory_limit = 128M/memory_limit = 256M/g' /etc/php5/apache2/php.ini > /etc/php5/apache2/php.ini_
cp /etc/php5/apache2/php.ini_ /etc/php5/apache2/php.ini

sed 's/error_reporting = E_ALL & ~E_DEPRECATED/error_reporting = E_ALL | E_STRICT/g' /etc/php5/apache2/php.ini > /etc/php5/apache2/php.ini_
cp /etc/php5/apache2/php.ini_ /etc/php5/apache2/php.ini

sed 's/display_errors = Off/display_errors = On/g' /etc/php5/apache2/php.ini > /etc/php5/apache2/php.ini_
cp /etc/php5/apache2/php.ini_ /etc/php5/apache2/php.ini

sed 's/display_startup_errors = Off/display_startup_errors = On/g' /etc/php5/apache2/php.ini > /etc/php5/apache2/php.ini_
cp /etc/php5/apache2/php.ini_ /etc/php5/apache2/php.ini

sed 's/upload_max_filesize = 2M/upload_max_filesize = 32M/g' /etc/php5/apache2/php.ini > /etc/php5/apache2/php.ini_
cp /etc/php5/apache2/php.ini_ /etc/php5/apache2/php.ini

echo "; STB-CONTROL: php.ini was modified " >> /etc/php5/apache2/php.ini


## make Ubuntu's bash handy
echo "\"\e[5~\"": beginning-of-history >>/etc/inputrc
echo "\"\e[6~\"": end-of-history >>/etc/inputrc

echo "alias la='ls -la'" >>/etc/bash.bashrc
echo "alias ..='cd ..'" >>/etc/bash.bashrc
echo "alias la='ls -la'" >>/root/.bashrc
echo "alias la='ls -la'" >>/home/vagrant/.bashrc


echo
echo
echo "Now you can 'vagrant ssh' into the new machine!"
echo "For Kajona: Please remember to change your database settings in project/system/config.php !!"
echo
echo


