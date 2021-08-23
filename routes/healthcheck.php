<?php
forbid_entrypoint(__FILE__);
getDatabase();
// throw new Exception("teste");

// db_run("create table teste (id int auto_increment primary key)");
respond_sucess([
    "message" => "tudo certo, patrão",
    "tables" => db_get_all_result(db_stmt("show full tables"), MYSQLI_ASSOC),
    "isMobile" => is_client_mobile()
]);
?>