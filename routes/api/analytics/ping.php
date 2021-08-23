<?php
[
    "domain" => $domain,
    "tag" => $tag
] = $INPUT_DATA;

$site_res = db_get_result(db_stmt("
select sid from sites 
where domain = ?", "s",
$domain));

["sid" => $sid] = $site_res;

if (is_null($sid)) {
    respond_error(404, "site not found");
}

$user = user_get();
$uid = !is_null($user) ? $user["uid"] : null;

[
    "REMOTE_ADDR" => $ip
] = $_SERVER;

$payload = '';
$handle = fopen('php://input', 'r');
for ($i = 0; $i < 100; $i++) { // limita o body a 100kb
    if (feof($handle)) {
        break;
    }
    $payload .= fread($handle, 1024);
}

$is_mobile = is_client_mobile();
db_execute(db_stmt("
insert into analytics_datapoint
(sid, uid, ip, tag, is_mobile, payload) VALUES
(?  , ?  , ? , ?  , ?        , ?      )
", "iissis",
$sid, $uid ?? "NULL", $ip, $tag, $is_mobile, $payload
));

respond_sucess([
    "result" => "ok"
]);

?>