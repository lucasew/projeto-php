<?php
forbid_entrypoint(__FILE__);

respond(200,[
    "say" => $input_data["word"]
]);
?>