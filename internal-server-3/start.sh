#!/bin/bash

# Initialize MySQL
mkdir -p /var/run/mysqld
chown mysql:mysql /var/run/mysqld
mysqld --initialize-insecure --user=mysql 2>/dev/null || true

# Start MySQL
mysqld --user=mysql --bind-address=0.0.0.0 &
sleep 5

# Set weak MySQL root password
mysql -u root -e "ALTER USER 'root'@'localhost' IDENTIFIED BY 'toor';" 2>/dev/null || true
mysql -u root -ptoor -e "CREATE DATABASE IF NOT EXISTS internal_db;" 2>/dev/null || true
mysql -u root -ptoor -e "FLUSH PRIVILEGES;" 2>/dev/null || true

# Start ProFTPD
service proftpd start

# Start Elasticsearch
/usr/share/elasticsearch/bin/elasticsearch -d -Des.network.host=0.0.0.0

# Start Go application on port 80
cd /var/www/go
./app &

# Start Tomcat
/opt/tomcat/bin/catalina.sh run
