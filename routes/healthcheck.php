<?php
forbid_entrypoint(__FILE__);
getDatabase();
// throw new Exception("teste");
respond_sucess([
    "message" => "tudo certo, patrão"
]);
?>