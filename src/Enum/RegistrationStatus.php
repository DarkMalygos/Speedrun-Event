<?php 
namespace App\Enum;

enum RegistrationStatus: string {
    case STANDBY = 'standby';
    case WAITLIST = 'waitlist';
}
?>