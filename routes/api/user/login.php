<?php
$user = user_must_authenticated();
respond_sucess([
    "jwt" => jwt_encode($user)
]);
?>