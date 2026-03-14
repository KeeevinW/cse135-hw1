<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Forbidden</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background-color: #f4f6f8;
            color: #333;
            text-align: center;
        }
        
        h1 {
            font-size: 6rem;
            margin: 0;
            color: #e53e3e; /* Changed to a red hue for error/forbidden */
        }

        h2 {
            margin-top: 0;
            font-weight: 400;
        }

        p {
            margin-bottom: 2rem;
            color: #666;
        }

        .btn {
            text-decoration: none;
            background-color: #007bff;
            color: white;
            padding: 12px 24px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <h1>403</h1>
    <h2>Access Denied.</h2>
    <p>You do not have the necessary permissions to view this page. If you believe this is an error, please contact your Super Admin.</p>
    <a href="saved_reports.php" class="btn">Go to Saved Reports</a>
</body>
</html>