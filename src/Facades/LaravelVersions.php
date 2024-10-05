<?php

namespace VildanBina\LaravelVersions\Facades;

use Illuminate\Support\Facades\Facade;

class LaravelVersions extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \VildanBina\LaravelVersions\LaravelVersions::class;
    }
}
