<?php
namespace App\Enums;
enum PaymentStatus: string {
    case Calculated = 'calculated';
    case Approved   = 'approved';
    case Paid       = 'paid';
}
