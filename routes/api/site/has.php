<?php
["domain" => $domain] = $INPUT_DATA;
$site = db_get_result(db_stmt("
select 
    sites.sid, 
    sites.domain, 
    users.username as owner 
from sites 
inner join users on sites.owner = users.uid 
where domain = ?
", "s", $domain), MYSQLI_ASSOC);
if (!is_null($site)) {
    respond_sucess([
        "site" => $site
    ]);
} else {
    respond_error(404, "not found");
}
?>