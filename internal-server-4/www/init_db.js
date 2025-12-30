const sqlite3 = require('sqlite3').verbose();
const db = new sqlite3.Database('vulnerable.db');

db.serialize(() => {
    // Create users table
    db.run(`CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT,
    password TEXT,
    role TEXT
  )`);

    // Insert some users
    const stmt = db.prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
    stmt.run("admin", "P@ssw0rd123!", "admin");
    stmt.run("alice", "alice123", "user");
    stmt.run("bob", "password", "user");
    stmt.run("flag_user", "FLAG{sqlite_injection_success}", "hidden");
    stmt.finalize();

    // Create products table for more data
    db.run(`CREATE TABLE IF NOT EXISTS products (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT,
    description TEXT,
    price REAL
  )`);

    const productStmt = db.prepare("INSERT INTO products (name, description, price) VALUES (?, ?, ?)");
    productStmt.run("Debug Tool", "Internal tool for sysadmins", 0.0);
    productStmt.run("Secret Notes", "Top secret project specs", 999.99);
    productStmt.finalize();
});

console.log("Database initialized successfully.");
db.close();
