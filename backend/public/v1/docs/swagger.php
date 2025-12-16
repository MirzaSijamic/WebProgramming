<?php
// Suppress warnings and errors for clean JSON output
ini_set('display_errors', 0);
error_reporting(0);

require __DIR__ . '/../../../vendor/autoload.php';

// Fix SERVER_NAME check for CLI
if (php_sapi_name() !== 'cli' && isset($_SERVER['SERVER_NAME'])) {
    if($_SERVER['SERVER_NAME'] == 'localhost' || $_SERVER['SERVER_NAME'] == '127.0.0.1'){
        define('BASE_URL', 'http://localhost/WebProgramming/WebProgramming/backend');
    } else {
        define('BASE_URL', '');
    }
} else {
    define('BASE_URL', 'http://localhost/WebProgramming/WebProgramming/backend');
}

// Stub Flight class for swagger-php parsing (if not already loaded)
if (!class_exists('Flight')) {
    class Flight {
        public static function group($path, $callback) {}
        public static function route($pattern, $callback) {}
        public static function register($name, $class) {}
    }
}

// Explicitly require the OpenAPI spec file (doc_setup.php)
require_once __DIR__ . '/doc_setup.php';

// Ensure Flight class exists for parsing route files
// This stub allows swagger-php to parse the route files without errors
if (!class_exists('Flight')) {
    class Flight {
        public static function group($path, $callback) {
            // Execute callback to register routes, but this won't affect swagger-php parsing
            if (is_callable($callback)) {
                try {
                    $callback();
                } catch (Exception $e) {
                    // Ignore execution errors during parsing
                }
            }
        }
        public static function route($pattern, $callback) {}
        public static function register($name, $class) {}
        public static function json($data, $code = 200) {}
        public static function halt($code, $message) {}
        public static function request() {
            return new class {
                public $data;
                public function __construct() {
                    $this->data = new class {
                        public function getData() { return []; }
                    };
                }
            };
        }
    }
}

// Get the paths to scan
$docsPath = __DIR__; // Contains doc_setup.php
$routesPath = realpath(__DIR__ . '/../../../rest/routes');

// Build explicit file list to ensure all files are scanned
$filesToScan = [
    __DIR__ . '/doc_setup.php',
    $routesPath . '/AuthRoutes.php',
    $routesPath . '/PlaylistRoutes.php',
    $routesPath . '/TrackRoutes.php',
    $routesPath . '/TagRoutes.php'
];

// Require route files so classes are defined (needed for swagger-php to find annotations)
// Use output buffering to prevent any output
ob_start();
foreach ($filesToScan as $file) {
    if (file_exists($file)) {
        try {
            require_once $file;
        } catch (Exception $e) {
            // Ignore execution errors
        }
    }

}
ob_end_clean();

// Scan with error handling
try {
    $openapi = \OpenApi\Generator::scan($filesToScan);
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()], JSON_PRETTY_PRINT);
    exit;
}

// Prevent caching
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

echo $openapi->toJson();
?>
?>