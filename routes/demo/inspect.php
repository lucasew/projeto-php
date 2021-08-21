<?php
forbid_entrypoint(__FILE__);
respond(200, [
    // "message" => $_SERVER,
    "uri" => $_SERVER["REQUEST_URI"],
    "parsed_url" => parse_url($_SERVER["REQUEST_URI"]),
    "get" => $_GET,
    "post" => $_POST,
    "is_get" => is_get($_GET),
    "is_post" => is_post($_POST),
    "input_data" => $input_data,
    "files" => $_FILES
]);
?>