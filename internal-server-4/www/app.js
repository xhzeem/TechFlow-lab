const express = require('express');
const sqlite3 = require('sqlite3').verbose();
const axios = require('axios');
const { exec } = require('child_process');
const fs = require('fs');
const path = require('path');
const session = require('express-session');
const cookieParser = require('cookie-parser');
const redis = require('redis');

const app = express();
const port = 3000;

// Setup database
const db = new sqlite3.Database('vulnerable.db');

// Setup Redis (Unauth)
const redisClient = redis.createClient({ url: 'redis://localhost:6379' });
redisClient.on('error', err => console.log('Redis Client Error', err));
redisClient.connect();

app.set('view engine', 'ejs');
app.set('views', path.join(__dirname, 'views'));
app.use(express.static('public'));
app.use(express.urlencoded({ extended: true }));
app.use(cookieParser());
app.use(session({
    secret: 'secret-key-12345',
    resave: false,
    saveUninitialized: true,
    cookie: { secure: false }
}));

// --- Middleware ---

const isAuthenticated = (req, res, next) => {
    if (req.session.user) {
        return next();
    }
    res.redirect('/login');
};

// --- Routes ---

app.get('/login', (req, res) => {
    res.render('login', { error: null });
});

app.post('/login', (req, res) => {
    const { username, password } = req.body;
    // VULNERABLE: SQL Injection in Login
    const query = `SELECT * FROM users WHERE username = '${username}' AND password = '${password}'`;

    db.get(query, [], (err, row) => {
        if (err) {
            return res.render('login', { error: 'Database error: ' + err.message });
        }
        if (row) {
            req.session.user = row;
            res.redirect('/dashboard');
        } else {
            res.render('login', { error: 'Invalid credentials' });
        }
    });
});

app.get('/logout', (req, res) => {
    req.session.destroy();
    res.redirect('/login');
});

app.get('/', (req, res) => {
    res.redirect('/dashboard');
});

app.get('/dashboard', isAuthenticated, (req, res) => {
    res.render('dashboard', { user: req.session.user });
});

// IDOR: View profile
app.get('/profile/:id', isAuthenticated, (req, res) => {
    const userId = req.params.id;
    // VULNERABLE: No check if the logged in user is authorized to see this ID
    const query = `SELECT id, username, role FROM users WHERE id = ${userId}`;

    db.get(query, [], (err, row) => {
        if (err || !row) {
            return res.status(404).send('User not found');
        }
        res.render('profile', { profile: row, user: req.session.user });
    });
});

// SSRF: Fetch external data
app.get('/tools/fetch', isAuthenticated, async (req, res) => {
    const url = req.query.url;
    if (!url) return res.render('fetch', { content: null, user: req.session.user });

    try {
        // VULNERABLE: SSRF
        const response = await axios.get(url, { timeout: 3000 });
        res.render('fetch', { content: response.data, user: req.session.user, url });
    } catch (error) {
        res.render('fetch', { content: 'Error: ' + error.message, user: req.session.user, url });
    }
});

// Path Traversal: View logs
app.get('/tools/logs', isAuthenticated, (req, res) => {
    const logFile = req.query.file || 'app.log';
    // VULNERABLE: Path Traversal
    const filePath = path.join(__dirname, 'logs', logFile);

    fs.readFile(filePath, 'utf8', (err, data) => {
        if (err) return res.render('logs', { content: 'Log file not found: ' + logFile, user: req.session.user });
        res.render('logs', { content: data, user: req.session.user });
    });
});

// Safe Network Tools: Ping tool
app.get('/tools/ping', isAuthenticated, (req, res) => {
    res.render('ping_result', { output: null, user: req.session.user });
});

app.post('/tools/ping', isAuthenticated, (req, res) => {
    const host = req.body.host;
    // SECURE: Validate input.
    if (!/^[a-zA-Z0-9.-]+$/.test(host)) {
        return res.render('ping_result', { output: 'Invalid host format', user: req.session.user });
    }

    const cmd = `ping -c 4 ${host}`;
    exec(cmd, (error, stdout, stderr) => {
        res.render('ping_result', { output: stdout + stderr, user: req.session.user });
    });
});

// EJS SSTI: Email Template Preview
app.get('/tools/email-preview', isAuthenticated, (req, res) => {
    res.render('email_preview', { template: '', result: null, user: req.session.user });
});

app.post('/tools/email-preview', isAuthenticated, (req, res) => {
    const { template } = req.body;
    try {
        // VULNERABLE: Rendering user-provided string as an EJS template
        const result = require('ejs').render(template, { user: req.session.user });
        res.render('email_preview', { template, result, user: req.session.user });
    } catch (err) {
        res.render('email_preview', { template, result: 'Error: ' + err.message, user: req.session.user });
    }
});

// Redis: Notes feature (Unauth access demonstration)
app.get('/notes', isAuthenticated, async (req, res) => {
    try {
        const keys = await redisClient.keys('note:*');
        const notes = [];
        for (const key of keys) {
            const content = await redisClient.get(key);
            notes.push({ id: key.split(':')[1], content });
        }
        res.render('notes', { notes, user: req.session.user });
    } catch (err) {
        res.status(500).send('Redis error');
    }
});

app.post('/notes/add', isAuthenticated, async (req, res) => {
    const { content } = req.body;
    const id = Date.now();
    await redisClient.set(`note:${id}`, content);
    res.redirect('/notes');
});

// API endpoint for search (SQLi)
app.get('/api/search', isAuthenticated, (req, res) => {
    const term = req.query.q;
    const query = `SELECT * FROM products WHERE name LIKE '%${term}%'`;
    db.all(query, [], (err, rows) => {
        if (err) return res.status(500).json({ error: err.message });
        res.json(rows);
    });
});

// Initialize dummy logs
if (!fs.existsSync(path.join(__dirname, 'logs'))) {
    fs.mkdirSync(path.join(__dirname, 'logs'));
}
fs.writeFileSync(path.join(__dirname, 'logs', 'app.log'), 'Admin Portal Started.\nUser admin logged in.\nSystem health: OK.');

app.listen(port, () => {
    console.log(`Vulnerable Admin Portal listening at http://localhost:${port}`);
});
