<?php
$user = user_must_authenticated();

respond_sucess([
    "sites" => db_get_all_result(db_stmt(
        "select 
            sid, domain, users.username, sites.created_at 
        from sites 
        join users on owner = users.uid
        where 
            owner = ?
        ", "i", $user["uid"],
    ), MYSQLI_ASSOC)
]);
?>