#!/usr/bin/python3
import datetime
import os
ip_address = os.environ.get('REMOTE_ADDR', '127.0.0.1')

print("Content-Type: text/html\n\n")
print("<html><head><title>Hello Python</title></head><body>")
print("<h1>Hello Team Xuanye (Python)</h1>")
print("<p>This page was generated with Python 3. Greetings from Xuanye!</p>")
print(f"<p>Current Time: {datetime.datetime.now()}</p>")
print(f"<p>Your IP Address: <strong>{ip_address}</strong></p>")
print("</body></html>")