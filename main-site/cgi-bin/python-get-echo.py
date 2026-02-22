#!/usr/bin/env python3
import os
from urllib.parse import parse_qsl

print("Cache-Control: no-cache")
print("Content-type: text/html\n")

print("""<!DOCTYPE html>
<html><head><title>GET Request Echo</title>
</head><body><h1 align="center">Get Request Echo</h1>
<hr>""")

query_string = os.environ.get('QUERY_STRING', '')
print(f"<b>Query String:</b> {query_string}<br />")

# Parse the Query String
# parse_qsl splits "key=value&key2=val2" into a list of tuples
pairs = parse_qsl(query_string)

for key, value in pairs:
    print(f"{key} = {value}<br/>")

print("</body></html>")