<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponse; // Import the trait

abstract class Controller
{
    use ApiResponse; // Enable the standardized response methods for all child controllers
}