<?php

namespace CeytekLabs\GoogleServicesLite;

use Google\Service\Sheets;
use Google\Client;
use Google\Service\Sheets\BatchUpdateSpreadsheetRequest;
use Google\Service\Sheets\CellData;
use Google\Service\Sheets\ClearValuesRequest;
use Google\Service\Sheets\Request;
use Google\Service\Sheets\RowData;
use Google\Service\Sheets\ValueRange;

class GoogleSheets
{
    private $id = null;

    private $client;

    public static function make(string $id = null): self
    {
        if (is_null($id)) {
            throw new \Exception('Spreadsheet id must be filled');
        }

        $instance = new self;

        $instance->id = $id;

        $instance->client = new Client();
        $instance->client->setApplicationName("Google Sheets API PHP Quickstart");
        $instance->client->addScope(Sheets::DRIVE);
        $instance->client->setAccessType('offline');

        return $instance;
    }

    public function setApplicationName(string $applicationName): self
    {
        $this->client->setApplicationName($applicationName);

        return $this;
    }

    public function setScope(string $scope): self
    {
        $this->client->addScope($scope);

        return $this;
    }

    public function setAccessType(string $accessType): self
    {
        $this->client->setAccessType($accessType);

        return $this;
    }

    public function setCredentials(string $credentialsPath): self
    {
        $this->client->setAuthConfig($credentialsPath);

        return $this;
    }

    public function update(string $page = null, array $values = []): array
    {
        if (is_null($page)) {
            throw new \Exception('Spreadsheet page must be filled');
        }

        if (empty($values)) {
            throw new \Exception('Spreadsheet values must be filled');
        }

        $service = new Sheets($this->client);

        $service->spreadsheets_values->clear($this->id, $page, new ClearValuesRequest());

        $result = $service->spreadsheets_values->update($this->id, $page, new ValueRange(['values' => $values]), ['valueInputOption' => 'RAW']);

        return [
            'updated_cells_count' => $result->getUpdatedCells(),
        ];
    }

    public function updateInChunks(string $page = null, array $values = [], int $chunkSize = 10): array
    {
        if (is_null($page)) {
            throw new \Exception('Spreadsheet page must be filled');
        }

        if (empty($values)) {
            throw new \Exception('Spreadsheet values must be filled');
        }

        $service = new Sheets($this->client);

        $service->spreadsheets_values->clear($this->id, $page, new ClearValuesRequest());

        $chunks = array_chunk($values, $chunkSize);

        $startRow = 1;
        
        $updatedCellsCount = 0;

        foreach ($chunks as $chunk) {
            $endRow = $startRow + count($chunk) - 1;

            $range = $page . '!A' . $startRow;

            $valueRange = new ValueRange([
                'range' => $range,
                'values' => $chunk,
            ]);

            $result = $service->spreadsheets_values->update(
                $this->id,
                $range,
                $valueRange,
                ['valueInputOption' => 'RAW']
            );

            $updatedCellsCount += $result->getUpdatedCells();

            $startRow = $endRow + 1;
        }

        return [
            'updated_cells_count' => $updatedCellsCount,
        ];
    }

    public function batchUpdate(string $page, array $values): array
    {
        if (is_null($page)) {
            throw new \Exception('Spreadsheet page must be filled');
        }
    
        if (empty($values)) {
            throw new \Exception('Spreadsheet values must be filled');
        }
    
        $service = new Sheets($this->client);

        $service->spreadsheets_values->clear($this->id, $page, new ClearValuesRequest());
    
        $spreadsheet = $service->spreadsheets->get($this->id);

        $sheets = $spreadsheet->getSheets();
    
        $sheetId = null;

        $rowCount = null;

        $columnCount = null;

        foreach ($sheets as $sheet) {
            if ($sheet->getProperties()->getTitle() === $page) {
                $sheetId = $sheet->getProperties()->getSheetId();

                $rowCount = $sheet->getProperties()->getGridProperties()->getRowCount();

                $columnCount = $sheet->getProperties()->getGridProperties()->getColumnCount();

                break;
            }
        }
    
        if (is_null($sheetId)) {
            throw new \Exception('Sheet not found: ' . $page);
        }

        $valuesRowCount = count($values);

        if ($valuesRowCount > $rowCount) {
            $newRows = $valuesRowCount - $rowCount;

            $requests[] = new Request([
                'appendDimension' => [
                    'sheetId' => $sheetId,
                    'dimension' => 'ROWS',
                    'length' => $newRows
                ]
            ]);

            $batchUpdateRequest = new BatchUpdateSpreadsheetRequest([
                'requests' => $requests
            ]);

            $service->spreadsheets->batchUpdate($this->id, $batchUpdateRequest);
        }

        $maxColumnCount = 0;

        foreach ($values as $row) {
            $currentColumnCount = count($row);

            if ($currentColumnCount > $maxColumnCount) {
                $maxColumnCount = $currentColumnCount;
            }
        }

        if ($maxColumnCount > $columnCount) {
            $newColumns = $maxColumnCount - $columnCount;

            $requests[] = new Request([
                'appendDimension' => [
                    'sheetId' => $sheetId,
                    'dimension' => 'COLUMNS',
                    'length' => $newColumns
                ]
            ]);

            $batchUpdateRequest = new BatchUpdateSpreadsheetRequest([
                'requests' => $requests
            ]);

            $service->spreadsheets->batchUpdate($this->id, $batchUpdateRequest);
        }
    
        $requests = [];
    
        foreach ($values as $rowIndex => $row) {
            $rowData = [];

            foreach ($row as $colIndex => $value) {
                if (is_string($value)) {
                    $detectedEncoding = mb_detect_encoding($value, ['UTF-8', 'ISO-8859-1', 'ISO-8859-15', 'Windows-1252', 'ASCII'], true);
            
                    if ($detectedEncoding === false) {
                        $detectedEncoding = 'Windows-1252';
                    }
            
                    $value = mb_convert_encoding($value, 'UTF-8', $detectedEncoding);
                }
            
                $cellData = new CellData([
                    'userEnteredValue' => is_numeric($value)
                        ? ['numberValue' => (float) $value]
                        : ['stringValue' => (string) $value]
                ]);

                $rowData[] = $cellData;
            }
    
            if (!empty($rowData)) {
                $requests[] = new Request([
                    'updateCells' => [
                        'start' => [
                            'sheetId' => $sheetId,
                            'rowIndex' => $rowIndex,
                            'columnIndex' => 0,
                        ],
                        'rows' => [new RowData(['values' => $rowData])],
                        'fields' => 'userEnteredValue'
                    ]
                ]);
            }
        }
    
        if (empty($requests)) {
            throw new \Exception('No valid requests to send to Google Sheets API.');
        }
    
        $batchUpdateRequest = new BatchUpdateSpreadsheetRequest([
            'requests' => $requests
        ]);
    
        $service->spreadsheets->batchUpdate($this->id, $batchUpdateRequest);
    
        return [
            'status' => true,
        ];
    }

    public function batchUpdateInChunks(string $page, array $values, int $chunkSize = 500): array
    {
        if (is_null($page)) {
            throw new \Exception('Spreadsheet page must be filled');
        }
    
        if (empty($values)) {
            throw new \Exception('Spreadsheet values must be filled');
        }
    
        $service = new Sheets($this->client);
    
        $service->spreadsheets_values->clear($this->id, $page, new ClearValuesRequest());
    
        $spreadsheet = $service->spreadsheets->get($this->id);
    
        $sheets = $spreadsheet->getSheets();
    
        $sheetId = null;

        $rowCount = null;

        $columnCount = null;
    
        foreach ($sheets as $sheet) {
            if ($sheet->getProperties()->getTitle() === $page) {
                $sheetId = $sheet->getProperties()->getSheetId();

                $rowCount = $sheet->getProperties()->getGridProperties()->getRowCount();

                $columnCount = $sheet->getProperties()->getGridProperties()->getColumnCount();

                break;
            }
        }
    
        if (is_null($sheetId)) {
            throw new \Exception('Sheet not found: ' . $page);
        }

        $valuesRowCount = count($values);

        if ($valuesRowCount > $rowCount) {
            $newRows = $valuesRowCount - $rowCount;

            $requests[] = new Request([
                'appendDimension' => [
                    'sheetId' => $sheetId,
                    'dimension' => 'ROWS',
                    'length' => $newRows
                ]
            ]);

            $batchUpdateRequest = new BatchUpdateSpreadsheetRequest([
                'requests' => $requests
            ]);

            $service->spreadsheets->batchUpdate($this->id, $batchUpdateRequest);
        }

        $maxColumnCount = 0;

        foreach ($values as $row) {
            $currentColumnCount = count($row);

            if ($currentColumnCount > $maxColumnCount) {
                $maxColumnCount = $currentColumnCount;
            }
        }

        if ($maxColumnCount > $columnCount) {
            $newColumns = $maxColumnCount - $columnCount;

            $requests[] = new Request([
                'appendDimension' => [
                    'sheetId' => $sheetId,
                    'dimension' => 'COLUMNS',
                    'length' => $newColumns
                ]
            ]);

            $batchUpdateRequest = new BatchUpdateSpreadsheetRequest([
                'requests' => $requests
            ]);

            $service->spreadsheets->batchUpdate($this->id, $batchUpdateRequest);
        }
    
        $chunks = array_chunk($values, $chunkSize);
    
        foreach ($chunks as $chunkIndex => $chunk) {
            $requests = [];
    
            $startRowIndex = $chunkIndex * $chunkSize;
    
            foreach ($chunk as $rowIndex => $row) {
                $rowData = [];
    
                foreach ($row as $colIndex => $value) {
                    if (is_string($value)) {
                        $detectedEncoding = mb_detect_encoding($value, ['UTF-8', 'ISO-8859-1', 'ISO-8859-15', 'Windows-1252', 'ASCII'], true);
    
                        if ($detectedEncoding === false) {
                            $detectedEncoding = 'Windows-1252';
                        }
    
                        $value = mb_convert_encoding($value, 'UTF-8', $detectedEncoding);
                    }
    
                    $cellData = new CellData([
                        'userEnteredValue' => is_numeric($value)
                            ? ['numberValue' => (float) $value]
                            : ['stringValue' => (string) $value]
                    ]);
    
                    $rowData[] = $cellData;
                }
    
                if (!empty($rowData)) {
                    $requests[] = new Request([
                        'updateCells' => [
                            'start' => [
                                'sheetId' => $sheetId,
                                'rowIndex' => $startRowIndex + $rowIndex,
                                'columnIndex' => 0,
                            ],
                            'rows' => [new RowData(['values' => $rowData])],
                            'fields' => 'userEnteredValue'
                        ]
                    ]);
                }
            }
    
            if (!empty($requests)) {
                $batchUpdateRequest = new BatchUpdateSpreadsheetRequest([
                    'requests' => $requests
                ]);
    
                $service->spreadsheets->batchUpdate($this->id, $batchUpdateRequest);
            }
        }
    
        return [
            'status' => true,
        ];
    }
}