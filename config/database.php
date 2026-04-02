<?php
$conn = mysqli_connect("localhost", "root", "", "quanlythuvien");
mysqli_set_charset($conn, "utf8");

if (!$conn) {
    die("Kết nối CSDL thất bại: " . mysqli_connect_error());
}
?>