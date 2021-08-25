<?php
["uid" => $uid] = user_must_authenticated();

$sites = db_get_all_result(db_stmt("
select * from sites
where owner = ?
", "i", $uid), MYSQLI_ASSOC);

$slugs = [];

foreach($sites as $i => $value) {
    $slugs[$value["domain"]] = db_get_all_result(db_stmt("
    select slid, slug from slug
    where sid = ?
    ", "i", $value["sid"]), MYSQLI_ASSOC);
}
respond_sucess([
    "slugs" => $slugs
]);
?>