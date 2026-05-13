<?php

namespace App\Domains\Users\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends \Innertia\Auth\Models\User
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;
}
