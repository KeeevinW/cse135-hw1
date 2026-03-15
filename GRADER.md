# CSE 135 - Final Project (HW 5)
## Grader Testing Guide & Documentation
**Team:** Xuanye

---

### 1. Access Credentials

**Site Basic Auth (if prompted by the browser):**
* **Username:** `team_user`
* **Password:** `TEAMSITE!!`

**Dashboard Role-Based Accounts:**
* **Super Admin:** * Username: `superadmin`
  * Password: `adminpass`
* **Analyst:** * Username: `data_analyst`
  * Password: `analystpass`
* **Viewer:** * Username: `basic_viewer`
  * Password: `viewerpass`


---

### 2. Recommended Testing Scenario

To fully evaluate the features implemented for this final push, please follow these steps:

1. **Log in as an Analyst or Super Admin:** Navigate to `https://xuanye.site/login.php` and log in using the Analyst credentials (`data_analyst` / `analystpass`).
2. **Review the Dashboard:** You will be routed to `dashboard.php`. Observe the Tailwind CSS-styled layout containing the three required report categories: Performance Metrics (Line Chart), User Behavior (Bar Chart + Data Table), and System Data (Doughnut Chart).
3. **Test the PDF Export:** Click the "Export to PDF" button at the top right of the dashboard. This uses client-side rendering (`html2pdf.js`) to capture the charts and tables and will automatically download a PDF formatted report to your machine.
4. **Test the Analyst Comments UI:** Type a comment in the "Analyst Comments" box under the Performance section. Notice that this section is intentionally excluded from the PDF export so the final generated report remains clean.
5. **Log Out:** Click the "Logout" button in the sidebar.
6. **Test Role-Based Access Control (RBAC):** Log back in using the Viewer credentials (`basic_viewer` / `viewerpass`). Attempt to navigate directly to `dashboard.php`. You should be immediately intercepted by the RBAC session logic and redirected to a custom `403.php` Access Denied page.
7. **Test "Script Off" Contingency:** Disable JavaScript in your browser and attempt to load `login.php` or the dashboard. A `<noscript>` warning banner will appear gracefully informing the user that the dashboard requires JavaScript to render charts.

---

### 3. Known Bugs & Architectural Concerns

In the spirit of accountability, here are a few areas of the architecture that could be improved or that have known limitations:

* **Tailwind CSS via CDN:** To achieve a visually organized and modern UI, the dashboard uses the Tailwind Play CDN. While excellent for prototyping, this is not a production-ready architectural choice. In a real-world scenario, this adds network latency and should be replaced with a compiled CSS file using a build step (Node.js/PostCSS).
* **Client-Side PDF Generation:** The PDF export relies heavily on the user's browser to render the canvas elements via `html2pdf.js`. While this bypasses the issue of server-side PHP libraries failing to capture JavaScript charts, it means the quality and formatting of the PDF can occasionally vary depending on the client's screen size or browser engine.
* **Analyst Comments Persistence:** The UI for Analyst Comments is present to fulfill the visual requirements of the dashboard, but the "Save Comment" button does not currently trigger an AJAX/fetch request to persist the text back to the MySQL database.
* **Session Security:** While the authentication system properly hashes passwords and checks roles, the session management is relatively basic. To be fully secure, it should implement session ID regeneration upon login and utilize CSRF tokens on forms to prevent cross-site request forgery.