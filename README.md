<p align="center">
    <img src="https://raw.githubusercontent.com/ceytek-labs/google-services-lite/refs/heads/1.x/art/banner.jpg" width="400" alt="Google Services Lite">
    <p align="center">
        <a href="https://packagist.org/packages/ceytek-labs/google-services-lite"><img alt="Total Downloads" src="https://img.shields.io/packagist/dt/ceytek-labs/google-services-lite"></a>
        <a href="https://packagist.org/packages/ceytek-labs/google-services-lite"><img alt="Latest Version" src="https://img.shields.io/packagist/v/ceytek-labs/google-services-lite"></a>
        <a href="https://packagist.org/packages/ceytek-labs/google-services-lite"><img alt="Size" src="https://img.shields.io/github/repo-size/ceytek-labs/google-services-lite"></a>
        <a href="https://packagist.org/packages/ceytek-labs/google-services-lite"><img alt="License" src="https://img.shields.io/packagist/l/ceytek-labs/google-services-lite"></a>
    </p>
</p>

------

# Google Services Lite - Google API for PHP

**Google Services Lite** is a lightweight and extendable library designed to help you manage data easily using the Google Sheets API in your PHP projects. Currently, it only supports the Google Sheets API, but future versions will include support for YouTube and other Google services.

> **Disclaimer:** This package is not an official product of Google. The developers accept no responsibility for any issues, discrepancies, or damages that may arise from its use.

## Requirements

- PHP 7.0 or higher

## Installation

You can add this package to your projects via Composer:

```bash
composer require ceytek-labs/google-services-lite
```

## Services
- [Google Sheets](#google-sheets)
    - [How to use](#google-sheets-how-to-use)
    - [Update](#google-sheets-update)
    - [Update in chunks](#google-sheets-update-in-chunks)
    - [Batch update](#google-sheets-batch-update)
    - [Batch update in chunks](#google-sheets-batch-update-in-chunks)

## Google Sheets

This package offers a streamlined integration with the Google Sheets API for PHP, focusing on effortless data updates within Google Sheets. Perfect for automating and enhancing your application’s data management.

### Example Usage

The following example demonstrates how to update data in a **Google Sheets** document:

**[⬆ Back to services](#services)**

#### Google Sheets: How to use

To use the Google Sheets API, you need to download the required authentication file and add it to your project’s root directory. Follow the steps below to set this up:

- Create a project in the Google Cloud Console.
- Enable the Google Sheets API for your project.
- Create authentication credentials for a "Service Account."
- Download the authentication JSON file and save it in your project’s root directory as `credentials.json`.
- Share the email address from the service account with the Google Sheets document you want to modify, giving it edit access.
- Don’t forget to note the sheet’s ID and the name of the tab you want to modify:
    - `https://docs.google.com/spreadsheets/d/<id>`

**[⬆ Back to services](#services)**

#### Google Sheets: Update

Updates the specified Google Sheets tab with the provided data, replacing all existing values. It returns the number of cells that were updated.

```php
use CeytekLabs\GoogleServicesLite\GoogleSheets;

$result = GoogleSheets::make('SPREADSHEET_ID')    // Set the ID of the Google Sheets document
    ->setCredentials(__DIR__.'/credentials.json') // Set the authentication file
    ->update('Sheet1', [                          // Set the name of the tab where data will be updated
        ["Data 1", "Data 2", "Data 3"],           // Add the data to be updated
        ["Data 4", "Data 5", "Data 6"],
        ["Data 7", "Data 8", "Data 9"],
    ]);

echo 'Number of updated cells: ' . $result['updated_cells_count'];
```

**[⬆ Back to services](#services)**

#### Google Sheets: Update in chunks

Similar to `update`, but handles large datasets by splitting the data into smaller chunks to prevent exceeding API limits. It returns the total number of updated cells.

```php
use CeytekLabs\GoogleServicesLite\GoogleSheets;

$result = GoogleSheets::make('SPREADSHEET_ID')    // Set the ID of the Google Sheets document
    ->setCredentials(__DIR__.'/credentials.json') // Set the authentication file
    ->updateInChunks('Sheet1', [                  // Update data in smaller chunks
        ["Data 1", "Data 2", "Data 3"],           // Add the data to be updated
        ["Data 4", "Data 5", "Data 6"],
        ["Data 7", "Data 8", "Data 9"],           // For large datasets, the data will be split into chunks
    ], 50);                                       // Define the chunk size (e.g., 50 rows)

echo 'Number of updated cells: ' . $result['updated_cells_count'];
```

**[⬆ Back to services](#services)**

#### Google Sheets: Batch update

Updates specific cells in a Google Sheets tab using a batch request, allowing more granular control over each cell. It returns the status of the update (success or failure).

```php
use CeytekLabs\GoogleServicesLite\GoogleSheets;

$result = GoogleSheets::make('SPREADSHEET_ID')    // Set the ID of the Google Sheets document
    ->setCredentials(__DIR__.'/credentials.json') // Set the authentication file
    ->batchUpdate('Sheet1', [                     // Set the name of the tab where data will be updated
        ["Data 1", "Data 2", "Data 3"],           // Add the data to be updated
        ["Data 4", "Data 5", "Data 6"],
        ["Data 7", "Data 8", "Data 9"],
    ]);

echo 'Batch update status: ' . ($result['status'] ? 'Success' : 'Failed');
```

**[⬆ Back to services](#services)**

#### Google Sheets: Batch update in chunks

Similar to `batchUpdate`, but handles large datasets by splitting the data into smaller chunks to prevent exceeding API limits. It returns the status of the update (success or failure).

```php
use CeytekLabs\GoogleServicesLite\GoogleSheets;

$result = GoogleSheets::make('SPREADSHEET_ID')    // Set the ID of the Google Sheets document
    ->setCredentials(__DIR__.'/credentials.json') // Set the authentication file
    ->batchUpdateInChunks('Sheet1', [             // Update data in smaller chunks
        ["Data 1", "Data 2", "Data 3"],           // Add the data to be updated
        ["Data 4", "Data 5", "Data 6"],
        ["Data 7", "Data 8", "Data 9"],           // For large datasets, the data will be split into chunks
    ], 750);                                      // Define the chunk size (e.g., 750 rows)

echo 'Number of updated cells: ' . $result['updated_cells_count'];
```

## Contributing

Feel free to submit a **pull request** or report an issue. Any contributions and feedback are highly appreciated!

## License

This project is licensed under the MIT License.