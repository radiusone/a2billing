<?php

namespace A2billing;

class Query_trace
{
    public $queryCount = 0;
    public $queries = array();

    private static $m_pInstance;

    /* CONSTRUCTOR */
    public function __construct()
    {

    }

    // Query_trace::getInstance();
    public static function getInstance()
    {
        if (!self::$m_pInstance) {
            self::$m_pInstance = new Query_trace();
        }

        return self::$m_pInstance;
    }
}
