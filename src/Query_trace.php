<?php

namespace A2billing;

class Query_trace
{
    public int $queryCount = 0;
    public array $queries = [];
    private static self $m_pInstance;

    public static function getInstance(): self
    {
        self::$m_pInstance ??= new Query_trace();

        return self::$m_pInstance;
    }
}
