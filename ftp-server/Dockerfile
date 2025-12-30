FROM debian:bullseye-slim

# Install vsftpd
RUN echo 'Acquire::Retries "3";' > /etc/apt/apt.conf.d/80-retries && \
    apt-get update && \
    DEBIAN_FRONTEND=noninteractive apt-get install -y --fix-missing \
    vsftpd \
    ncat \
    && rm -rf /var/lib/apt/lists/*

# Create FTP directory and add sensitive files
RUN mkdir -p /var/ftp/pub && \
    chmod 755 /var/ftp/pub

# Add some "leaked" sensitive files
RUN echo "admin:5f4dcc3b5aa765d61d8327deb882cf99" > /var/ftp/pub/credentials.txt && \
    echo "Internal network: 10.10.10.0/24" > /var/ftp/pub/network-info.txt && \
    echo "SSH Key found in internal-server-1 web application" > /var/ftp/pub/hints.txt && \
    echo "FLAG{ftp_anonymous_access}" > /var/ftp/pub/flag.txt

# Copy vsftpd configuration
COPY vsftpd.conf /etc/vsftpd.conf

# Expose FTP ports
EXPOSE 21 21000-21010

# Start vsftpd
CMD ["/usr/sbin/vsftpd", "/etc/vsftpd.conf"]
