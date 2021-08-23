<?php
["uid" => $uid] = user_must_authenticated();
[
    "body" => $body,
    "cid" => $cid
] = $INPUT_DATA;

$affected = db_execute(db_stmt("
update comments
set body = ?
where 
    cid = ?
    and uid = ?
", "sii", $body, $cid, $uid));

$modified_comment = db_get_result(db_stmt("
select 
    users.username
    cid,
    body
from comments
join users on users.uid = comments.uid
where cid = ?
", "i", $cid));

respond_sucess([
    "comment" => $modified_comment,
    "modified" => $affected > 0
])

?>