<?php
$user = $INPUT_DATA["user"];
$password = $INPUT_DATA["password"];
db_execute(db_stmt(
    "insert into users (username, role, password) values (?, ?, ?)",
    "sss",
    $user,
    "USER",
    pw_create($password)
));
respond_sucess([
    "uid" => db_get_last_inserted_id(),
    "user_name" => $user
]);
?>