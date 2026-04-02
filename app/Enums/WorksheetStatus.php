<?php namespace App\Enums;
enum WorksheetStatus: string {
    case Draft    = 'draft';
    case Pending  = 'pending';
    case Synced   = 'synced';
    case Approved = 'approved';
    case Paid     = 'paid';
}
