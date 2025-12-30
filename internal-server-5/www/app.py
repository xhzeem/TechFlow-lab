from flask import Flask, request, send_file, render_template_string
import os

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
        .footer { margin-top: 40px; font-size: 12px; color: #7f8c8d; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
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
        
        <div class="footer">
            &copy; 2025 Asset Management System v1.0
        </div>
    </div>
</body>
</html>
'''

@app.route('/')
def index():
    return render_template_string(BASE_TEMPLATE)

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
