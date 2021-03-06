<?php
user_must_admin();

$sites = db_execute(db_stmt("
delete from sites
where 
    owner not in 
        (select uid from users)
"));

$slugs = db_execute(db_stmt("
delete from slug
where
    sid not in
        (select sid from sites)
"));

$comments = db_execute(db_stmt("
delete from comments
where
    slid not in
        (select slid from slug)
    or uid not in
        (select uid from users)
"));

$analytics = db_execute(db_stmt("
delete from analytics_datapoint
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