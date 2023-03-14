<?php

namespace core\Database;

class DatabaseInfo
{

    public string|null $limit = null;
    private Mysql      $mysql;

    public function __construct(Mysql $mysql)
    {
        $this->mysql = $mysql;
    }

    /**
     * @param int $page
     * @param int $limit
     * @return \core\Database\DatabaseInfo
     */
    public function page(int $page, int $limit): static
    {
        $start = ($page - 1) * $limit;
        $end   = ($page * $limit) - 1;
        $this->limit($start, $end);
        return $this->mysql;
    }

    /**
     * @param int $start
     * @param int $end
     * @return \core\Database\DatabaseInfo
     */
    public function limit(int $start, int $end): static
    {
        $this->limit = "$start,$end";
        return $this->mysql;
    }
}