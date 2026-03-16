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

### 2. Testing Scenario
To evaluate the project, please follow these steps:
1. Log in as the **Basic Viewer**. Observe that you are immediately redirected away from the raw data dashboard (403 or Saved Reports stub), validating that viewers cannot see raw data.
2. Log out, and log back in as the **Data Analyst**. 
3. Scroll down the dashboard to view the three distinct report categories (Performance, Behavior, and System). 
4. Type a brief analysis in the "Analyst Interpretation" text area under Performance Metrics and click "Save Comment". Refresh the page to verify the text persists.
5. Click the **"Export to PDF"** button at the top right.
6. Open the downloaded PDF. Verify that the layout is clean, the Chart.js canvases rendered successfully, and your written comment is visible (while the 'Save' button is hidden).

---

### 3. Known Bugs & Architectural Concerns

In the spirit of accountability, here are a few areas of the architecture that could be improved or that have known limitations:

* The PDF export relies heavily on the user's browser to render the canvas elements via `html2pdf.js`. While this bypasses the issue of server-side PHP libraries failing to capture JavaScript charts, it means the quality and formatting of the PDF can occasionally vary depending on the client's screen size or browser engine.
* The Analyst Comment saving mechanism utilizes browser `localStorage` rather than a MySQL backend. This means comments are device-specific and will not globally sync for other analysts viewing the dashboard.