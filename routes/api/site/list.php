<?php
$user = user_must_authenticated();

respond_sucess([
    "sites" => db_get_all_result(db_stmt(
        "select * from sites where owner = ?", "i", $user["uid"],
    ), MYSQLI_ASSOC)
]);
?>