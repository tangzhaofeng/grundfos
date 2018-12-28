<?php
dump(111);exit();
$mysqli = new mysqli('127.0.0.1','root','','grundfos','3306');
$sql = "select * from xk_forward where id =".$_GET['id'];

$mysqli -> query('set names utf8');
$url = $mysqli -> query($sql);
$mysqli -> close();

header("Location:".$url);
exit();