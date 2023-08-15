<?php

namespace A2billing;

use Profiler_Profiler;

class Profiler extends Profiler_Profiler
{
    private ?Query_trace $db;

    /*--------------------------------------------------------
         QUERY DATA -- DATABASE OBJECT WITH LOGGING REQUIRED
    ----------------------------------------------------------*/

    public function gatherQueryData()
    {
        $queryTotals = [];
        $queryTotals['count'] = 0;
        $queryTotals['time'] = 0;
        $queries = [];

        if (!is_null($this->db)) {
            $queryTotals['count'] += $this->db->queryCount;
            foreach ($this->db->queries as $query) {
                $queryTotals['time'] += $query['time'];
                $query['time'] = $this->getReadableTime($query['time']);
                $queries[] = $query;
            }
        }

        $queryTotals['time'] = $this->getReadableTime($queryTotals['time']);
        $this->output['queries'] = $queries;
        $this->output['queryTotals'] = $queryTotals;
    }

    public function display(Query_trace $db = null)
    {
        $this->db = $db;
        parent::display();
    }
}
