<?php
[
    "uid" => $uid,
    "username" => $username
] = user_must_authenticated();
[
    "domain" => $domain,
    "slug" => $slug,
    "body" => $body
] = $INPUT_DATA;

$site_res = db_get_result(db_stmt("
select sid from sites 
where domain = ?", "s",
$domain));

log_httpd(json_encode($site_res));
["sid" => $sid] = $site_res;

if (is_null($sid)) {
    respond_error(404, "site not found");
}

// log_httpd(json_encode(db_get_result(db_stmt("select * from sites"))));
// log_httpd(json_encode([$site_res]));
// log_httpd(json_encode([$sid]));

$slid = db_get_result(db_stmt("
select slid from slug where slug = ? and sid = ?
", "si", $slug, $sid));
if (is_null($slid)) {
    db_execute(db_stmt("insert into slug (sid, slug) values (?, ?)", "is", $sid, $slug));
    $slid = [db_get_last_inserted_id()];
}
$slid = $slid[0];

db_execute(db_stmt("insert into comments (slid, uid, body) values (?, ?, ?)",
"iis", $slid, $uid, $body));
$cid = db_get_last_inserted_id();

respond_sucess([
    "comment" => [
        "user" => $username,
        "cid" => $cid,
        "site" => $domain,
        "sid" => $sid,
        "slug" => $slug,
        "slid" => $slid,
        "body" => $body
    ]
]);

?>