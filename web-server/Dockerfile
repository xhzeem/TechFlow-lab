FROM php:7.4-apache

# Install system dependencies and pentesting tools
RUN echo 'Acquire::Retries "3";' > /etc/apt/apt.conf.d/80-retries && \
    apt-get update && \
    DEBIAN_FRONTEND=noninteractive apt-get install -y --fix-missing \
    wget \
    curl \
    netcat-traditional \
    nmap \
    iproute2 \
    iputils-ping \
    dnsutils \
    vim \
    nano \
    git \
    unzip \
    python3 \
    python3-pip \
    && docker-php-ext-install mysqli pdo pdo_mysql \
    && rm -rf /var/lib/apt/lists/*

# Install Chisel for tunneling
RUN arch=$(uname -m) && \
    if [ "$arch" = "x86_64" ]; then chisel_arch="amd64"; else chisel_arch="arm64"; fi && \
    wget https://github.com/jpillora/chisel/releases/download/v1.9.1/chisel_1.9.1_linux_${chisel_arch}.gz \
    && gunzip chisel_1.9.1_linux_${chisel_arch}.gz \
    && mv chisel_1.9.1_linux_${chisel_arch} /usr/local/bin/chisel \
    && chmod +x /usr/local/bin/chisel

# Enable Apache modules
RUN a2enmod rewrite

# Create uploads directory with write permissions
RUN mkdir -p /var/www/html/uploads && chmod 777 /var/www/html/uploads

# Set working directory
WORKDIR /var/www/html

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]
