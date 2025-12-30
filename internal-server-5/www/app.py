from flask import Flask, request, send_file, render_template_string
import os
from jinja2 import DictLoader

app = Flask(__name__)

BASE_TEMPLATE = '''
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asset Portal</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f7f6; padding: 40px; color: #333; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 40px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        h1 { color: #2c3e50; }
        .asset-list { margin-top: 20px; }
        .asset-item { padding: 15px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; }
        .asset-item:last-child { border-bottom: none; }
        .btn { padding: 8px 16px; background: #3498db; color: white; text-decoration: none; border-radius: 4px; font-size: 14px; }
        .btn:hover { background: #2980b9; }
        .nav { display: flex; gap: 15px; margin-bottom: 30px; border-bottom: 1px solid #eee; padding-bottom: 15px; }
        .nav a { color: #3498db; text-decoration: none; font-weight: 600; font-size: 14px; }
        .nav a:hover { color: #2c3e50; }
        .footer { margin-top: 40px; font-size: 12px; color: #7f8c8d; text-align: center; border-top: 1px solid #eee; padding-top: 20px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 600; }
        .form-group input, .form-group textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="nav">
            <a href="/">Home</a>
            <a href="/history">History</a>
            <a href="/feedback">Feedback</a>
        </div>
        {% block content %}{% endblock %}
        <div class="footer">
            &copy; 2025 Asset Management System v1.1
        </div>
    </div>
</body>
</html>
'''

app.jinja_env.loader = DictLoader({'base': BASE_TEMPLATE})

HOME_CONTENT = '''
{% extends "base" %}
{% block content %}
    <h1>📁 Internal Asset Portal</h1>
    <p>Access and download system assets and documentation.</p>
    
    <div class="asset-list">
        <div class="asset-item">
            <span>Welcome Document</span>
            <a href="/download?file=welcome.txt" class="btn">View</a>
        </div>
        <div class="asset-item">
            <span>System Version Info</span>
            <a href="/download?file=version.txt" class="btn">View</a>
        </div>
        <div class="asset-item">
            <span>Network Topology</span>
            <a href="/download?file=topology.txt" class="btn">View</a>
        </div>
    </div>
{% endblock %}
'''

FEEDBACK_CONTENT = '''
{% extends "base" %}
{% block content %}
    <h1>📝 Service Feedback</h1>
    <p>We value your feedback on the Asset Portal service.</p>
    {% if message %}
        <p style="color: green;">{{ message }}</p>
    {% endif %}
    <form method="POST">
        <div class="form-group">
            <label>Subject:</label>
            <input type="text" name="subject" required>
        </div>
        <div class="form-group">
            <label>Message:</label>
            <textarea name="message" rows="5" required></textarea>
        </div>
        <button type="submit" class="btn">Submit Feedback</button>
    </form>
{% endblock %}
'''

HISTORY_CONTENT = '''
{% extends "base" %}
{% block content %}
    <h1>📜 Recent Downloads</h1>
    <p>Your recent download activity on this server.</p>
    <div class="asset-list">
        <div class="asset-item">
            <span>welcome.txt</span>
            <span>2025-12-30 08:12</span>
        </div>
        <div class="asset-item">
            <span>topology.txt</span>
            <span>2025-12-29 14:45</span>
        </div>
        <div class="asset-item">
            <span>version.txt</span>
            <span>2025-12-28 10:20</span>
        </div>
    </div>
{% endblock %}
'''

@app.route('/')
def index():
    return render_template_string(HOME_CONTENT)

@app.route('/feedback', methods=['GET', 'POST'])
def feedback():
    message = None
    if request.method == 'POST':
        message = "Thank you for your feedback!"
    return render_template_string(FEEDBACK_CONTENT, message=message)

@app.route('/history')
def history():
    return render_template_string(HISTORY_CONTENT)

@app.route('/download')
def download():
    filename = request.args.get('file')
    if not filename:
        return "File parameter missing", 400
    
    # Path Traversal Vulnerability
    base_path = '/var/www/assets/'
    file_path = os.path.join(base_path, filename)
    
    try:
        return send_file(file_path)
    except Exception as e:
        return f"Error: {str(e)}", 404

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=80)
迫使
