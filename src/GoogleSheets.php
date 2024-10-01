<?php

namespace CeytekLabs\GoogleServicesLite;

use Google\Service\Sheets;
use Google\Client;
use Google\Service\Sheets\ClearValuesRequest;
use Google\Service\Sheets\ValueRange;

class GoogleSheets
{
    private Client $client;

    private ?string $id = null;

    private ?array $values = null;

    private ?string $page = null;

    public static function make(): self
    {
        $instance = new self;

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

    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function setPage(string $page): self
    {
        $this->page = $page;

        return $this;
    }

    public function setValues(array $values): self
    {
        $this->values = $values;

        return $this;
    }

    public function update(): array
    {
        if (is_null($this->id)) {
            throw new \Exception('Spreadsheet id must be filled');
        }

        if (is_null($this->page)) {
            throw new \Exception('Spreadsheet page must be filled');
        }

        if (is_null($this->values)) {
            throw new \Exception('Spreadsheet values must be filled');
        }

        $service = new Sheets($this->client);

        $service->spreadsheets_values->clear($this->id, $this->page, new ClearValuesRequest());

        $body = new ValueRange(['values' => $this->values]);

        $result = $service->spreadsheets_values->update($this->id, $this->page, $body, ['valueInputOption' => 'RAW']);

        return [
            'updated_cells_count' => $result->getUpdatedCells(),
        ];
    }
}