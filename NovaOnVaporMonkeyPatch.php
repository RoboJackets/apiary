<?php

namespace Laravel\Nova\Http\Controllers;

function filemtime(string $path): int {
    return time();
}
