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
        'description' => <<<'MARKDOWN'
# Education Management System API

Welcome to the comprehensive API documentation for the **Education Management System (SMP)**. This API provides complete access to manage students, parents, staff, classes, schedules, attendance, internships, and more.

## Authentication

This API uses **JWT (JSON Web Token)** authentication. To access protected endpoints:

1. Login via `POST /api/auth/login` with your credentials
2. Receive a JWT token in the response
3. Include the token in all subsequent requests with header: `Authorization: Bearer {your-jwt-token}`

## Main Features

| Feature | Description |
|---------|-------------|
| **Student Management** | CRUD operations, batch import, photo upload, room assignment |
| **Parent Management** | Parent profiles, batch import with auto user creation |
| **Staff Management** | Staff profiles, role assignments, position tracking |
| **Academic Operations** | Class schedules, attendance tracking, leave management |
| **Internship Management** | Placement tracking, supervisor assignments |
| **Reports & Analytics** | Dashboard statistics, attendance reports |

## Import Features

### Student Import
- **Template:** `GET /api/main/student/import/template`
- **Import:** `POST /api/main/student/import`
- Validates data, checks for duplicates (NIS), handles numeric fields correctly

### Parent Import
- **Template:** `GET /api/main/parent/import/template`
- **Import:** `POST /api/main/parent/import`
- Creates user account (NIK as email, default password: "password")

## Response Format

All API responses follow a consistent JSON structure with `success`, `message`, and `data` fields.

## Error Codes

| Code | Description |
|------|-------------|
| 200 | Success |
| 201 | Created |
| 400 | Bad Request |
| 401 | Unauthorized |
| 403 | Forbidden |
| 404 | Not Found |
| 422 | Validation Error |
| 500 | Server Error |

## Getting Started

1. **Authenticate:** Login to get your JWT token
2. **Explore Endpoints:** Browse the API documentation below
3. **Try It Out:** Use the interactive "Try It" feature

---

**Note:** All timestamps are in UTC. Dates follow ISO 8601 format (YYYY-MM-DD).
MARKDOWN,
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
