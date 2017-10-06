<?php

namespace AppBundle\Pagination;

class PaginatedCollection
{
    private $items;
    private $total;
    private $count;
    private $_links = [];

    /**
     * PaginatedCollection constructor.
     *
     * @param array $items
     * @param int   $totalItems
     */
    public function __construct(array $items, $totalItems)
    {
        $this->items = $items;
        $this->total = $totalItems;
        $this->count = count($items);
    }

    public function addLink($ref, $url)
    {
        $this->_links[$ref] = $url;
    }

    /**
     * Get items
     *
     * @return array
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * Get total
     *
     * @return int
     */
    public function getTotal(): int
    {
        return $this->total;
    }

    /**
     * Get count
     *
     * @return int
     */
    public function getCount(): int
    {
        return $this->count;
    }

    /**
     * Get _links
     *
     * @return array
     */
    public function getLinks(): array
    {
        return $this->_links;
    }
}