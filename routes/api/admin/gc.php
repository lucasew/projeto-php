<?php
user_must_admin();

$sites = db_get_all_result(db_stmt("
select domain from sites
where 
    owner not in 
        (select uid from users)
"));

$slugs = db_get_all_result(db_stmt("
select slid from slug
where
    sid not in
        (select sid from sites)
"));

$comments = db_get_all_result(db_stmt("
select cid from comments
where
    slid not in
        (select slid from slug)
    or uid not in
        (select uid from users)
"));

$analytics = db_get_all_result(db_stmt("
select sid from analytics_datapoint
where
    uid not in 
        (select uid from users)
"));

respond_sucess([
    "sites" => $sites,
    "slugs" => $slugs,
    "comments" => $comments,
    "analytics" => $analytics
]);

?>