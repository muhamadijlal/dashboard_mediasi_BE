<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class MediasiQuery
{
    /**
     * Execute a query with dynamic table and select columns.
     * 
     * @param string $table
     * @param array $selectColumns
     * @param array $whereConditions
     * @param string $connection
     * @return \Illuminate\Support\Collection
    */

    public static function get($table, $selectColumn = ["*"], $whereConditions = [], $connection = 'mediasi') 
    {
        $query = DB::connection($connection)->table($table)->select($selectColumn);

        foreach($whereConditions as $column => $value) {
            if (is_array($value)) {
                $query->whereBetween($column, $value);
            } else {
                $query->where($column, $value);
            }
        }

        return $query->get();
    }
}