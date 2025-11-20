<?php
// Production Error Handling Configuration
// Include this file at the top of your main application files

// Set error reporting for production
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors to users
ini_set('log_errors', 1); // Log errors to file
ini_set('error_log', __DIR__ . '/storage/logs/php_errors.log');

// Set custom error handler
function customErrorHandler($errno, $errstr, $errfile, $errline) {
    $error_message = "Error [$errno]: $errstr in $errfile on line $errline";
    
    // Log to file
    error_log($error_message);
    
    // For production, don't show errors to users
    if (ini_get('display_errors') == 0) {
        // Redirect to 500 error page for fatal errors
        if ($errno == E_ERROR || $errno == E_CORE_ERROR || $errno == E_COMPILE_ERROR) {
            http_response_code(500);
            include __DIR__ . '/500.php';
            exit;
        }
    }
    
    return false; // Let PHP handle the error normally
}

// Set the custom error handler
set_error_handler('customErrorHandler');

// Set exception handler
function customExceptionHandler($exception) {
    $error_message = "Uncaught exception: " . $exception->getMessage() . " in " . $exception->getFile() . " on line " . $exception->getLine();
    
    // Log to file
    error_log($error_message);
    
    // Show 500 error page
    http_response_code(500);
    include __DIR__ . '/500.php';
    exit;
}

set_exception_handler('customExceptionHandler');

// Set shutdown handler for fatal errors
function customShutdownHandler() {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE])) {
        $error_message = "Fatal error: " . $error['message'] . " in " . $error['file'] . " on line " . $error['line'];
        
        // Log to file
        error_log($error_message);
        
        // Show 500 error page
        http_response_code(500);
        include __DIR__ . '/500.php';
    }
}

register_shutdown_function('customShutdownHandler');
?>
