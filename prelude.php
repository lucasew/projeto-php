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
set_error_handler('errorHandler');

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
set_exception_handler('exceptionHandler');
function must_getenv(string $variable): string {
    $value = $_ENV[$variable];
    if ($value === "") {
        throw new Exception("environment variable $". $variable . " is empty");
    }
    return $value;
}

function jwt_get_key(): string {
    $key = must_getenv("JWT_KEY");
    return $key;
}

function jwt_encode($payload): string {
    $key = jwt_get_key();
    $jwt = Firebase\JWT\JWT::encode($payload, $key);
    return $jwt;
}

function jwt_decode(string $jwt) {
    try {
    $key = jwt_get_key();
    $payload = Firebase\JWT\JWT::decode($jwt, $key, array('HS256'));
    $payloadArray = json_decode(json_encode($payload), true);
    return $payloadArray;
    } catch (Exception $e) { // só pra não correr o risco de vazar o secret no stacktrace
        throw new Exception($e->getMessage());
    }
}

$db = null;
function getDatabase() {
    GLOBAL $db;
    if (!is_null($db)) {
        return $db;
    }
    $user = must_getenv("MYSQL_USER");
    $password = must_getenv("MYSQL_PASSWORD");
    $database = must_getenv("MYSQL_DATABASE");
    $host = must_getenv("MYSQL_HOST");
    $db = new mysqli($host, $user, $password);
    if ($db->connect_error) {
        respond_error(500, "failed to connect to database: " . $db->connect_error);
    }
    if (!$db->select_db($database)) {
        db_report_failure();
    };
    return $db;
}

function db_report_failure($stmt = null) {
    $db = getDatabase();
    $use_stmt = !is_null($stmt);
    $errno = $use_stmt ? $stmt->errno : $db->errno;
    $error = $use_stmt ? $stmt->error : $db->error;
    if ($errno == 0) { // nada errado aqui
        return;
    }
    if ($use_stmt) {
        throw new Exception("MySQL statement failure: " . $errno . " " . $error);
    } else {
        throw new Exception("MySQL database failure: " . $errno . " " . $error);
    }
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
        $stmt->bind_param($types, ...$placeholders);
    }
    return $stmt;
}

function db_run(string $query) {
    db_execute(db_stmt($query));
}

function db_execute($stmt) {
    $result = $stmt->execute();
    if (!$result) {
        db_report_failure($stmt);
    }
    return getDatabase()->affected_rows;
}
function db_drop_and_create_table(string $table_name, string ...$columns) {
    db_execute(db_stmt("drop table if exists " . $table_name));
    return db_execute(db_stmt("create table " 
    . $table_name 
    . " (" . join(",", $columns) . ")"));
}

function db_get_result($stmt, $mode = MYSQLI_BOTH) {
    db_execute($stmt);
    $result = $stmt->get_result();
    if (!$result) {
        db_report_failure($stmt);
    }
    return $result->fetch_array($mode);
}

function db_get_all_result($stmt, $mode = MYSQLI_BOTH, $limit = 0) {
    db_execute($stmt);
    $result = $stmt->get_result();
    if (!$result) {
        db_report_failure($stmt);
    }
    if ($limit == 0) {
        $ret = [];
        $i = 0;
        $line = $result->fetch_array($mode);
        while (!is_null($line)) {
            $ret[$i] = $line;
            $i++;
            $line = $result->fetch_array($mode);
        }
        return $ret;
    } else {
        $ret = [];
        for ($i = 0; $i < $limit; $i++) {
            $line = $result->fetch_array($mode);
            if (is_null($line)) {
                break;
            }
            $ret[$i] = $line;
        }
        return $ret;
    }
    return []; // why not xD
}

function db_get_last_inserted_id() {
    return db_get_result(db_stmt("SELECT LAST_INSERT_ID()"))[0];
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
    respond(200, [
        "result" => $data
    ]);
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

function is_client_mobile() {
    $detector = new Mobile_Detect;
    return $detector->isMobile();
}

function pw_create($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

function pw_verify($username, $password) {
    $hashed = db_get_result(db_stmt("select password from users where username = ?", "s", $username));
    if (is_null($hashed)) {
        return false;
    }
    // log_httpd(json_encode($hashed));
    $hashed = $hashed["password"];
    return password_verify($password, $hashed);
}

forbid_entrypoint(__FILE__);
?>