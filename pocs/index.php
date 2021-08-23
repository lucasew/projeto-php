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

<script type="text/javascript" src="/commentsection.js?slug=eoq"></script>
<style>
.comment-section {
    display: flex;
    max-width: 500px;
    flex-direction: column;
    margin: auto;
}
.comment-section p {
    margin: 0;
}
.commment-section > button {
    width: 100%;
}
.comment-section-comment-username {
    font-weight: bold;
    padding-right: 10px;
}
</style>