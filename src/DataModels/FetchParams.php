<?php

declare(strict_types=1);

namespace AbstractRepo\DataModels;

/**
 * Data model class to hold fetch parameters.
 */
final class FetchParams
{
    /**
     * @param int|null $page
     * @param int|null $itemsPerPage
     * @param string|null $conditions
     * @param array|null $bind
     */
    public function __construct(
        private ?int             $page = null,
        private ?int             $itemsPerPage = null,
        private readonly ?string $conditions = null,
        private readonly ?array  $bind = null
    )
    {
    }

    /**
     * @return int|null
     */
    public function getPage(): ?int
    {
        return $this->page;
    }

    /**
     * @return int|null
     */
    public function getItemsPerPage(): ?int
    {
        return $this->itemsPerPage;
    }

    /**
     * @return string|null
     */
    public function getConditions(): ?string
    {
        return $this->conditions;
    }

    /**
     * @return array|null
     */
    public function getBind(): ?array
    {
        return $this->bind;
    }

    /**
     * @param int $page
     * @return void
     */
    public function setPage(int $page): void
    {
        $this->page = $page;
    }

    /**
     * @param int $itemsPerPage
     * @return void
     */
    public function setItemsPerPage(int $itemsPerPage): void
    {
        $this->itemsPerPage = $itemsPerPage;
    }
}