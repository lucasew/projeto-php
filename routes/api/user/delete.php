<?php
$user = user_must_authenticated();

db_execute(db_stmt(
    "delete from users where uid=?",
    "i",
    $user["uid"]
));

respond_sucess(["status" => "ok"]);
?>