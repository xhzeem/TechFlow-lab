FROM debian:bullseye-slim

# Install Apache, Perl, Samba, and additional vulnerable services
RUN echo 'Acquire::Retries "3";' > /etc/apt/apt.conf.d/80-retries && \
    apt-get update && \
    DEBIAN_FRONTEND=noninteractive apt-get install -y --fix-missing \
    apache2 \
    libapache2-mod-perl2 \
    libcgi-pm-perl \
    samba \
    smbclient \
    openssh-server \
    vsftpd \
    redis-server \
    ncat \
    && rm -rf /var/lib/apt/lists/*

# Configure SSH
RUN mkdir /var/run/sshd && \
    sed -i 's/#PermitRootLogin prohibit-password/PermitRootLogin yes/' /etc/ssh/sshd_config && \
    sed -i 's/#PubkeyAuthentication yes/PubkeyAuthentication yes/' /etc/ssh/sshd_config

# Enable CGI and Perl modules
RUN a2enmod cgi perl

# Configure Apache for Perl CGI
RUN echo "<Directory /var/www/perl>" >> /etc/apache2/sites-available/000-default.conf && \
    echo "    Options +ExecCGI" >> /etc/apache2/sites-available/000-default.conf && \
    echo "    AddHandler cgi-script .pl" >> /etc/apache2/sites-available/000-default.conf && \
    echo "    DirectoryIndex index.pl" >> /etc/apache2/sites-available/000-default.conf && \
    echo "</Directory>" >> /etc/apache2/sites-available/000-default.conf && \
    echo "Alias / /var/www/perl/" >> /etc/apache2/sites-available/000-default.conf

# Create SMB shares directory
RUN mkdir -p /srv/samba/shares/public && \
    mkdir -p /srv/samba/shares/confidential && \
    chmod 777 /srv/samba/shares/public

# Add sensitive files to SMB shares
RUN echo "FLAG{smb_share_access}" > /srv/samba/shares/public/flag.txt && \
    echo "Database backup credentials: admin:SuperSecret123!" > /srv/samba/shares/confidential/db_backup.txt && \
    echo "Internal Server 3 runs on 10.10.10.4" > /srv/samba/shares/public/network_notes.txt

# Copy Samba configuration
COPY smb.conf /etc/samba/smb.conf

# Create Samba user (weak password)
RUN (echo "smbpassword"; echo "smbpassword") | smbpasswd -a -s root

# Configure Redis (no authentication - vulnerable)
RUN sed -i 's/^bind 127.0.0.1/bind 0.0.0.0/' /etc/redis/redis.conf && \
    sed -i 's/^protected-mode yes/protected-mode no/' /etc/redis/redis.conf

# Configure vsftpd
RUN echo "listen=YES" > /etc/vsftpd.conf && \
    echo "anonymous_enable=NO" >> /etc/vsftpd.conf && \
    echo "local_enable=YES" >> /etc/vsftpd.conf && \
    echo "write_enable=YES" >> /etc/vsftpd.conf && \
    echo "chroot_local_user=YES" >> /etc/vsftpd.conf && \
    echo "allow_writeable_chroot=YES" >> /etc/vsftpd.conf

# Create Perl application directory
RUN mkdir -p /var/www/perl

# Copy Perl application
COPY www/index.pl /var/www/perl/index.pl
RUN chmod +x /var/www/perl/index.pl

# Copy setup script
COPY setup.sh /setup.sh
RUN chmod +x /setup.sh

# Expose ports
EXPOSE 80 139 445 22 21 6379

# Start services
CMD /setup.sh && service ssh start && service vsftpd start && service redis-server start && service smbd start && service nmbd start && apache2ctl -D FOREGROUND
