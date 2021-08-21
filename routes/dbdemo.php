<?php
forbid_entrypoint(__FILE__);

log_httpd("Puxando db");
getDatabase();
respond_sucess([
    "out" => is_entrypoint(__FILE__)
]);
?>