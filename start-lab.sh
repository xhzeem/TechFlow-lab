#!/bin/bash

echo "🎯 Penetration Testing Lab - Quick Start Script"
echo "================================================"
echo ""

# Check if Docker is running
if ! docker info > /dev/null 2>&1; then
    echo "❌ Error: Docker is not running. Please start Docker first."
    exit 1
fi

echo "✅ Docker is running"
echo ""

# Check if docker compose is available
if ! docker compose version > /dev/null 2>&1; then
    echo "❌ Error: docker compose is not available."
    exit 1
fi

echo "✅ docker compose is available"
echo ""

# Stop any existing containers
echo "🛑 Stopping existing containers..."
docker compose down 2>/dev/null

echo ""
# Pre-create uploads directory and set permissions
echo "📁 Setting up asset repository permissions..."
mkdir -p web-server/www/uploads
chmod 777 web-server/www/uploads

# Build and start containers
echo "🚀 Starting the lab environment..."
docker compose up -d --build

echo ""
echo "⏳ Waiting for services to initialize..."
sleep 10

echo ""
echo "📊 Container Status:"
docker compose ps

echo ""
echo "✅ Lab is ready!"
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "🌐 Access Points:"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "  📱 Web Application:  http://localhost:8080"
echo "  📁 FTP Server:       ftp localhost 2121"
echo "                       (anonymous login)"
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "🎯 Quick Tips:"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "  1. Start with the web application at http://localhost:8080"
echo "  2. Try SQL injection in the login form"
echo "  3. Check FTP for leaked credentials"
echo "  4. Look for file upload vulnerabilities"
echo "  5. Use chisel for pivoting to internal network"
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "📚 Documentation:"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "  README.md  - Full documentation"
echo "  HINTS.md   - Progressive hints (no spoilers)"
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "🛠️  Useful Commands:"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "  View logs:           docker compose logs -f"
echo "  Stop lab:            docker compose down"
echo "  Reset lab:           docker compose down -v && docker compose up -d"
echo "  Access web shell:    docker exec -it pentest-web /bin/bash"
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "⚠️  WARNING: This lab contains intentionally vulnerable"
echo "    services. NEVER expose to the internet!"
echo ""
echo "Happy Hacking! 🎉"
echo ""
