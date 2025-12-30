#!/usr/bin/env python3
from flask import Flask, request, render_template_string, redirect, url_for, session, send_file
import os
import subprocess
import pickle
import base64
from jinja2 import DictLoader

app = Flask(__name__)
app.secret_key = 'insecure_secret_key_12345'

# Session storage (in-memory for simplicity)
users = {
    'admin': 'admin123',
    'user': 'password'
}

# Base template
BASE_TEMPLATE = '''
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Portal</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            max-width: 1000px;
            margin: 0 auto;
            padding: 40px;
        }
        h1 { color: #667eea; margin-bottom: 20px; }
        h2 { color: #333; margin-bottom: 15px; }
        .nav {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e9ecef;
            flex-wrap: wrap;
        }
        .nav a {
            padding: 10px 20px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .nav a:hover { background: #764ba2; }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input, .form-group textarea, .form-group select {
            width: 100%;
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 5px;
        }
        .btn {
            padding: 12px 30px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .btn:hover { background: #764ba2; }
        .info-box {
            background: #e8f5e9;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #667eea;
        }
        .file-link {
            display: inline-block;
            padding: 8px 15px;
            background: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 5px;
            text-decoration: none;
            color: #333;
            margin: 5px;
        }
        .file-link:hover { background: #e9ecef; }
        .output {
            background: #1e1e1e;
            color: #00ff00;
            padding: 15px;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            margin-top: 15px;
            white-space: pre-wrap;
            max-height: 400px;
            overflow-y: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>👥 Employee Portal</h1>
        {% if 'username' in session %}
        <div class="nav">
            <a href="/">Home</a>
            <a href="/profile">Profile</a>
            <a href="/resources">Resources</a>
            <a href="/templates">Templates</a>
            <a href="/reports">Reports</a>
            <a href="/settings">Settings</a>
            <a href="/logout">Logout</a>
        </div>
        {% endif %}
        {% block content %}{% endblock %}
    </div>
</body>
</html>
'''

app.jinja_env.loader = DictLoader({'base': BASE_TEMPLATE})

@app.route('/')
def home():
    if 'username' not in session:
        return redirect('/login')
    
    # SSTI Vulnerability #1 - in welcome message
    welcome_msg = request.args.get('msg', f'Welcome back, {session["username"]}!')
    
    content = f'''
    {{% extends "base" %}}
    {{% block content %}}
        <h2>{welcome_msg}</h2>
        <div class="info-box">
            <p>Access your employee resources and manage your profile.</p>
        </div>
        <ul style="margin-top: 20px; line-height: 2;">
            <li>View your profile information</li>
            <li>Download company resources</li>
            <li>Generate reports from templates</li>
            <li>Update your settings</li>
        </ul>
    {{% endblock %}}
    '''
    
    return render_template_string(content)

@app.route('/login', methods=['GET', 'POST'])
def login():
    if request.method == 'POST':
        username = request.form.get('username')
        password = request.form.get('password')
        
        if username in users and users[username] == password:
            session['username'] = username
            return redirect('/')
        else:
            error = 'Invalid credentials'
            content = f'''
            {{% extends "base" %}}
            {{% block content %}}
                <h2>Login</h2>
                <p style="color: red; margin-bottom: 15px;">{error}</p>
                <form method="POST">
                    <div class="form-group">
                        <label>Username:</label>
                        <input type="text" name="username" required>
                    </div>
                    <div class="form-group">
                        <label>Password:</label>
                        <input type="password" name="password" required>
                    </div>
                    <button type="submit" class="btn">Login</button>
                </form>
            {{% endblock %}}
            '''
            return render_template_string(content)
    
    content = '''
    {% extends "base" %}
    {% block content %}
        <h2>Login</h2>
        <form method="POST">
            <div class="form-group">
                <label>Username:</label>
                <input type="text" name="username" required>
            </div>
            <div class="form-group">
                <label>Password:</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn">Login</button>
        </form>
    {% endblock %}
    '''
    return render_template_string(content)

@app.route('/profile')
def profile():
    if 'username' not in session:
        return redirect('/login')
    
    # SSTI Vulnerability #2 - in profile bio
    custom_bio = request.args.get('bio', 'No bio set')
    
    content = f'''
    {{% extends "base" %}}
    {{% block content %}}
        <h2>Profile: {session["username"]}</h2>
        <div class="info-box">
            <p><strong>Bio:</strong> {custom_bio}</p>
        </div>
        <form method="GET">
            <div class="form-group">
                <label>Customize your bio:</label>
                <textarea name="bio" rows="3" placeholder="Enter your bio...">{custom_bio}</textarea>
            </div>
            <button type="submit" class="btn">Update</button>
        </form>
    {{% endblock %}}
    '''
    
    return render_template_string(content)

@app.route('/resources')
def resources():
    if 'username' not in session:
        return redirect('/login')
    
    content = '''
    {% extends "base" %}
    {% block content %}
        <h2>Company Resources</h2>
        <div class="info-box">
            <p>Download important company documents and resources.</p>
        </div>
        <h3 style="margin-top: 20px;">Available Files:</h3>
        <div style="margin-top: 15px;">
            <a href="/.ssh_key" class="file-link" download>🔑 SSH Access Key</a>
            <a href="/resources?file=handbook" class="file-link">📖 Employee Handbook</a>
            <a href="/resources?file=policies" class="file-link">📋 Company Policies</a>
        </div>
    {% endblock %}
    '''
    
    return render_template_string(content)

@app.route('/templates', methods=['GET', 'POST'])
def templates():
    if 'username' not in session:
        return redirect('/login')
    
    output = ''
    
    if request.method == 'POST':
        template_content = request.form.get('template', '')
        data = request.form.get('data', '')
        
        # SSTI Vulnerability #3 - in template rendering
        try:
            result = render_template_string(template_content, data=data)
            output = result
        except Exception as e:
            output = f"Error: {str(e)}"
    
    content = f'''
    {{% extends "base" %}}
    {{% block content %}}
        <h2>Template Generator</h2>
        <div class="info-box">
            <p>Generate documents from templates with custom data.</p>
        </div>
        <form method="POST">
            <div class="form-group">
                <label>Template:</label>
                <textarea name="template" rows="5" placeholder="Enter template content..."></textarea>
            </div>
            <div class="form-group">
                <label>Data:</label>
                <input type="text" name="data" placeholder="Enter data...">
            </div>
            <button type="submit" class="btn">Generate</button>
        </form>
        {f'<div class="output">{output}</div>' if output else ''}
    {{% endblock %}}
    '''
    
    return render_template_string(content)

@app.route('/reports', methods=['GET', 'POST'])
def reports():
    if 'username' not in session:
        return redirect('/login')
    
    output = ''
    
    if request.method == 'POST':
        # Insecure Deserialization Vulnerability (RCE #2)
        report_data = request.form.get('report_data', '')
        
        if report_data:
            try:
                decoded = base64.b64decode(report_data)
                obj = pickle.loads(decoded)  # Vulnerable!
                output = f"Report loaded: {obj}"
            except Exception as e:
                output = f"Error: {str(e)}"
    
    content = f'''
    {{% extends "base" %}}
    {{% block content %}}
        <h2>Report Manager</h2>
        <div class="info-box">
            <p>Load and manage saved reports.</p>
        </div>
        <form method="POST">
            <div class="form-group">
                <label>Report Data (Base64):</label>
                <textarea name="report_data" rows="5" placeholder="Paste base64 encoded report data..."></textarea>
            </div>
            <button type="submit" class="btn">Load Report</button>
        </form>
        {f'<div class="output">{output}</div>' if output else ''}
    {{% endblock %}}
    '''
    
    return render_template_string(content)

@app.route('/settings', methods=['GET', 'POST'])
def settings():
    if 'username' not in session:
        return redirect('/login')
    
    message = ''
    
    if request.method == 'POST':
        # Save preferences
        theme = request.form.get('theme', 'light')
        language = request.form.get('language', 'en')
        message = f"Settings saved: Theme={theme}, Language={language}"
    
    content = f'''
    {{% extends "base" %}}
    {{% block content %}}
        <h2>Settings</h2>
        <div class="info-box">
            <p>Customize your portal experience.</p>
        </div>
        {f'<p style="color: green; margin-bottom: 15px;">{message}</p>' if message else ''}
        <form method="POST">
            <div class="form-group">
                <label>Theme:</label>
                <select name="theme">
                    <option value="light">Light</option>
                    <option value="dark">Dark</option>
                </select>
            </div>
            <div class="form-group">
                <label>Language:</label>
                <select name="language">
                    <option value="en">English</option>
                    <option value="es">Spanish</option>
                    <option value="fr">French</option>
                </select>
            </div>
            <button type="submit" class="btn">Save Settings</button>
        </form>
    {{% endblock %}}
    '''
    
    return render_template_string(content)

@app.route('/.ssh_key')
def send_ssh_key():
    try:
        return send_file('.ssh_key')
    except:
        return "SSH Key not found", 404

@app.route('/logout')
def logout():
    session.clear()
    return redirect('/login')

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000, debug=False)
