# maJIStic 2K25 Check-in Integration

This provides a simple webhook endpoint to update check-in statuses in the maJIStic 2K25 MySQL database from your external MongoDB portal.

## Overview

The webhook is designed to handle check-in status updates in real time from an external system.

## Implementation Guidelines

### 1. Upload & Configure

1. **Upload Files**: Ensure that all files in this directory are uploaded to your web server:
   - webhook.php - Main webhook endpoint
   - update_checkin_external.php - API for querying student status
   - test_webhook.php - Testing utility
   - index.php - Documentation page

2. **Security**: The API key in the PHP files is already set to:
   ```
   d2bf07f0b99d6e139c36d3ee09beb762fbfb516dacc0193e215299583e96e30f
   ```

3. **Create Log Directory**: Make sure the logs directory is writable:
   ```
   /integrations/external/logs/
   ```

### 2. Testing Your Integration

Before implementing in your MongoDB application, test if your configuration works:

1. Visit the test utility:
   ```
   https://your-domain.com/integrations/external/test_webhook.php
   ```

2. Enter a valid JIS ID from your database (that has payment_status = 'Paid')

3. Click "Run Tests" to verify if:
   - The student/alumni can be found in the database
   - Check-in can be updated
   - Changes are properly recorded

### 3. Webhook Implementation

Implement this code in your React/Node.js + MongoDB application to automatically update both systems:

```javascript
/**
 * Function to update check-in status in both MongoDB and MySQL
 * @param {string} jisId - Student's JIS ID
 * @param {number} day - Day number (1 or 2)
 * @param {boolean} isCheckedIn - Check-in status
 */
async function updateCheckInStatus(jisId, day, isCheckedIn) {
  try {
    // 1. First update MongoDB (your primary database)
    await updateMongoDBCheckIn(jisId, day, isCheckedIn);
    
    // 2. Then send webhook to update MySQL
    const response = await fetch('https://your-domain.com/integrations/external/webhook.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-API-KEY': 'd2bf07f0b99d6e139c36d3ee09beb762fbfb516dacc0193e215299583e96e30f'
      },
      body: JSON.stringify({
        jis_id: jisId,
        day: day,
        status: isCheckedIn,
        timestamp: new Date().toISOString()
      })
    });
    
    const result = await response.json();
    console.log('MySQL update result:', result);
    
    if (!result.success) {
      console.error('Failed to update MySQL:', result.message);
    }
    
    return result;
  } catch (error) {
    console.error('Check-in synchronization failed:', error);
    // Implement retry logic if needed
    return { success: false, error: error.message };
  }
}
```

## Troubleshooting

If you're experiencing issues with the integration:

1. **Check logs**:
   - `/integrations/external/logs/webhook_YYYY-MM-DD.log` - API call logs
   - `/integrations/external/logs/raw_input_YYYY-MM-DD.log` - Raw request data

2. **Common issues**:
   - **301/302 Redirect errors**: Make sure you're using the correct URL path
   - **JIS ID not found**: Verify the JIS ID exists and has payment_status = 'Paid'
   - **Format issues**: Ensure JIS ID format matches exactly what's in your database
   - **HTTPS issues**: If using HTTPS, ensure your certificate is valid

3. **Testing specific endpoints**:
   - To check student status: 
     ```
     GET https://your-domain.com/integrations/external/update_checkin_external.php?jis_id=JIS/2024/1234
     ```
   - Headers: `X-API-KEY: d2bf07f0b99d6e139c36d3ee09beb762fbfb516dacc0193e215299583e96e30f`

4. **Server requirements**:
   - PHP 7.0 or higher
   - PDO and MySQL extensions enabled
   - Write permissions for log directory

## API Reference

Find the full API documentation at:
```
https://your-domain.com/integrations/external/
```
