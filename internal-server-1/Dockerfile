FROM debian:bullseye-slim

# Install Nginx, PHP-FPM, Python, OpenSSH, and vulnerable services
RUN echo 'Acquire::Retries "3";' > /etc/apt/apt.conf.d/80-retries && \
    apt-get update && \
    DEBIAN_FRONTEND=noninteractive apt-get install -y --fix-missing \
    nginx \
    php7.4-fpm \
    php7.4-cli \
    openssh-server \
    python3 \
    python3-pip \
    memcached \
    sudo \
    ncat \
    iputils-ping \
    traceroute \
    && rm -rf /var/lib/apt/lists/*

# Install Python Flask and dependencies
RUN pip3 install flask jinja2

# Configure SSH
RUN mkdir /var/run/sshd && \
    sed -i 's/#PermitRootLogin prohibit-password/PermitRootLogin yes/' /etc/ssh/sshd_config && \
    sed -i 's/#PubkeyAuthentication yes/PubkeyAuthentication yes/' /etc/ssh/sshd_config

# Configure Nginx to proxy to Flask app
RUN echo 'server { \n\
    listen 80 default_server; \n\
    location / { \n\
    proxy_pass http://127.0.0.1:5000; \n\
    proxy_set_header Host $host; \n\
    proxy_set_header X-Real-IP $remote_addr; \n\
    } \n\
    }' > /etc/nginx/sites-available/default

# Configure Memcached (no authentication - vulnerable)
RUN sed -i 's/^-l 127.0.0.1/-l 0.0.0.0/' /etc/memcached.conf

# Create web directory
RUN mkdir -p /var/www/html

# Copy setup script
COPY setup.sh /setup.sh
RUN chmod +x /setup.sh

# Copy Python Flask app
COPY www/app.py /var/www/app.py
RUN chmod +x /var/www/app.py

# Expose ports
EXPOSE 80 22 11211

# Start services
CMD ["/bin/bash", "-c", "/setup.sh && service memcached start && service ssh start && nginx && cd /var/www && python3 app.py"]
