<?php
forbid_entrypoint(__FILE__);
respond(200, [
    // "message" => $_SERVER,
    "uri" => $_SERVER["REQUEST_URI"],
    "parsed_url" => parse_url($_SERVER["REQUEST_URI"]),
    "get" => $_GET,
    "post" => $_POST,
    "input_data" => $INPUT_DATA,
    "files" => $_FILES
]);
?>