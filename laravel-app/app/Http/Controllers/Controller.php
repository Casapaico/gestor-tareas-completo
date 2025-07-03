<?php

namespace App\Http\Controllers; // Import necessary classes for the controller

use Illuminate\Foundation\Auth\Access\AuthorizesRequests; // Import AuthorizesRequests trait for authorization
use Illuminate\Foundation\Validation\ValidatesRequests; // Import ValidatesRequests trait for validation
use Illuminate\Routing\Controller as BaseController; // Import the base Controller class from Laravel's routing system

class Controller extends BaseController // Define the base controller class for the application
{
    use AuthorizesRequests, ValidatesRequests; // Use the AuthorizesRequests and ValidatesRequests traits to enable authorization and validation features
    // This class can be extended by other controllers in the application
    // It provides a base for handling requests, authorizing actions, and validating input data
    // Additional methods and properties can be added here as needed
}
