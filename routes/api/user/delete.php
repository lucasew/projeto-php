<?php
["uid" => $uid] = user_must_authenticated();

$affected = db_execute(db_stmt(
    "delete from users where uid=?",
    "i",
    $uid
));

respond_sucess([
    "status" => "ok",
    "modified" => $affected > 0
]);
?>