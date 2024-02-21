<?php
// 连接MySQL数据库
$servername = "localhost";
$username = "fanyule";
$password = "fBcMKAEAiKHTcwXp";
$dbname = "fanyule";
$conn = mysqli_connect($servername, $username, $password, $dbname);
if (!$conn) {
    die("连接数据库失败：" . mysqli_connect_error());
}
$shopIds = [100, 109, 111];
// $shopIds = [63];
// 查询用户信息
$sql = "SELECT id, views, user_id FROM shops where id in (" . implode(",", $shopIds).")";
$result = mysqli_query($conn, $sql);
if (mysqli_num_rows($result) > 0) {
    // 输出查询结果
    while ($row = mysqli_fetch_assoc($result)) {
        if (!empty($row["user_id"])) {
            $sql1 = "SELECT count(id) FROM products where published = 1 and user_id=".$row["user_id"];
            //  echo $sql1;
            $result1 = mysqli_query($conn, $sql1);
            if (mysqli_num_rows($result1) > 0) {
                $num = random_int(1, 100);
                file_put_contents("./add.log", date("Y-m-d H:i:s") ."\t". $row["user_id"] . "\t" . $num."\r\n", FILE_APPEND);
                if ($num % 2 == 0) {
                    $update_sql = "UPDATE shops SET views=".($row["views"]+1)." WHERE id=" . $row["id"];
                    mysqli_query($conn, $update_sql);
                }
            }
        }
    }
}

// 关闭数据库连接
mysqli_close($conn);
?>