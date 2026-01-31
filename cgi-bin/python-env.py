#!/usr/bin/python3
import os

print("Content-Type: text/html\n\n")
print("<html><body><h1>Environment Variables</h1><ul>")

for key, value in os.environ.items():
    print(f"<li><b>{key}:</b> {value}</li>")

print("</ul></body></html>")