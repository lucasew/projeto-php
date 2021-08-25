<?php
["uid" => $uid, "is_admin" => $is_admin] = user_must_authenticated();
["cid" => $cid] = $INPUT_DATA;

if ($is_admin) {
    $affected = db_execute(db_stmt("
    delete from comments where cid = ?
    ", "i", $cid));
    respond_sucess([
        "cid" => $cid,
        "modified" => $affected > 0
    ]);
} else {
    $affected = db_execute(db_stmt("
    delete from comments 
    where 
        cid = ?
        and uid = ?
    ", "ii", $cid, $uid));
    respond_sucess([
        "cid" => $cid,
        "modified" => $affected > 0
    ]);
}

?>