<?php
[
    "domain" => $domain
] = $INPUT_DATA;

$user = user_must_authenticated();

db_execute(db_stmt(
    "insert into sites (domain, owner) values (?, ?)",
    "si",
    $domain,
    $user["uid"]
));

respond_sucess([
    "sid" => db_get_last_inserted_id(),
    "domain" => $domain
])

?>