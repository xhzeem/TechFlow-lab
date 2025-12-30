FROM eclipse-temurin:8-jdk

# Install Go, Tomcat dependencies, and other vulnerable services
RUN echo 'Acquire::Retries "3";' > /etc/apt/apt.conf.d/80-retries && \
    apt-get update && \
    DEBIAN_FRONTEND=noninteractive apt-get install -y --fix-missing \
    wget \
    curl \
    # openjdk-11-jdk provided by base \
    golang-go \
    proftpd-basic \
    default-mysql-server \
    netcat-traditional \
    ncat \
    && rm -rf /var/lib/apt/lists/*

# Install Tomcat 8.5.50 (vulnerable to CVE-2020-1938 Ghostcat)
RUN wget https://archive.apache.org/dist/tomcat/tomcat-8/v8.5.50/bin/apache-tomcat-8.5.50.tar.gz && \
    tar -xzf apache-tomcat-8.5.50.tar.gz -C /opt && \
    mv /opt/apache-tomcat-8.5.50 /opt/tomcat && \
    rm apache-tomcat-8.5.50.tar.gz

# Configure Tomcat with weak credentials
COPY tomcat-users.xml /opt/tomcat/conf/tomcat-users.xml

# Enable AJP connector (Ghostcat vulnerability)
RUN sed -i 's/<!-- <Connector port="8009" protocol="AJP\/1.3" redirectPort="8443" \/> -->/<Connector port="8009" protocol="AJP\/1.3" redirectPort="8443" secretRequired="false" \/>/' /opt/tomcat/conf/server.xml

# Configure ProFTPD
RUN echo "ServerName \"FTP Server\"" > /etc/proftpd/proftpd.conf && \
    echo "ServerType standalone" >> /etc/proftpd/proftpd.conf && \
    echo "DefaultServer on" >> /etc/proftpd/proftpd.conf && \
    echo "Port 21" >> /etc/proftpd/proftpd.conf && \
    echo "Umask 022" >> /etc/proftpd/proftpd.conf && \
    echo "MaxInstances 30" >> /etc/proftpd/proftpd.conf && \
    echo "User nobody" >> /etc/proftpd/proftpd.conf && \
    echo "Group nogroup" >> /etc/proftpd/proftpd.conf && \
    echo "<Anonymous /srv/ftp>" >> /etc/proftpd/proftpd.conf && \
    echo "  User ftp" >> /etc/proftpd/proftpd.conf && \
    echo "  Group nogroup" >> /etc/proftpd/proftpd.conf && \
    echo "  UserAlias anonymous ftp" >> /etc/proftpd/proftpd.conf && \
    echo "  <Limit WRITE>" >> /etc/proftpd/proftpd.conf && \
    echo "    DenyAll" >> /etc/proftpd/proftpd.conf && \
    echo "  </Limit>" >> /etc/proftpd/proftpd.conf && \
    echo "</Anonymous>" >> /etc/proftpd/proftpd.conf

# Create FTP directory
RUN mkdir -p /srv/ftp && \
    echo "FLAG{proftpd_vulnerable_version}" > /srv/ftp/flag.txt

# Configure MySQL
RUN mkdir -p /var/run/mysqld && \
    chown mysql:mysql /var/run/mysqld

# Install Elasticsearch 1.4.2 (vulnerable to CVE-2015-1427)
RUN mkdir -p /usr/share/elasticsearch && \
    wget https://download.elastic.co/elasticsearch/elasticsearch/elasticsearch-1.4.2.tar.gz && \
    tar -xzf elasticsearch-1.4.2.tar.gz -C /usr/share/elasticsearch --strip-components=1 && \
    rm elasticsearch-1.4.2.tar.gz

# Create Go application directory
RUN mkdir -p /var/www/go

# Copy Go application
COPY www/main.go /var/www/go/main.go

# Build Go application
WORKDIR /var/www/go
RUN go build -o app main.go

# Add flag files
RUN echo "FLAG{tomcat_ghostcat_cve}" > /opt/tomcat/webapps/ROOT/flag.txt && \
    echo "FLAG{mysql_weak_credentials}" > /root/mysql_flag.txt && \
    echo "FLAG{elasticsearch_rce}" > /root/es_flag.txt

# Expose ports
EXPOSE 80 8080 8009 21 3306 9200

# Start script
COPY start.sh /start.sh
RUN chmod +x /start.sh

WORKDIR /

CMD ["/start.sh"]
