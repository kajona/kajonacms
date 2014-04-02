#!/usr/bin/env bash


apt-get update
apt-get install -y apache2

apt-get install -y w3m libapache2-mod-gnutls php5 zip

echo
echo "Please remember to change your database settings in project/system/config.php !!"



