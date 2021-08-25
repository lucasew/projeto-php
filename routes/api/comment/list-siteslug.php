<?php
[
    "domain" => $domain,
    "slug" => $slug,
] = $INPUT_DATA;

$site_res = db_get_result(db_stmt("
select sid from sites 
where domain = ?", "s",
$domain));

["sid" => $sid] = $site_res;

if (is_null($sid)) {
    respond_error(400, "site not found");
}

$slid = db_get_result(db_stmt("
select slid from slug where slug = ? and sid = ?
", "si", $slug, $sid));
if (is_null($slid)) {
    respond_error(404, "slug not found");
}
$slid = $slid[0];

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