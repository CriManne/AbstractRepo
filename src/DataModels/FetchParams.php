<?php

declare(strict_types=1);

namespace AbstractRepo\DataModels;

/**
 * @TODO: Refactor, phpdocs, cleaning and optimize.
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
        private ?int    $page = null,
        private ?int    $itemsPerPage = null,
        private ?string $conditions = null,
        private ?array  $bind = null
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



    public function setPage(int $page): void
    {
        $this->page = $page;
    }

    public function setItemsPerPage(int $itemsPerPage): void
    {
        $this->itemsPerPage = $itemsPerPage;
    }
}