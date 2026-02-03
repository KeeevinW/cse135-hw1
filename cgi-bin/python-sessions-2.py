#!/usr/bin/python3
import cgi
import os
import time
import http.cookies
import uuid

# Setup
form = cgi.FieldStorage()
username = form.getvalue('username')
destroy = form.getvalue('destroy')

cookie = http.cookies.SimpleCookie()
session_id = None
cookie_string = os.environ.get('HTTP_COOKIE')

# 1. LOAD SESSION ID from Cookie
if cookie_string:
    cookie.load(cookie_string)
    if 'CGISESSID' in cookie:
        session_id = cookie['CGISESSID'].value

# 2. HANDLE DESTROY
if destroy:
    if session_id and os.path.exists(f'/tmp/sess_{session_id}'):
        os.remove(f'/tmp/sess_{session_id}')
    print("Set-Cookie: CGISESSID=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/")
    print("Content-Type: text/html\n")
    print("<html><body>Session Destroyed. <a href='python-sessions-1.py'>Back</a></body></html>")
    exit()

# 3. HANDLE NEW LOGIN
if username:
    # Generate new ID
    session_id = str(uuid.uuid4())
    # Save to server file
    with open(f'/tmp/sess_{session_id}', 'w') as f:
        f.write(username)
    # Set Cookie Header (Must happen before Content-Type)
    print(f"Set-Cookie: CGISESSID={session_id}; path=/")

# 4. READ SESSION DATA
stored_name = "Unknown"
if session_id and os.path.exists(f'/tmp/sess_{session_id}'):
    with open(f'/tmp/sess_{session_id}', 'r') as f:
        stored_name = f.read()

print("Content-Type: text/html\n")
print(f"""
<html>
<body style="padding: 2rem; font-family: sans-serif;">
    <h1>Python Session Page 2</h1>
    <p>Stored Name on Server: <strong>{stored_name}</strong></p>
    <p>Session ID: {session_id}</p>
    
    <form action="python-sessions-2.py" method="POST">
        <input type="hidden" name="destroy" value="true">
        <button type="submit">Destroy Session</button>
    </form>
    <br>
    <a href="python-sessions-1.py">Back to Page 1</a>
</body>
</html>
""")