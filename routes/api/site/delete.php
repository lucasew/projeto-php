<?php
["uid" => $uid] = user_must_authenticated();
["domain" => $domain] = $INPUT_DATA;
// log_httpd(json_encode($domain));
$affected = db_execute(db_stmt(
    "delete from sites where domain=? and owner=?",
    "si",
    $domain,
    $uid
));

respond_sucess([
    "status" => "ok",
    "modified" => $affected > 0
]);

?>