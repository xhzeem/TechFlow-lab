# 🎯 Penetration Testing Lab

A comprehensive Docker-based penetration testing lab featuring vulnerable web applications, network services, and multiple exploitation paths across public and internal networks.

## 🏗️ Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                      PUBLIC NETWORK                          │
│                     (172.20.0.0/24)                         │
│                                                              │
│  ┌──────────────────┐         ┌──────────────────┐         │
│  │   Web Server     │         │   FTP Server     │         │
│  │  Port: 8080      │         │   Port: 2121     │         │
│  │  - PHP App       │         │   - Anonymous    │         │
│  │  - 10+ Vulns     │         │   - Sensitive    │         │
│  │  - 3 RCE paths   │         │     Files        │         │
│  │  - Chisel        │         │                  │         │
│  │  - Nmap          │         │                  │         │
│  └────────┬─────────┘         └──────────────────┘         │
│           │                                                  │
└───────────┼──────────────────────────────────────────────────┘
            │ PIVOT REQUIRED
            │
┌───────────┼──────────────────────────────────────────────────┐
│           │         INTERNAL NETWORK                         │
│           │        (10.10.10.0/24)                          │
│           │                                                  │
│  ┌────────▼─────────┐  ┌──────────────┐  ┌──────────────┐ │
│  │  Internal-1      │  │ Internal-2   │  │ Internal-3   │ │
│  │  10.10.10.2      │  │ 10.10.10.3   │  │ 10.10.10.4   │ │
│  │  - Web (80)      │  │ - Web (80)   │  │ - Web (80)   │ │
│  │  - SSH (22)      │  │ - SMB (445)  │  │ - Tomcat     │ │
│  │  - SSH Key       │  │ - Weak Auth  │  │   (8080)     │ │
│  │    Exposed       │  │              │  │ - MySQL      │ │
│  │                  │  │              │  │ - ProFTPD    │ │
│  └──────────────────┘  └──────────────┘  └──────────────┘ │
│                                                              │
└──────────────────────────────────────────────────────────────┘
```

## 📋 Prerequisites

- Docker Engine 20.10+
- Docker Compose 1.29+
- At least 4GB RAM
- 10GB free disk space

## 🚀 Quick Start

### 1. Clone or Navigate to Lab Directory

```bash
cd /Users/user/Desktop/TechFlow
```

### 2. Start the Lab

```bash
docker-compose up -d
```

### 3. Verify All Services Are Running

```bash
docker-compose ps
```

You should see all 6 containers running:
- `pentest-db` - MySQL database
- `pentest-web` - Main vulnerable web server
- `pentest-ftp` - FTP server
- `internal-web-ssh` - Internal server 1
- `internal-smb-web` - Internal server 2
- `internal-multi-service` - Internal server 3

### 4. Access the Lab

**Public Web Server:** http://localhost:8080  
**FTP Server:** `ftp localhost 2121` (anonymous access)

## 🎯 Lab Objectives

### Phase 1: Initial Reconnaissance
- Enumerate public services
- Identify vulnerabilities in the web application
- Gain initial access to the web server

### Phase 2: Exploitation
- Exploit web vulnerabilities for RCE
- Extract sensitive information from FTP
- Establish persistence

### Phase 3: Pivoting
- Use chisel or SSH tunneling to access internal network
- Scan internal network from compromised web server
- Map internal services

### Phase 4: Internal Exploitation
- Extract SSH key from internal-server-1
- Exploit SMB vulnerabilities on internal-server-2
- Exploit CVEs on internal-server-3
- Achieve root access on all internal servers

## 🔓 Vulnerability Summary

### Public Web Server (10+ Vulnerabilities)

| Vulnerability | Severity | Location | RCE |
|--------------|----------|----------|-----|
| SQL Injection | Critical | Login, Search | ❌ |
| Command Injection | Critical | Admin Panel | ✅ RCE #1 |
| File Upload | Critical | Upload Page | ✅ RCE #2 |
| LFI to RCE | Critical | File Viewer | ✅ RCE #3 |
| Stored XSS | High | Comments | ❌ |
| Reflected XSS | Medium | Search | ❌ |
| IDOR | High | Profile View | ❌ |
| Auth Bypass | Critical | SQL Injection | ❌ |
| SSRF | High | Admin Panel | ❌ |
| Weak Passwords | Medium | MD5 Hashing | ❌ |
| Session Issues | Medium | Various | ❌ |

### FTP Server
- Anonymous access enabled
- Sensitive files exposed
- Network information leaked

### Internal Server 1
- SSH private key exposed in web application
- Root access via SSH key
- Network information disclosure

### Internal Server 2
- SMB shares with weak authentication
- Null session enumeration
- Sensitive data in shares

### Internal Server 3
- **Tomcat 8.5.50** - CVE-2020-1938 (Ghostcat)
- **ProFTPD 1.3.5** - CVE-2015-3306
- **MySQL** - Weak root password (toor)
- **Tomcat Manager** - Default credentials (admin:tomcat)

## 🛠️ Pre-installed Tools (Web Server)

Access the web server container:
```bash
docker exec -it pentest-web /bin/bash
```

Available tools:
- **chisel** - Tunneling tool (`/usr/local/bin/chisel`)
- **nmap** - Network scanner
- **ip** - Network configuration
- **netcat** - Network utility
- **curl/wget** - HTTP clients

## 🔧 Useful Commands

### Start the Lab
```bash
docker-compose up -d
```

### Stop the Lab
```bash
docker-compose down
```

### Reset the Lab (Delete All Data)
```bash
docker-compose down -v
docker-compose up -d
```

### View Logs
```bash
docker-compose logs -f [service-name]
```

### Access Container Shell
```bash
docker exec -it [container-name] /bin/bash
```

### Network Scanning from Web Server
```bash
docker exec -it pentest-web nmap -sn 10.10.10.0/24
```

## 🌐 Network Information

### Public Network (172.20.0.0/24)
- Web Server: 172.20.0.x
- FTP Server: 172.20.0.x
- Database: 172.20.0.x

### Internal Network (10.10.10.0/24)
- Internal Server 1: 10.10.10.2
- Internal Server 2: 10.10.10.3
- Internal Server 3: 10.10.10.4

## 🎓 Learning Paths

### Beginner Path
1. SQL injection in login form
2. File upload vulnerability
3. FTP enumeration
4. Basic network scanning

### Intermediate Path
1. Command injection
2. LFI to RCE via log poisoning
3. Chisel tunneling
4. SSH key extraction
5. SMB enumeration

### Advanced Path
1. SSRF exploitation
2. Multi-hop pivoting
3. CVE exploitation (Ghostcat, ProFTPD)
4. Custom exploit development
5. Full network compromise

## ⚠️ Security Warning

**CRITICAL:** This lab contains intentionally vulnerable services.

- **NEVER** expose these containers to the internet
- **ONLY** run in isolated lab environments
- **DO NOT** use on production networks
- **ALWAYS** run behind a firewall
- **FOR EDUCATIONAL PURPOSES ONLY**

## 🐛 Troubleshooting

### Containers Won't Start
```bash
docker-compose down
docker-compose up -d --force-recreate
```

### Database Connection Issues
```bash
docker-compose restart db
docker-compose restart web-server
```

### Port Conflicts
Edit `docker-compose.yml` and change the host ports:
```yaml
ports:
  - "8080:80"  # Change 8080 to another port
```

### Can't Access Internal Network
Make sure you've compromised the web server first and are using it as a pivot point.

## 📚 Additional Resources

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [Chisel Documentation](https://github.com/jpillora/chisel)
- [Metasploit Framework](https://www.metasploit.com/)
- [HackTricks](https://book.hacktricks.xyz/)

## 📝 Hints

For progressive hints without spoilers, see [HINTS.md](HINTS.md)

## 🏆 Flags

Throughout the lab, you'll find flags in the format: `FLAG{description}`

Collect all flags to complete the lab!

## 📄 License

This lab is provided for educational purposes only. Use responsibly.

## 🤝 Contributing

Found a bug or want to add more vulnerabilities? Feel free to contribute!

---

**Happy Hacking! 🎉**
