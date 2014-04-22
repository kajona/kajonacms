#!/usr/bin/env bash
# vagrant startup config script
# STB 04/2014
###############################
# Fedora #
#

######  DO NOT TOUCH THE FIRST BLOCK !!!! ##########

## yum update and install apache2
yum install -y httpd php mysql-server mod_ssl phpmyadmin php-pecl-xdebug

## prevent annoying warning during apache (re-)start
echo "ServerName KajonaVagrantVM" >> /etc/httpd/conf/httpd.conf

## get some handy tools
yum install -y w3m vim-enhanced


## Start services
service httpd start
#service mysqld start

#####################################################


################# Additional settings ###############
## Make your personal settings here:


## further software?
# yum install -y <packagename>

