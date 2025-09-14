<?php

namespace Xoshbin\TranslatableSelect\Commands;

use Illuminate\Console\Command;

class TranslatableSelectCommand extends Command
{
    public $signature = 'translatable-select';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
