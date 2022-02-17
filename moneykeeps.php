<?php
define("DB_HOST","localhost");
define("DB_USER","root");
define("DB_PASS","root");
define("DB_NAME","moneykeeps");
define("DB_CHARSET","utf8mb4");

//session_start();
//データベースの読み込み
$instance = new mysqli(DB_HOST,DB_USER,DB_PASS,DB_NAME);
if(! $instance -> connect_error){
    //正常に接続できた場合の処理
    $instance -> set_charset(DB_CHARSET);
}

// 分岐処理（登録，更新，削除
if ($_SERVER["REQUEST_METHOD"] == "POST" && $_POST ) {
    if ($_POST['operation'] == "update") {
        update($instance);
    } else if ($_POST['operation'] == "create") {
        error_log("call create");
        create($instance);
    } else if ($_POST['operation'] == "delete") {
        delete($instance);
    }
}

// 検索と表示
// DB接続
// DB検索   
$sql = "SELECT * FROM PRODUCT";
//SQLの実行準備
$meals = [];
if ($result = $instance->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $num = $row['num'];
        $photo = $row['PHOTO'];
        $date = $row['DATE'];
        $category = $row['CATEGORY'];
        $money = $row['MONEY'];
        $name = $row['NAME'];
       // $memo = $row['MEMO'];
        $meals += [ "$num" => ["$photo", "$date", "$category", "$money", "$name"]];
    }
}

// 更新
function update($mysql) {
    if (isset($_FILES['photo']) && is_uploaded_file($_FILES['photo']['tmp_name'])) {
        error_log('upload');
        $image_name = uploadImage($_FILES['photo']);
        error_log('upload ok');
    } else {
        $image_name =  $_POST['prev_photo'];
    }

    // ===== 更新処理 =====
    $sql = "UPDATE PRODUCT SET PHOTO = ?, DATE = ?,CATEGORY = ?, MONEY = ?, NAME =? , where num = ?";
    $_POST["name"];

    if($stmt = $mysql -> prepare($sql)){
        error_log("call prepared statement");
        //SQLの実行準備成功
        //変数のバインド（商品番号,商品名,カテゴリ,値段）
        $stmt -> bind_param("sssisis",$image_name,$_POST["date"],$_POST["category"],$_POST["money"],$_POST["name"], $_POST['num']);
        //SQLの実行
        $stmt -> execute();
        error_log("execute ps");
        $mysql->commit();
        $stmt -> close();
    }
}

// 登録
function create($mysql) {
    error_log('create');
    if (isset($_FILES['photo']) && is_uploaded_file($_FILES['photo']['tmp_name'])) {
        error_log('upload');
        $image_name = uploadImage($_FILES['photo']);
        error_log('upload ok');
    }

    $sql = "INSERT INTO PRODUCT(NUM,DATE,NAME,MONEY,CATEGORY,PHOTO) VALUES(?,?,?,?,?)";
    //$_POST["name"];
    /*<p class="text-red-600"><?= $errmessage?></p>*/
    if($stmt = $mysql -> prepare($sql)){
        error_log("call prepared statement");
        //SQLの実行準備成功
        //変数のバインド（商品番号,商品名,カテゴリ,値段）
        $stmt -> bind_param("sssis",$image_name,$_POST["date"],$_POST["category"],$_POST["money"],$_POST["name"]);
        //SQLの実行
        $stmt -> execute();
        error_log("execute ps");
        $mysql->commit();
        $stmt -> close();
    }
    //$instance -> close();
}

// 削除
function delete($mysql) {
    error_log('delete');
    //var_dump($_POST);
    $sql = "DELETE FROM PRODUCT WHERE NUM = ? ";

    $num = $_POST["num"];
    //$_POST["name"];

    //SQLの実行準備
    if($stmt = $mysql -> prepare($sql)){
        //SQLの実行準備成功
        //変数のバインド（商品番号,商品名,カテゴリ,値段）
        $stmt -> bind_param("s",$num);
        //SQLの実行
        $stmt -> execute();
        if($stmt->affected_rows == 1){
            //更新成功コミット処理
            $mysql->commit();
        }else{
            //更新失敗
            $mysql -> rollback();
            //$_SESSION["errmessage"] = "商品情報新登録ができませんでした";
        }
    }    
}

function uploadImage(array $file)
{
    // 画像のファイル名から拡張子を取得（例：.png）
    $image_extension = strrchr($file['name'], '.');
 
    // 画像のファイル名を作成（YmdHis: 2021-01-01 00:00:00 ならば 20210101000000）
    $image_name = date('YmdHis') . $image_extension;
 
    // 保存先のディレクトリ
    $directory = './imgs/';
 
    // 画像のパス
    $image_path = $directory . $image_name;
 
    // 画像を設置
    move_uploaded_file($file['tmp_name'], $image_path);
 
    error_log('move ok');
    // 画像ファイルの場合->ファイル名をreturn
    if (exif_imagetype($image_path)) {
        return $image_path;
    }
 
    // 画像ファイル以外の場合
    error_log('This file is not an image file.');
    exit;
}

// mysqlコネクションをクローズ
$instance->close();

?>

<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.5.0/main.min.css">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.5.0/main.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
        <link rel="stylesheet" href="moneykeeps.css">
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">



        <title>moneykeeps</title>
    </head>
    <body>
        <div class="header">moneykeeps</div>
        <div class="contents">
            <div class="list">
                <ul>
                    <li><a href="#" class="text-blue">input</li>
                    <li><a href="#" class="text-blue">graph</li>
                    <li><a href="#" class="text-blue">setting</li>
                </ul>
            </div>
            <div id="calendar" ></div>
            <div id="msgarea" class="form-group mb-3">
                <form action="./moneykeeps.php" method="post" enctype="multipart/form-data" name="moneykeeps">
                    <div class="container">
                        <input type="hidden" name="num" id="num" value="">
                        <input type="hidden" name="prev_photo" id="prev_photo" value="">
                            <div class="col-xs-2">
                                    <h2>input<h2>
                                <br>
                                <input type="text" class="form-control" name="date" placeholder="日付" value="" id="date">
                                <br>
                                <input type="text" class="form-control" name="name" placeholder="名前" value="" id="name">
                                <br>
                                <input type="text" class="form-control" name="money" placeholder="値段" value="" id="money">
                                <br>
                            
                                <select name="category" id="category" class="form-select form-select-sm">
                                <option value="1">食費</option>
                                    <option value="2">外食費</option>
                                    <option value="3">日用品</option>
                                    <option value="4">交通費</option>
                                    <option value="5">衣服</option>
                                    <option value="6">交際費</option>
                                    <option value="7">趣味</option>
                                    <option value="8">その他</option>
                                </select><br>
                            
                            </div>

                            <div class="col-xs-1">
                                <input type="file" name="photo" class="form-control" id="photo" onchange="changeImage()">
                            </div>
                            <div id="imgcampus"></div>
                            <br>
                        

                            <input type="hidden" name="operation" value="" id="operation">
                            <input type="button" class="btn btn-info btn-sm" id="submitbutton" value="新規作成" onclick="createMoneykeeps()">
                            <input type="button" class="btn btn-outline-danger btn-sm" id="deletebutton" value="消去" onclick="submit_('delete')">
                            <br>
                    </div>
                </form>
            </div>
        </div>
        <script>
            const meals = [
            <?php foreach($meals as $index => $meal) { ?>
                {
                    id: "<?php echo $index; ?>",
                    start: "<?php echo $meal[1] . ' ' . ($meal[2] == 1 ? '07:00:00' : ($meal[2] == 2 ? '12:00:00' : ($meal[2] == 3 ? '19:00:00' : '15:00:00'))); ?>",
                    //start: "<?php echo $meal[1]; ?>",
                    end: "<?php echo $meal[1] . ' ' . ($meal[2] == 1 ? '08:00:00' : ($meal[2] == 2 ? '13:00:00' : ($meal[2] == 3 ? '20:00:00' : '16:00:00'))); ?>",
                    groupId: "<?php echo $meal[2]; ?>",
                    title: "<?php echo $meal[4]; ?>",
                    money: "<?php echo $meal[3]; ?>",
                    description: "<?php echo $meal[0]; ?>",
                    allDay: false,
                },
            <?php } ?>
            ];

            // カレンダーの表示
            let calendarEl = document.getElementById('calendar');
            let calendar = new FullCalendar.Calendar(calendarEl, {
                headerToolbar: {
                    left: "dayGridMonth,listMonth",
                    center: "title",
                    right: "prev,next"
                },
                events: meals,
                dateClick: function(date, allDay, jsEvent, view) {
                    console.log('new');
                    document.getElementById("num").value = "";
                    document.getElementById("photo").value = "";
                    document.getElementById("date").value = date.dateStr;
                    document.getElementById("name").value = "";
                    document.getElementById("money").value = "";
                    document.getElementById("category").value = "1";
                    var b_obj = document.getElementById("submitbutton");
                    b_obj.value = "新規作成"
                    b_obj.onclick = "";
                    b_obj.className = "btn btn-info btn-sm";
                    b_obj.addEventListener("click", createMoneykeeps);
                    var d_obj = document.getElementById("deletebutton");
                    d_obj.style.display = "none";
                    var div_obj = document.getElementById("imgcampus");
                    div_obj.innerHTML = "";

                },
                eventClick: function(info) {
                    console.log('event');
                    document.getElementById("num").value = info.event.id;
                    document.getElementById("date").value = (info.event.startStr).split('T')[0];
                    document.getElementById("name").value = info.event.title;
                    document.getElementById("money").value = info.event.extendedProps.money;
                    document.getElementById("category").value = info.event.groupId;
                    document.getElementById("photo").value = "";

                    // 更新ボタンの表示
                    var b_obj = document.getElementById("submitbutton");
                    b_obj.value = "更新"
                    b_obj.onclick = "";
                    b_obj.className="btn btn-outline-info btn-sm";
                    b_obj.addEventListener("click", updateMoneykeeps);
                    // 削除ボタンの表示
                    var d_obj = document.getElementById("deletebutton");
                    d_obj.style.display = "block";
                    // 写真の表示
                    if (info.event.description != "") {
                        var div_obj = document.getElementById("imgcampus");
                        div_obj.innerHTML = "";
                        var new_img = document.createElement("img");
                        new_img.src = info.event.extendedProps.description;
                        document.getElementById("prev_photo").value = info.event.extendedProps.description;
                        new_img.width = 280;
                        div_obj.appendChild(new_img);
                    } else {
                        var div_obj = document.getElementById("imgcampus");
                        div_obj.innerHTML = "";
                    }

                },
            });

            // ボタン押すメソッド
            function createMoneykeeps() {
                submit_moneykeeps('create');
            }
            function updateMoneykeeps() {
                submit_moneykeeps('update');
            }
            // valueに日本語入れたいから．operationをhiddenにしてパラメータとして送るように変更
            function submit_moneykeeps(op) {
                opobj = document.getElementById('operation');
                opobj.value = op;
                document.moneykeeps.submit();
            }
            // 画像選択時の処理
            function changeImage() {
                document.getElementById('imgcampus').innerHTML = "";
            }

            // カレンダー
            calendar.render();
            //function to calculate window height
            function get_calendar_height() {
                return $(window).height() - 30;
            }

            // デフォルトでは削除ボタンを非表示
            var d_obj = document.getElementById("deletebutton");
            d_obj.style.display = "none";

        </script>
    </body>
</html>
