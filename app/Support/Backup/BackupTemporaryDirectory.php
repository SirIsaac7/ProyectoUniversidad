<?php

namespace App\Support\Backup;

use Spatie\TemporaryDirectory\TemporaryDirectory;

class BackupTemporaryDirectory extends TemporaryDirectory
{
    public function empty(): static
    {
        return $this;
    }
}
