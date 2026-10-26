<?php

namespace A2billing\Forms\Elements\Edit\Traits;

trait Splittable
{

    /**
     * Take a string such as "3-5,12,18-20" and return ["3", "4", "5", "12", "18", "19", "20"]
     *
     * @param string $value
     * @return string[]
     */
    public static function getSplitValues(string $value): array
    {
        if (empty($value) || str_starts_with($value, "_")) {
            // dialprefix can be a range *or* an Asterisk-style extension pattern starting with _
            return [$value];
        }
        $array = [];

        $items = explode(",", $value);
        foreach ($items as $item) {
            $item = trim($item);
            $range = explode("-", $item, 2);
            if (isset($range[1])) {
                $min = trim($range[0]);
                $max = trim($range[1]);
                // get common prefix to avoid issues with very large numeric strings like card numbers
                $prefix_len = strspn("$min" ^ "$max", chr(0));
                $prefix = substr($min, 0, $prefix_len);
                $min = substr($min, $prefix_len);
                $max = substr($max, $prefix_len);
                if (is_numeric($min) && is_numeric($max) && $min < $max) {
                    for ($i = $min; $i <= $max; $i++) {
                        $array[] = $prefix . $i;
                    }
                } elseif (is_numeric($min)) {
                    $array[] = $prefix . $min;
                } elseif (is_numeric($max)) {
                    $array[] = $prefix . $max;
                }
            } elseif (is_numeric($range[0])) {
                $array[] = $range[0];
            }
        }

        return $array;
    }
}
