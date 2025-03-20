# maJIStic 2k25 Registration System

## Recent Updates

### Check Status Page Enhancement
- Fixed CSS file name typo from "check_ststus.css" to "check_status.css"
- Updated check_status.php to fetch details from MongoDB database
- Added department coordinator contact information for unpaid registrations
- Improved timeline visualization with ticket generation status
- Added Day 1 and Day 2 check-in status in the timeline
- Enhanced user experience with visual indicators for registration progress

### API Endpoints
- `get_coordinators.php`: Retrieves all department coordinators or filters by department
- `get_coordinator_by_department.php`: Gets a specific coordinator by department name

## Database Collections
- `registrations`: For in-house student registrations
- `alumni_registrations`: For alumni registrations
- `department_coordinators`: Contains coordinator contact information

## Timeline Features
The registration timeline now shows:
1. Registration completion
2. Payment status (with coordinator contact details if unpaid)
3. Ticket generation status
4. Day 1 check-in status
5. Day 2 check-in status
6. Event completion

## How to Use
1. Students enter their JIS ID and full name to check their registration status
2. The system fetches their details from MongoDB
3. If payment is incomplete, department coordinator's contact is displayed
4. The timeline shows their current progress in the registration process
5. Check-in status for both event days is displayed when available

