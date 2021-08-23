<?php
[
    "user" => $user,
    "password" => $password
] = $INPUT_DATA;

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