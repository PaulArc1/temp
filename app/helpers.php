<?php

if (! function_exists('hasAcceptableTolerance')) {
    function hasAcceptableTolerance($nominal, $upper, $lower, $actual): bool
    {
        if ($lower) {
            $lower = str_replace('-', '', $lower);
        }

        if ((
            isset($nominal) && $nominal != ''
            && isset($upper) && $upper != ''
            && isset($lower) && $lower != ''
            && isset($actual)) && $actual != '') {
            if ($actual > $nominal + $upper ||
                $actual < $nominal - $lower) {
                return false;
            } else {
                return true;
            }
        }

        return true;
    }
}
