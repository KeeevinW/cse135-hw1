#!/usr/bin/python3
import json
import datetime
import os

print("Content-Type: application/json\n\n")

data = {
    "message": "Hello Team Xuanye",
    "language": "Python 3",
    "date": str(datetime.datetime.now()),
    "ip": os.environ.get("REMOTE_ADDR", "Unknown")
}

print(json.dumps(data))