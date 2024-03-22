<?php

declare(strict_types=1);

namespace AbstractRepo\Models;

use AbstractRepo\Interfaces\IModel;
use JsonSerializable;

final readonly class FetchedData implements JsonSerializable
{
    /**
     * @param IModel[] $data
     * @param int $currentPage
     * @param int $itemsPerPage
     * @param int $totalPages
     */
    public function __construct(
        private array $data,
        private int   $currentPage,
        private int   $itemsPerPage,
        private int   $totalPages
    )
    {

    }

    /**
     * @return IModel[]
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @return int
     */
    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    /**
     * @return int
     */
    public function getItemsPerPage(): int
    {
        return $this->itemsPerPage;
    }

    /**
     * @return int
     */
    public function getTotalPages(): int
    {
        return $this->totalPages;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            "data" => $this->getData(),
            "currentPage" => $this->getCurrentPage(),
            "itemsPerPage" => $this->getItemsPerPage(),
            "totalPages" => $this->getTotalPages()
        ];
    }
}