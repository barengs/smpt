<?php

use Dedoc\Scramble\Http\Middleware\RestrictedDocsAccess;

return [
    /*
     * Your API path. By default, all routes starting with this path will be added to the docs.
     * If you need to change this behavior, you can add your custom routes resolver using `Scramble::routes()`.
     */
    'api_path' => 'api',

    /*
     * Your API domain. By default, app domain is used. This is also a part of the default API routes
     * matcher, so when implementing your own, make sure you use this config if needed.
     */
    'api_domain' => null,

    /*
     * The path where your OpenAPI specification will be exported.
     */
    'export_path' => 'api.json',

    'info' => [
        /*
         * API version.
         */
        'version' => env('API_VERSION', '1.0.0'),

        /*
         * Description rendered on the home page of the API documentation (`/docs/api`).
         */
        'description' => ''
            . '# Education Management System API\n\n'
            . 'Welcome to the comprehensive API documentation for the Education Management System (SMP). '
            . 'This API provides complete access to manage students, parents, staff, classes, schedules, attendance, internships, and more.\n\n'
            . '## Authentication\n\n'
            . 'This API uses **JWT (JSON Web Token)** authentication. To access protected endpoints:\n\n'
            . '1. Login via `POST /api/auth/login` with your credentials\n'
            . '2. Receive a JWT token in the response\n'
            . '3. Include the token in all subsequent requests:\n'
            . '   ```\n'
            . '   Authorization: Bearer {your-jwt-token}\n'
            . '   ```\n\n'
            . '## Main Features\n\n'
            . '### 📚 Student Management\n'
            . '- Create, read, update, delete student records\n'
            . '- **Batch import** from Excel/CSV files\n'
            . '- Photo upload with automatic compression\n'
            . '- Room assignment and hostel management\n'
            . '- Academic tracking and class assignments\n\n'
            . '### 👥 Parent Management\n'
            . '- Manage parent/guardian profiles\n'
            . '- **Batch import** with automatic user account creation\n'
            . '- Link parents to students\n'
            . '- Contact information management\n\n'
            . '### 👨‍🏫 Staff Management\n'
            . '- Staff profiles and role assignments\n'
            . '- Position and organization tracking\n'
            . '- Academic qualifications\n'
            . '- Photo management\n\n'
            . '### 📅 Academic Operations\n'
            . '- Class schedules and timetables\n'
            . '- Attendance/presence tracking\n'
            . '- Student leave management with multi-level approval\n'
            . '- Academic year management\n\n'
            . '### 🏢 Internship Management\n'
            . '- Internship placement tracking\n'
            . '- Supervisor assignments\n'
            . '- Progress monitoring\n\n'
            . '### 📊 Reports & Analytics\n'
            . '- Dashboard statistics\n'
            . '- Student analytics by period\n'
            . '- Attendance reports\n'
            . '- Leave system reports\n\n'
            . '## Import Features\n\n'
            . '### Student Import\n'
            . 'Upload Excel/CSV files to batch import student data. The system automatically:\n'
            . '- Validates data and checks for duplicates (NIS)\n'
            . '- Handles numeric fields (NIK, KK, phone) correctly\n'
            . '- Records the staff member who performed the import\n'
            . '- Provides detailed error reports for failed rows\n\n'
            . '**Endpoints:**\n'
            . '- `GET /api/main/student/import/template` - Download Excel template\n'
            . '- `POST /api/main/student/import` - Upload and import student data\n\n'
            . '### Parent Import\n'
            . 'Upload Excel/CSV files to batch import parent data with automatic user creation:\n'
            . '- Creates user account for each parent (NIK as email)\n'
            . '- Sets default password: "password"\n'
            . '- Assigns "user" role automatically\n'
            . '- Validates NIK and KK uniqueness\n'
            . '- Transaction safety (rollback on error)\n\n'
            . '**Endpoints:**\n'
            . '- `GET /api/main/parent/import/template` - Download Excel template\n'
            . '- `POST /api/main/parent/import` - Upload and import parent data\n\n'
            . '## Response Format\n\n'
            . 'All API responses follow a consistent JSON structure:\n\n'
            . '**Success Response:**\n'
            . '```json\n'
            . '{\n'
            . '  "success": true,\n'
            . '  "message": "Operation successful",\n'
            . '  "data": { ... }\n'
            . '}\n'
            . '```\n\n'
            . '**Error Response:**\n'
            . '```json\n'
            . '{\n'
            . '  "success": false,\n'
            . '  "message": "Error description",\n'
            . '  "errors": { ... }\n'
            . '}\n'
            . '```\n\n'
            . '## Rate Limiting\n\n'
            . 'API requests are rate-limited to prevent abuse. Current limits:\n'
            . '- 60 requests per minute for authenticated users\n'
            . '- 10 requests per minute for unauthenticated users\n\n'
            . '## File Uploads\n\n'
            . 'When uploading files (photos, Excel imports):\n'
            . '- Use `multipart/form-data` content type\n'
            . '- Maximum file size: 10MB for imports, 2MB for photos\n'
            . '- Allowed formats: .xlsx, .xls, .csv for imports; .jpg, .png for photos\n\n'
            . '## Error Codes\n\n'
            . '- `200` - Success\n'
            . '- `201` - Created\n'
            . '- `400` - Bad Request\n'
            . '- `401` - Unauthorized (missing or invalid token)\n'
            . '- `403` - Forbidden (insufficient permissions)\n'
            . '- `404` - Not Found\n'
            . '- `422` - Validation Error\n'
            . '- `500` - Server Error\n\n'
            . '## Support & Documentation\n\n'
            . 'For complete documentation and examples, refer to:\n'
            . '- Import Features: `docs/IMPORT_FEATURES_DOCUMENTATION.md`\n'
            . '- Project README: `README.md`\n\n'
            . '## Getting Started\n\n'
            . '1. **Authenticate:** Login to get your JWT token\n'
            . '2. **Explore Endpoints:** Browse the API documentation below\n'
            . '3. **Try It Out:** Use the interactive "Try It" feature\n'
            . '4. **Check Examples:** Review request/response samples\n\n'
            . '---\n\n'
            . '**Note:** All timestamps are in UTC. Dates follow ISO 8601 format (YYYY-MM-DD).',
    ],

    /*
     * Customize Stoplight Elements UI
     */
    'ui' => [
        /*
         * Define the title of the documentation's website. App name is used when this config is `null`.
         */
        'title' => 'SMP Education Management System - API Documentation',

        /*
         * Define the theme of the documentation. Available options are `light`, `dark`, and `system`.
         */
        'theme' => 'light',

        /*
         * Hide the `Try It` feature. Enabled by default.
         */
        'hide_try_it' => false,

        /*
         * Hide the schemas in the Table of Contents. Enabled by default.
         */
        'hide_schemas' => false,

        /*
         * URL to an image that displays as a small square logo next to the title, above the table of contents.
         */
        'logo' => '',

        /*
         * Use to fetch the credential policy for the Try It feature. Options are: omit, include (default), and same-origin
         */
        'try_it_credentials_policy' => 'include',

        /*
         * There are three layouts for Elements:
         * - sidebar - (Elements default) Three-column design with a sidebar that can be resized.
         * - responsive - Like sidebar, except at small screen sizes it collapses the sidebar into a drawer that can be toggled open.
         * - stacked - Everything in a single column, making integrations with existing websites that have their own sidebar or other columns already.
         */
        'layout' => 'responsive',
    ],

    /*
     * The list of servers of the API. By default, when `null`, server URL will be created from
     * `scramble.api_path` and `scramble.api_domain` config variables. When providing an array, you
     * will need to specify the local server URL manually (if needed).
     *
     * Example of non-default config (final URLs are generated using Laravel `url` helper):
     *
     * ```php
     * 'servers' => [
     *     'Live' => 'api',
     *     'Prod' => 'https://scramble.dedoc.co/api',
     * ],
     * ```
     */
    'servers' => null,

    /**
     * Determines how Scramble stores the descriptions of enum cases.
     * Available options:
     * - 'description' – Case descriptions are stored as the enum schema's description using table formatting.
     * - 'extension' – Case descriptions are stored in the `x-enumDescriptions` enum schema extension.
     *
     *    @see https://redocly.com/docs-legacy/api-reference-docs/specification-extensions/x-enum-descriptions
     * - false - Case descriptions are ignored.
     */
    'enum_cases_description_strategy' => 'description',

    'middleware' => [
        'web',
        RestrictedDocsAccess::class,
    ],

    'extensions' => [],
];
