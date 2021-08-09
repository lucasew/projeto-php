<?php
require 'utils.php';

log_httpd("Puxando db");
getDatabase();
respond_sucess([
    "out" => is_entrypoint(__FILE__)
]);
?>