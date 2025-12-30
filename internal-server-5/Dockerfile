FROM debian:bullseye-slim

# Install dependencies
RUN apt-get update && apt-get install -y \
    python3 \
    python3-pip \
    openssh-server \
    curl \
    iputils-ping \
    traceroute \
    && rm -rf /var/lib/apt/lists/*

RUN pip3 install flask

# Configure SSH
RUN mkdir /var/run/sshd && \
    sed -i 's/#PermitRootLogin prohibit-password/PermitRootLogin yes/' /etc/ssh/sshd_config && \
    sed -i 's/#PubkeyAuthentication yes/PubkeyAuthentication yes/' /etc/ssh/sshd_config

# Set up web app
WORKDIR /var/www
COPY www/ /var/www/
COPY setup.sh /setup.sh
RUN chmod +x /setup.sh

# Expose web and SSH
EXPOSE 80 22

CMD ["/bin/bash", "-c", "/setup.sh && service ssh start && python3 app.py"]
