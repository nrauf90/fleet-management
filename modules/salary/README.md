# Salary Management Module

A comprehensive salary management module for Perfex CRM that allows administrators to manage staff salaries and advance salary requests.

## Features

### Staff Salary Management

- Set and manage initial salary for staff members
- Track current salary and effective dates
- Maintain salary history for each staff member
- Support for advance salary amounts

### Advance Salary Management

- Create advance salary requests
- Approval workflow for advance requests
- Track request status (pending, approved, rejected, paid)
- Automatic calculation of maximum advance based on salary percentage
- Reason tracking for advance requests

### Dashboard

- Overview of salary statistics
- Recent salary updates
- Recent advance requests
- Quick access to main functions

### Reports

- Monthly salary reports
- Advance salary reports
- Filter by staff member and month
- Export functionality (PDF, Excel, CSV)
- Print-friendly reports

### Settings

- Configure maximum advance percentage
- Set salary currency
- Configure decimal places
- Enable/disable approval requirements

## Installation

1. Copy the `salary` folder to your `modules` directory
2. Go to Setup > Modules in your Perfex CRM admin panel
3. Find "Salary Management" and click "Activate"
4. The module will automatically create the necessary database tables

## Database Tables

The module creates the following tables:

### `tblstaff_salary`

- Stores staff salary information
- Tracks initial salary, advance salary, and effective dates
- Maintains salary history

### `tblstaff_advance_salary`

- Stores advance salary requests
- Tracks request status and approval workflow
- Links to staff and approver information

### `tblsalary_settings`

- Stores module configuration settings
- Includes advance percentage limits and currency settings

## Permissions

The module includes the following permissions:

- **View**: View salary information and reports
- **Create**: Add new salary records and advance requests
- **Edit**: Modify existing salary records and advance requests
- **Delete**: Remove advance salary requests

## Usage

### Managing Staff Salary

1. Navigate to Salary > Staff Salary
2. Click "Add New Staff Salary" or edit existing records
3. Select staff member and enter salary details
4. Set effective date and advance amount if needed

### Managing Advance Requests

1. Navigate to Salary > Advance Salary
2. Click "Add New Advance Salary Request"
3. Select staff member and enter amount
4. Provide reason for the request
5. Approve or reject requests as needed

### Viewing Reports

1. Navigate to Salary > Reports
2. Select month and staff member (optional)
3. View salary and advance reports
4. Export or print reports as needed

### Configuring Settings

1. Navigate to Salary > Settings (Admin only)
2. Set maximum advance percentage
3. Configure currency and decimal places
4. Enable/disable approval requirements

## API Endpoints

The module provides the following API endpoints:

- `GET /api/salary/staff` - Get staff salary data
- `GET /api/salary/advance` - Get advance salary data
- `POST /api/salary/staff` - Create staff salary record
- `POST /api/salary/advance` - Create advance salary request
- `PUT /api/salary/advance/{id}` - Update advance salary request

## Customization

### Adding Custom Fields

You can extend the salary tables to include additional fields by modifying the installation file and adding the necessary database columns.

### Custom Validation Rules

Modify the JavaScript validation functions in `assets/js/salary.js` to add custom validation rules.

### Custom Reports

Extend the reports functionality by adding new report types in the controller and views.

## Support

For support and questions, please refer to the Perfex CRM documentation or contact the module developer.

## Version History

- **v1.0.0** - Initial release with basic salary and advance management features

## License

This module is licensed under the same license as Perfex CRM.
