<?php
user_must_admin();
respond_sucess([
    "datapoints" => db_get_all_result(db_stmt(
        "select * from analytics_datapoint"
    ), MYSQLI_ASSOC)
])
?>