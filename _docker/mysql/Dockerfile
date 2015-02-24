FROM ubuntu:14.04
MAINTAINER Tim Kiefer <tim.kiefer@kojikui.de>

#ENV http_proxy http://yourproxy.com:8080
#ENV https_proxy http://yourproxy.com:8080

#RUN apt-get update
RUN apt-get -y upgrade
# seed database password
COPY mysqlpwdseed /root/mysqlpwdseed
RUN debconf-set-selections /root/mysqlpwdseed

RUN apt-get update && apt-get -y install mysql-server

RUN sed -i -e"s/^bind-address\s*=\s*127.0.0.1/bind-address = 0.0.0.0/" /etc/mysql/my.cnf

RUN /usr/sbin/mysqld & \
    sleep 10s && \
    echo "GRANT ALL ON *.* TO admin@'%' IDENTIFIED BY 'kajona' WITH GRANT OPTION; FLUSH PRIVILEGES" | mysql -u root --password=kajona && \
    echo "create database kajona" | mysql -u root --password=kajona && \
    echo "CREATE USER kajona@'%' IDENTIFIED BY 'kajona'" | mysql -u root --password=kajona && \
    echo "GRANT ALL ON *.* TO kajona@'%' IDENTIFIED BY 'kajona' WITH GRANT OPTION; FLUSH PRIVILEGES" | mysql -u root --password=kajona

EXPOSE 3306

CMD ["/usr/bin/mysqld_safe"]

#docker build -t "local/mysql:v1" .
#docker run -p 3306:3306 -d --name mysql local/mysql:v1