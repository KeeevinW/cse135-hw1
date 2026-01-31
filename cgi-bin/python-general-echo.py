#!/usr/bin/python3
import cgi
import cgitb
import os
import sys

# Enable error reporting
cgitb.enable()

print("Content-Type: text/html\n\n")
print("<html><body><h1>Python Echo</h1>")

# Print Request Details
print(f"<p><b>Method:</b> {os.environ.get('REQUEST_METHOD')}</p>")
print(f"<p><b>Protocol:</b> {os.environ.get('SERVER_PROTOCOL')}</p>")

# Handle Form Data
form = cgi.FieldStorage()

print("<h3>Form Data Received:</h3><ul>")
if form.keys():
    for key in form.keys():
        print(f"<li><b>{key}:</b> {form.getvalue(key)}</li>")
else:
    print("<li>No data received (or JSON body sent - Python CGI needs extra logic for raw JSON)</li>")
print("</ul>")

# Handle Raw JSON Input (if sent as application/json)
if os.environ.get("CONTENT_TYPE") == "application/json":
    try:
        raw_data = sys.stdin.read()
        print(f"<h3>Raw JSON Body:</h3><pre>{raw_data}</pre>")
    except:
        pass

print("</body></html>")