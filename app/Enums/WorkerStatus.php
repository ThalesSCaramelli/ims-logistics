<?php
// app/Enums/WorkerStatus.php
namespace App\Enums;

enum WorkerStatus: string
{
    case Active    = 'active';
    case Suspended = 'suspended';
    case Inactive  = 'inactive';

    public function label(): string
    {
        return match($this) {
            self::Active    => 'Active',
            self::Suspended => 'Suspended',
            self::Inactive  => 'Inactive',
        };
    }
}
