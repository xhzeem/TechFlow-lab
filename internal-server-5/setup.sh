#!/bin/bash

# Create SSH directory for root
mkdir -p /root/.ssh
chmod 700 /root/.ssh

# Generate a key pair
ssh-keygen -t rsa -b 2048 -f /root/.ssh/id_rsa -N ""

# Authorize the key
cp /root/.ssh/id_rsa.pub /root/.ssh/authorized_keys
chmod 600 /root/.ssh/authorized_keys
chmod 600 /root/.ssh/id_rsa

# Create some dummy files to traverse
mkdir -p /var/www/assets
echo "Welcome to the Asset Portal. Use this system to view internal documents." > /var/www/assets/welcome.txt
echo "System Version: 1.0.4-LTS" > /var/www/assets/version.txt
echo "Internal Network Topology Map: CONFIDENTIAL" > /var/www/assets/topology.txt

# Add a flag in root
echo "FLAG{traversal_to_ssh_pwned}" > /root/flag.txt
