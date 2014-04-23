#!/usr/bin/env bash
# vagrant startup config script
# STB 04/2014
###############################
# Ubuntu #
#

######  DO NOT TOUCH THE FIRST BLOCK !!!! ##########

## apt update and install apache2
apt-get update
apt-get install -y apache2

## prevent annoying warning during apache (re-)start
echo "ServerName KajonaVagrantVM" >> /etc/apache2/httpd.conf

## get some more software
apt-get install -y libapache2-mod-gnutls php5 php5-mysql php5-sqlite php5-gd

## dependency chain for an up-to-date xdebug. xdebug > 2.1 is required to have remote_connect_back
apt-get install -y build-essential php-pear
pecl install xdebug

## get some handy tools
apt-get install -y w3m zip vim

## enable mod_rewrite
a2enmod rewrite

## enable mod_ssl
a2ensite default-ssl
a2enmod ssl


## IMPORTANT! Restart apache! Otherwise lib-gd is NOT available!
/etc/init.d/apache2 restart
#####################################################


################# Additional settings ###############
## Make your personal settings here:

## prefer newer PHP5 version? Uncomment the following lines!
#cp /etc/php5/apache2/php.ini /etc/php5/apache2/php.ini.bck
#apt-get install -y python-software-properties; add-apt-repository -y ppa:ondrej/php5-oldstable
#apt-get update; sudo apt-get install -y php5
#cp /etc/php5/apache2/php.ini.bck /etc/php5/apache2/php.ini


## further software?
# apt-get install -y <packagename>

