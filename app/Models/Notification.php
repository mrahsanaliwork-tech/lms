<?php

namespace App\Models;

use Illuminate\Notifications\DatabaseNotification;

// Alias to Laravel's built-in DatabaseNotification so existing references keep working.
class Notification extends DatabaseNotification
{
}
