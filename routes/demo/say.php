<?php
forbid_entrypoint(__FILE__);

respond(200,[
    "say" => $INPUT_DATA["word"]
]);
?>