<?php
["uid" => $uid, "is_admin" => $is_admin] = user_must_authenticated();
["slid" => $sid] = $INPUT_DATA;

if ($is_admin) {
    $affected = db_execute(db_stmt("
    delete from slug
    where
        slid = ?
    ", "i", $slid));
    respond_sucess([
        "status" => "ok",
        "modified" => $affected > 0
    ]);
} else {
    $affected = db_execute(db_stmt("
    delete from slug
    inner join sites on slug.sid = sites.sid
    inner join users on users.uid = sites.owner
    where
        uid = ?
        and slid = ?
    ", "ii", $slid, $uid));
    respond_sucess([
        "status" => "ok",
        "modified" => $affected > 0
    ]);
}
?>