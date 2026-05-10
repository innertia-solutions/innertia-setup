<?php

namespace App\Platform\Contracts;

abstract class UseCase implements Executable
{
    public function __invoke(...$args)
    {
        return $this->execute(...$args);
    }
}
