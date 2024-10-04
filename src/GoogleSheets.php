<?php

namespace CeytekLabs\GoogleServicesLite;

use Google\Service\Sheets;
use Google\Client;
use Google\Service\Sheets\ClearValuesRequest;
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

    /**
     * Google Sheets API Limits:
     * - Maximum number of cells that can be updated in a single request: 10,000 cells.
     * - Maximum write requests per user per minute: 60 requests.
     *
     * Therefore, you need to consider these limits when updating your data.
     * For example, if you update 500 rows and 10 columns in each request,
     * this amounts to 5,000 cells and stays within the limits.
     */
    public function updateInChunks(string $page = null, array $values = [], int $chunkSize = 500): array
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
}