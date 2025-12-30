FROM node:18-bullseye-slim

# Install system dependencies
RUN apt-get update && \
    DEBIAN_FRONTEND=noninteractive apt-get install -y --no-install-recommends \
    iputils-ping \
    traceroute \
    curl \
    sqlite3 \
    redis-server \
    supervisor \
    && rm -rf /var/lib/apt/lists/*

# Create app directory
WORKDIR /app

# Copy package.json
COPY www/package.json ./

# Install dependencies
RUN npm install

# Copy application files
COPY www/ ./
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Initialize database
RUN node init_db.js

# Expose ports
EXPOSE 3000 6379

# Start supervisor
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
