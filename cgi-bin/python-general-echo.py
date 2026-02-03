#!/usr/bin/env python3
import os
import sys
import datetime

print("Cache-Control: no-cache")
print("Content-type: text/html\n")

print("""<!DOCTYPE html>
<html><head><title>General Request Echo</title>
</head><body><h1 align="center">General Request Echo</h1>
<hr>""")

print(f"<p><b>HTTP Protocol:</b> {os.environ.get('SERVER_PROTOCOL', '')}</p>")
print(f"<p><b>HTTP Method:</b> {os.environ.get('REQUEST_METHOD', '')}</p>")
print(f"<p><b>Query String:</b> {os.environ.get('QUERY_STRING', '')}</p>")

# Read Message Body from Standard Input
try:
    content_length = int(os.environ.get('CONTENT_LENGTH', 0))
except (ValueError, TypeError):
    content_length = 0

body = sys.stdin.read(content_length)

print(f"<p><b>Message Body:</b> {body}</p>")

print(f"<p><b>Remote IP:</b> {os.environ.get('REMOTE_ADDR', '')}</p>")
print(f"<p><b>User Agent:</b> {os.environ.get('HTTP_USER_AGENT', '')}</p>")
print(f"<p><b>Host:</b> {os.environ.get('HTTP_HOST', '')}</p>")
print(f"<p><b>Date/Time:</b> {str(datetime.datetime.now())}</p>")

print("</body></html>")