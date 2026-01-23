# CSE 135 - Homework 1
## Team Xuanye

### 1. Team Members
* **Name:** Xuanye Wang
* **Email:** xuw040@ucsd.edu
* **GitHub Username:** KeeevinW

### 2. Deployment Links
* **Website URL:** https://xuanye.site

---

### 3. Access Credentials

#### A. Server Access (SSH)
* **IP Address:** 165.232.48.207
* **Username:** grader
* **SSH Private Key:** see grader_key file
* To log in to the server:
    ``` bash
    # Set permissions (Mac/Linux/WSL only)
    chmod 600 grader_key

    # Connect
    ssh -i grader_key grader@165.232.48.207
    ```

#### B. Website Access
To view the site, use these credentials when the popup appears:
* **Username:** team_user
* **Password:** TEAMSITE!!

#### C. MySQL Database
- Username: root
- Password: CSE135MYSQL!!

### 4. Technical Report

#### Part 2: GitHub Actions Deployment
I set up a CI/CD pipeline using GitHub Actions to automate deployment.

1. I created a `.github/workflows/deploy.yml` file in the repository.
2. The workflow listens for `push` events to the `main` branch.
3. I generated a dedicated SSH Deploy Key. The public key was added to the server's `authorized_keys`, and the private key was stored securely in GitHub Repo Secrets (`SSH_PRIVATE_KEY`), along with the host IP.
4. When code is pushed, GitHub launches a runner that uses `rsync` over SSH to synchronize the files from the GitHub repository directly to `/var/www/xuanye.site` on the DigitalOcean server.

#### Part 3, Step 5: Compression (mod_deflate)
Observation: After enabling mod_deflate and restarting Apache, I inspected the Network tab in Chrome DevTools.

- I verified that the Content-Encoding header was set to gzip.

- Result: The "Transferred" size of the HTML file (1.9kB) was smaller than the actual "Resource" size (4.3kB). This confirms that the server is compressing text files before sending them to the browser, which reduces bandwidth usage and speeds up page load times.

#### Part 3, Step 6: Server Identity (Obscuring Headers)
How it was achieved: Standard Apache configuration (`ServerTokens`) only allows hiding version numbers, not changing the name entirely. To achieve the requirement of changing the header to "CSE135 Server":

1. I installed libapache2-mod-security2 (ModSecurity).

2. I set `ServerTokens Full` in the Apache security config to ensure a header was generated.

3. I configured ModSecurity with the directive `SecServerSignature "CSE135 Server"`.

4. This forces the server to intercept the outgoing header and replace the string "Apache..." with "CSE135 Server".

### 6. Extra Credit: Analytics System (Matomo)

**Installation Process:**
1.  Installed required PHP extensions (`php-xml`, `php-mbstring`, `php-gd`, `php-curl`) on the LAMP stack.
2.  Created a MySQL database (`matomo`) and user (`matomo_admin`) for analytics data storage.
3.  Downloaded the official Matomo release to `/var/www/xuanye.site/matomo`, adjusted file permissions for Apache (`www-data`), and ran the web-based installation wizard.
4.  Embedded the generated JavaScript tracking code into `index.html` to capture real-time visitor data.

#### Matomo Login Credentials
- Username: admin
- Password: teamadmin123!

**Verification Link:**
* [Matomo Login Page](https://xuanye.site/matomo)