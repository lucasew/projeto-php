<?php
[
    "slug_id" => $slid
] = $INPUT_DATA;

$comments = db_get_all_result(db_stmt("
select
    body,
    cid,
    username
from comments
inner join users on comments.uid = users.uid
where comments.slid = ?
", "i", $slid), MYSQLI_ASSOC);

respond_sucess([
    "comments" => $comments,
    "slid" => $slid
]);
?>