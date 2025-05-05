# Certificate Generation System

This system generates certificates for participants, volunteers, and crew members of MaJIStic 2K25.

## Setup Instructions

### Required Directory Structure
- `/certificate` - Main certificate folder
  - `/assets` - For fonts and preview images
  - `/templates` - For PDF certificate templates
  - `/temp` - For temporary generated certificates
  - `/vendor` - For PDF libraries

### Required Libraries
For optimal PDF certificate generation, install these libraries:

1. **Using Composer (recommended):**
   ```
   composer require setasign/fpdf
   composer require setasign/fpdi
   ```

2. **Manual Installation:**
   - Download FPDF from: https://fpdf.org/
   - Download FPDI from: https://www.setasign.com/products/fpdi/downloads/
   - Extract both into the `/vendor` directory

### Required Font Files
Place these font files in the `/assets` directory:
- `arial_bold.ttf`

### Required Template Files
Place these template files in the `/templates` directory:
- `participant_template.pdf`
- `volunteer_template.pdf`
- `crew_template.pdf`

### Fallback System
If PDF libraries are unavailable, the system will automatically generate simpler certificates using PHP's GD library.

## Troubleshooting

### Common Errors and Solutions

1. **"Error generating certificate. Please try again later."**
   - Check that all template files exist in the `/templates` directory
   - Ensure the PDF libraries are correctly installed
   - Make sure the `/temp` directory exists and is writable
   - Check PHP error logs for detailed error messages

2. **"Required PDF libraries are missing"**
   - Install the required libraries as described in the setup section
   - Verify the paths to the libraries are correct

3. **"Certificate Generation Debug Mode"**
   - Enable `$debug = true;` in `generate_certificate.php` for detailed console errors
   - Check the browser console (F12) for error messages when in debug mode

4. **HTTP 500 Errors on Hostinger or Production Server**
   - Access `hostinger_debug.php` to diagnose server configuration issues
   - Check if the `/temp` directory has proper write permissions (755)
   - Verify memory limits are sufficient (at least 128M recommended)
   - Check if required extensions (GD, ZIP) are enabled
   - Review error logs at `error_log.txt` within the certificate directory

### Checking File Permissions
Make sure these directories have correct permissions:
- `/templates` directory: readable (644)
- `/temp` directory: readable and writable (755)
- PDF libraries in `/vendor`: readable (644)

### Template Naming Convention
Template files must be named exactly:
- `participant_template.pdf`
- `volunteer_template.pdf`
- `crew_template.pdf`

### Hostinger/Production-Specific Tips

If experiencing issues in hosted environments but working fine locally:

1. **Permission Issues**: Set directory permissions:
   ```
   chmod 755 temp/
   chmod 644 templates/*
   ```

2. **Memory Limits**: Add to .htaccess:
   ```
   php_value memory_limit 256M
   php_value max_execution_time 300
   ```

3. **Double Certificate Issues**:
   - If a student has multiple roles, ensure tables have proper structure
   - Check the database tables for duplicate entries
   - Use the verification debug URL parameter: `verify.php?id=XX&debug=1`
