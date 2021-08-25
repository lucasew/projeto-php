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

.comment-section-comments {
    list-style-type: none;
}
.comment-section-comment-user {
    font-weight: bold;
    padding-right: 5px;
}

.comment-section-bottom {
    width: 100%;
    display: flex;
}
.comment-section-bottom-logged input {
    flex: 1;
}

.comment-section-bottom-unlogged * {
    flex: 1;
}
.comment-section-comment-body {
    flex: 1;
}
.comment-section-comment {
    display: flex;
}
.comment-section-comment-button {
    display: inline-block;
    width: 10px;
    height: 10px;
}
.comment-section-comment-button-update {
    background-color: blue;
}
.comment-section-comment-button-delete {
    background-color: red;
}
</style>