<?php
["uid" => $uid] = user_must_authenticated();
["domain" => $domain] = $INPUT_DATA;

respond_sucess([
    "raw_data" => db_get_all_result(db_stmt("
    select 
        users.username,
        ip,
        tag,
        is_mobile,
        payload,
        analytics_datapoint.created_at
    from analytics_datapoint
    inner join users on users.uid = analytics_datapoint.uid
    where
        sid in (
            select sid from sites
                where domain = ?)
    ", "s", $domain), MYSQLI_ASSOC)
]);
?>