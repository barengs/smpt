# Registration Transaction Method Fix

## Issue

The `createRequestTransaction` method in the RegistrationController was not accessible from outside because:

1. The route was incorrectly registered under `TransactionController` instead of `RegistrationController`
2. There was a minor issue with the validation rules (unused `amount` field)

## Solution

1. Fixed the route registration in `routes/api.php`:

    - Changed from: `Route::post('registration/transaction', [TransactionController::class, 'createRequestTransaction'])`
    - Changed to: `Route::post('registration/transaction', [RegistrationController::class, 'createRequestTransaction'])`

2. Removed the unused `amount` validation rule from the method

3. Fixed the Auth facade usage to use the fully qualified class name to avoid linter errors

## Method Functionality

The `createRequestTransaction` method now:

1. Validates required input parameters
2. Creates a student record from registration data
3. Creates an account for the student
4. Creates a transaction record
5. Creates transaction ledger entries
6. Updates the registration with payment status

## Route

The method is now accessible via:
`POST /api/main/registration/transaction`

## Authentication

The method uses `Auth::id()` to automatically set the user_id field when creating the student record.
