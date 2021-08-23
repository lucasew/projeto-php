<?php
$user = user_must_authenticated();
respond_sucess([
    "user" => $user
]);
?>