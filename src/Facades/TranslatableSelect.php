<?php

namespace Xoshbin\TranslatableSelect\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Xoshbin\TranslatableSelect\TranslatableSelect
 */
class TranslatableSelect extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Xoshbin\TranslatableSelect\TranslatableSelect::class;
    }
}
