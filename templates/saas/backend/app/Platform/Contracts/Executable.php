<?php

namespace App\Platform\Contracts;

interface Executable
{
    public function execute(...$args);
}
