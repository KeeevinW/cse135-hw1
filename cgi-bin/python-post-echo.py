#!/usr/bin/env python3
import os
import sys
from urllib.parse import parse_qsl

print("Cache-Control: no-cache")
print("Content-type: text/html\n")

print("""<!DOCTYPE html>
<html><head><title>POST Request Echo</title>
</head><body><h1 align="center">POST Request Echo</h1>
<hr>""")

# Read Message Body
try:
    content_length = int(os.environ.get('CONTENT_LENGTH', 0))
except (ValueError, TypeError):
    content_length = 0

form_data = sys.stdin.read(content_length)

# Parse the Body
pairs = parse_qsl(form_data)

print("<b>Message Body:</b><br />")
print("<ul>")

for key, value in pairs:
    print(f"<li>{key} = {value}</li>")

print("</ul>")
print("</body></html>")