<?php
// Restrict direct access with your secure API key
if (!isset($_SERVER['HTTP_X_API_KEY']) || $_SERVER['HTTP_X_API_KEY'] !== 'd2bf07f0b99d6e139c36d3ee09beb762fbfb516dacc0193e215299583e96e30f') {
    header('HTTP/1.0 403 Forbidden');
    exit('Access denied');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>maJIStic External API Integration</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: #f9f9f9;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1, h2, h3 {
            color: #2c3e50;
        }
        code {
            background: #f4f4f4;
            padding: 2px 5px;
            border: 1px solid #ddd;
            border-radius: 3px;
            font-family: monospace;
        }
        pre {
            background: #f4f4f4;
            padding: 10px;
            border-radius: 5px;
            overflow-x: auto;
        }
        .endpoint {
            margin-bottom: 30px;
            border-left: 4px solid #3498db;
            padding-left: 15px;
        }
        .method {
            font-weight: bold;
            background: #3498db;
            color: white;
            padding: 3px 8px;
            border-radius: 3px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>maJIStic 2K25 External API Documentation</h1>
        <p>Welcome to the external integration API for maJIStic 2K25. This API allows external applications to update check-in statuses in the main MySQL database.</p>
        
        <h2>Authentication</h2>
        <p>All API requests must include an <code>X-API-KEY</code> header with your provided API key.</p>
        
        <h2>Endpoints</h2>
        
        <div class="endpoint">
            <h3><span class="method">GET</span> /update_checkin_external.php?jis_id={jis_id}</h3>
            <p>Get check-in status for a student by JIS ID</p>
            
            <h4>Parameters:</h4>
            <table>
                <tr>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Description</th>
                </tr>
                <tr>
                    <td>jis_id</td>
                    <td>string</td>
                    <td>The JIS ID of the student</td>
                </tr>
            </table>
            
            <h4>Example Response:</h4>
            <pre>
{
    "success": true,
    "jis_id": "12345678",
    "name": "John Doe",
    "department": "CSE",
    "type": "student",
    "is_paid": true,
    "ticket_generated": true,
    "check_in_status": {
        "day1": {
            "checked_in": true,
            "timestamp": "2023-04-15 10:30:45"
        },
        "day2": {
            "checked_in": false,
            "timestamp": null
        }
    }
}
            </pre>
        </div>
        
        <div class="endpoint">
            <h3><span class="method">POST</span> /update_checkin_external.php</h3>
            <p>Update check-in status for a student</p>
            
            <h4>Request Body:</h4>
            <pre>
{
    "jis_id": "12345678",
    "checkin_day": 1,
    "checkin_status": true,
    "timestamp": "2023-04-15T10:30:45Z",
    "updated_by": "external-portal"
}
            </pre>
            
            <h4>Fields:</h4>
            <table>
                <tr>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Description</th>
                </tr>
                <tr>
                    <td>jis_id</td>
                    <td>string</td>
                    <td>The JIS ID of the student</td>
                </tr>
                <tr>
                    <td>checkin_day</td>
                    <td>integer</td>
                    <td>Day number (1 or 2)</td>
                </tr>
                <tr>
                    <td>checkin_status</td>
                    <td>boolean</td>
                    <td>True for checked-in, false for not checked in</td>
                </tr>
                <tr>
                    <td>timestamp</td>
                    <td>string</td>
                    <td>ISO formatted date-time of the check-in</td>
                </tr>
                <tr>
                    <td>updated_by</td>
                    <td>string</td>
                    <td>Identifier of the system or user making the update</td>
                </tr>
            </table>
            
            <h4>Example Response:</h4>
            <pre>
{
    "success": true,
    "message": "Check-in updated successfully",
    "details": {
        "student_id": 42,
        "name": "John Doe",
        "department": "CSE",
        "table": "registrations",
        "day": 1,
        "status": "Yes",
        "timestamp": "2023-04-15 10:30:45"
    }
}
            </pre>
        </div>
        
        <h2>Automatic Integration with Webhooks</h2>
        <p>For real-time synchronization between your external MongoDB portal and this MySQL system, use the webhook endpoint:</p>
        
        <div class="endpoint">
            <h3><span class="method">POST</span> /webhook.php</h3>
            <p>Process automatic events from the external portal</p>
            
            <h4>Request Body (Check-in Event):</h4>
            <pre>
{
    "event_type": "check_in",
    "data": {
        "jis_id": "12345678",
        "day": 1,
        "status": true,
        "timestamp": "2023-04-15T10:30:45Z",
        "updated_by": "external-portal"
    }
}
            </pre>
            
            <h4>Request Body (Ticket Generation Event):</h4>
            <pre>
{
    "event_type": "ticket_generation",
    "data": {
        "jis_id": "12345678",
        "updated_by": "external-portal"
    }
}
            </pre>
            
            <h4>Example Response:</h4>
            <pre>
{
    "success": true,
    "message": "Check-in updated successfully",
    "details": {
        "student_id": 42,
        "name": "John Doe",
        "department": "CSE",
        "table": "registrations",
        "day": 1,
        "status": "Yes",
        "timestamp": "2023-04-15 10:30:45"
    }
}
            </pre>
        </div>
        
        <h2>Error Responses</h2>
        <p>Error responses will always include a <code>success: false</code> field and a <code>message</code> explaining the error.</p>
        
        <pre>
{
    "success": false,
    "message": "No paid registration found with JIS ID: 12345678"
}
        </pre>
    </div>
</body>
</html>
