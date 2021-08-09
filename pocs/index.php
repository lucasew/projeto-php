<?php
function show($item) {
    echo "<p>";
    echo $item;
    echo "</p>";
}
?>
<?php
include "./setup.php";
?>
<h1>Hello, world</h1>
<?php
show("Nhaa");
show(var_dump (2));

show(var_dump($db));

show(2+2);
show(phpversion());

$stmt = $db->prepare("show tables");
$stmt->execute();
$result = $stmt->get_result();
var_dump($result);
?>
