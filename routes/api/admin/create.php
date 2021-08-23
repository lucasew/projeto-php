<?php
$user = user_must_admin();

[
    "user" => $user,
    "password" => $password,
    "role" => $role
] = $INPUT_DATA;

db_execute(db_stmt(
    "insert into users 
    (username, role, password)
    values (?, ?, ?)", "sss",
    $user,
    $role ?? "USER",
    pw_create($password)
));

respond_sucess([
    "uid" => db_get_last_inserted_id(),
    "user_name" => $user
]);
?>