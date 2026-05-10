<?php

namespace App\Platform\Contracts;

interface GateInterface
{
    public function authorize(): bool;
}
