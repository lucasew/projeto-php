<?php
["uid" => $uid] = user_must_authenticated();
["domain" => $domain] = $INPUT_DATA;

$slugs = db_get_all_result(db_stmt("
select slid, slug from slug
where sid in (select sid from sites where owner = ?) 
", "i", $uid), MYSQLI_ASSOC);

respond_sucess([
    "slugs" => $slugs
]);
?>