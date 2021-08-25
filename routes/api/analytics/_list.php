<?php
user_must_admin();
respond_sucess([
    "datapoints" => db_get_all_result(db_stmt("
    select 
        users.username,
        sites.domain,
        ip,
        tag,
        is_mobile,
        payload,
        analytics_datapoint.created_at
    from analytics_datapoint
    inner join users on users.uid = analytics_datapoint.uid
    inner join sites on sites.sid = analytics_datapoint.sid
    "), MYSQLI_ASSOC)
]);
?>