# HW 2 Extra Credit: Browser Fingerprinting

## 1. Client-Side
I utilized the `FingerprintJS` open-source library (https://github.com/fingerprintjs/fingerprintjs). This script runs in the browser and analyzes the user's unique hardware and software configuration. These attributes are combined to generate a unique "Visitor ID" hash.

## 2. Server-Side
I created a custom endpoint `fingerprint-handler.php`. When a user enters data, the server saves it into a JSON file (`fp_db.json`), using the Visitor ID as the key. On page load, the client sends its Visitor ID to the server. The server checks if this ID exists in the database. If a match is found, the server returns the saved user data, effectively logging the user back in without cookies.

## How to Verify
1.  Go to the Fingerprint Demo page.
2.  Enter a name and click "Save".
3.  Click "Delete Cookies & Reload".
4.  Notice that normally this would clear the form, but the name reappears automatically because the server recognized the browser's fingerprint.
