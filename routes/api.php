<?php

use Illuminate\Support\Facades\Route;

require base_path('routes/api/admin.php');

require base_path('routes/api/user.php');

require base_path('routes/api/driver.php');

Route::prefix('socket')->group(base_path('routes/api/socket.php'));
