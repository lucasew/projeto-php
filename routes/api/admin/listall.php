<?php
user_must_admin();
[
    "entity" => $entity
] = $INPUT_DATA;

respond_sucess([
    $entity => db_get_all_result(db_stmt("select * from " . $entity), MYSQLI_ASSOC)
]);
?>