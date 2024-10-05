<?php

namespace VildanBina\LaravelVersions;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;

class LaravelVersions
{
    public function getCurrentUser(): ?Authenticatable
    {
        return Auth::guard(config('versions.auth.guard'))->user();
    }
}
