#!/usr/bin/python3
import os
import http.cookies

cookie_string = os.environ.get('HTTP_COOKIE')
session_active = False

if cookie_string:
    cookie = http.cookies.SimpleCookie()
    cookie.load(cookie_string)
    if 'CGISESSID' in cookie:
        sess_id = cookie['CGISESSID'].value
        if os.path.exists('/tmp/sess_' + sess_id):
            session_active = True

print("Content-Type: text/html\n")

if session_active:
    print(f"""
    <html>
    <body>
        <h1>Session Active</h1>
        <p>You are already logged in.</p>
        <a href="python-sessions-2.py">Go to Session Page 2</a>
    </body>
    </html>
    """)
else:
    print("""
    <html>
    <head><title>Python Session 1</title></head>
    <body style="padding: 2rem; font-family: sans-serif;">
        <h1>Python Session Setup</h1>
        <form action="python-sessions-2.py" method="POST">
            <label>Enter Name:</label>
            <input type="text" name="username" required>
            <button type="submit">Save Session</button>
        </form>
    </body>
    </html>
    """)