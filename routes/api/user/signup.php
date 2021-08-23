<?php
$user = $INPUT_DATA["user"];
$password = $INPUT_DATA["password"];
db_execute(db_stmt(
    "insert into users (username, role, password) values (?, ?, ?)",
    "sss",
    $user,
    "USER",
    $password
));
respond_sucess([
    "user_id" => db_get_result(db_stmt("SELECT LAST_INSERT_ID()"))[0],
    "user_name" => $user
]);
?>