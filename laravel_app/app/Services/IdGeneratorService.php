<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class IdGeneratorService
{
    public function make(string $table, string $column, string $prefix, int $padLength = 2): string
    {
        $lastCode = DB::table($table)
            ->where($column, 'like', $prefix.'%')
            ->orderByDesc($column)
            ->value($column);

        if (!$lastCode) {
            return $prefix.str_pad('1', $padLength, '0', STR_PAD_LEFT);
        }

        if (!preg_match('/^'.preg_quote($prefix, '/').'(\d+)$/', (string) $lastCode, $matches)) {
            return $prefix.str_pad('1', $padLength, '0', STR_PAD_LEFT);
        }

        $nextNumber = (int) $matches[1] + 1;
        $width = max($padLength, strlen((string) $nextNumber));

        return $prefix.str_pad((string) $nextNumber, $width, '0', STR_PAD_LEFT);
    }
}
