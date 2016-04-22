FROM ubuntu:14.04
MAINTAINER Tim Kiefer<tim.kiefer@kojikui.de>

#ENV http_proxy http://yourproxy.com:8080
#ENV https_proxy http://yourproxy.com:8080

## apt update and install apache2
RUN apt-get update && \
    apt-get install -y apache2 && \

## prevent annoying warning during apache (re-)start
    echo "ServerName KajonaDocker" >> /etc/apache2/httpd.conf && \

## get some more software
    apt-get install -y libapache2-mod-gnutls php5 php5-dev php5-mysql php5-sqlite php5-gd && \

## dependency chain for an up-to-date xdebug. xdebug > 2.1 is required to have remote_connect_back
    apt-get install -y build-essential php-pear && \
    pecl install xdebug && \

## get some handy tools
    apt-get install -y w3m zip vim && \

## enable mod_rewrite
    a2enmod rewrite

## enable mod_ssl
#a2ensite default-ssl
#a2enmod ssl



## Set variables
ENV PHPINI /etc/php5/apache2/php.ini
ENV PHPINI2 /etc/php5/apache2/php.ini_
ENV HTTPCONF /etc/apache2/httpd.conf
ENV HTTPCONF2 /etc/apache2/httpd.conf_
###############################


## Fancy! Do some wild things to enable error logging and adjust upload size in php!
RUN sed 's/memory_limit = 128M/memory_limit = 256M/g' $PHPINI > $PHPINI2 && cp $PHPINI2 $PHPINI && \

    sed 's/error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT/error_reporting = E_ALL | E_STRICT/g' $PHPINI > $PHPINI2 && \
    cp $PHPINI2 $PHPINI && \

    sed 's/display_errors = Off/display_errors = On/g' $PHPINI > $PHPINI2 && \
    cp $PHPINI2 $PHPINI && \

    sed 's/display_startup_errors = Off/display_startup_errors = On/g' $PHPINI > $PHPINI2 && \
    cp $PHPINI2 $PHPINI && \

    sed 's/upload_max_filesize = 2M/upload_max_filesize = 32M/g' $PHPINI > $PHPINI2 && \
    cp $PHPINI2 $PHPINI && \

    echo "zend_extension=/usr/lib/php5/20121212/xdebug.so " >> $PHPINI && \
    echo "xdebug.remote_enable=On " >> $PHPINI && \
    echo "xdebug.remote_connect_back=1 " >> $PHPINI && \
    echo "xdebug.remote_host=localhost " >> $PHPINI && \
    echo "xdebug.remote_port=9000 " >> $PHPINI && \
    echo "xdebug.remote_handler=dbgp " >> $PHPINI && \
    echo "xdebug.overload_var_dump = 0 " >> $PHPINI && \

    echo "; STB-CONTROL: php.ini was modified " >> $PHPINI && \

## Fancy again! Enable mod_rewrite by setting AllowOverride to ALL
    sed 's/AllowOverride None/AllowOverride All/g'  $HTTPCONF >$HTTPCONF2 && \
    cp $HTTPCONF2 $HTTPCONF

RUN php -r "readfile('https://getcomposer.org/installer');" > composer-setup.php
RUN php -r "if (hash_file('SHA384', 'composer-setup.php') === '7228c001f88bee97506740ef0888240bd8a760b046ee16db8f4095c0d8d525f2367663f22a46b48d072c816e7fe19959') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
RUN php composer-setup.php
RUN php -r "unlink('composer-setup.php');"


#COPY . /var/www/html

EXPOSE 80
EXPOSE 9000

# By default, simply start apache.
CMD /usr/sbin/apache2ctl -D FOREGROUND

#docker build -t "kajona/web:0.1" .
#docker run -p 127.0.0.1:80:80 -p 9000:9000 -d -v .:/var/www/html --link mysql:mysql --name web kajona/web:0.1