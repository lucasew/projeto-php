<?php

require "vendor/autoload.php";
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
header("X-Made_by: @lucasew (github.com/lucasew)");

function errorHandler($severity, $message, $filename, $lineno) {
    respond(500, [
        "error" => [
            "severity" => $severity,
            "message" => $message,
            "filename" => $filename,
            "lineno" => $lineno
        ]
    ]);
}
function exceptionHandler($e) {
    respond(500, [
        "exception" => [
            "message" => $e->getMessage(),
            "trace" => $e->getTrace(),
            "file" => $e->getFile(),
            "line" => $e->getLine()
        ]
    ]);
}
set_error_handler('errorHandler');
set_exception_handler('exceptionHandler');

$db = null;
function getDatabase() {
    GLOBAL $db;
    if (!is_null($db)) {
        return $db;
    }
    $user = getenv("DB_USER");
    $password = getenv("DB_PASSWD");
    $database = getenv("DB_DATABASE");
    $host = getenv("DB_HOST");
    $db = new mysqli($host, $user, $password);
    if ($db->connect_error) {
        respond_error(500, "failed to connect to database: " . $db->connect_error);
    }
    if (!$db->select_db($database)) {
        db_report_failure();
    };
    return $db;
}

function db_report_failure() {
    $db = getDatabase();
    throw new Exception("MySQL statement failure: " . $db->errno . " " . $db->error);
}

/**
 * shorthand for the query bureaucracy, returns the mysql statement
 * $query: your sql query
 * $types: placeholder types, empty by default. See: https://www.php.net/manual/en/mysqli-stmt.bind-param.php
 * $placeholders: the placeholder value
 */
function db_stmt(string $query, string $types = "", string ...$placeholders) {
    $db = getDatabase();
    $stmt = $db->prepare($query);
    if (!$stmt) {
        db_report_failure();
    }
    if ($types != "") {
        $db->bind_param($types, ...$placeholders);
    }
    return $stmt;
}

function db_run(string $query) {
    db_execute(db_stmt($query));
}

function db_execute($stmt) {
    $result = $stmt->execute();
    if (!$result) {
        db_report_failure();
    }
}

function db_get_result($stmt) {
    db_execute($stmt);
    return $stmt->get_result()->fetch_array();
}

// https://stackoverflow.com/questions/6079492/how-to-print-a-debug-log
function log_httpd(string $message) {
    file_put_contents('php://stderr', print_r($message, TRUE));
}

function respond(int $status_code, array $data) {
    http_response_code($status_code);
    header("Content-Type: application/json");
    echo json_encode($data);
    die(0);
}

function respond_sucess(array $data) {
    respond(200, $data);
}

function respond_error(int $status_code, string $message) {
    respond($status_code, [
        "error" => $message
    ]);
}


function cat(string $filename): string {
    $file = fopen($filename, "r");
    $ret = fread($file, filesize($filename));
    fclose($file);
    return $ret;
}
function must_extension(string $extension) {
    if (!extension_loaded($extension)) {
        respond_error(500, "Extension " . $extension . " is not installed. This is fatal");
    }
}
function is_entrypoint($__FILE__) {
    return basename($__FILE__) == basename($_SERVER["SCRIPT_FILENAME"]);
}

function forbid_entrypoint($__FILE__) {
    if (is_entrypoint($__FILE__)) {
        respond_error(400, "invalid route");
    }
}

forbid_entrypoint(__FILE__);
?>