<?php
user_must_admin();
[
    "variable" => $variable
] = $INPUT_DATA;

respond_sucess([
    "value" => $_ENV[$variable] ?? null
]);
?>