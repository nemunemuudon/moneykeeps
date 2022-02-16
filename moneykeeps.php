<?php
define("DB_HOST","localhost");
define("DB_USER","root");
define("DB_PASS","root");
define("DB_NAME","MONEYKEEPS");
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
        $num = $row['NUM'];
        $date = $row['DATE'];
        $name = $row['NAME'];
        $money = $row['MONEY'];
        $category = $row['CATEGORY'];
        $photo = $row['PHOTO'];
        $memo = $row['MEMO'];
        $meals += [ "$num" => ["$date", "$name", "$money", "$category", "$photo" , "$memo"]];
    }
}

// 更新
function update($mysql) {
    if (isset($_FILES['name']) && is_uploaded_file($_FILES['name']['tmp_name'])) {
        error_log('upload');
        $image_name = uploadImage($_FILES['name']);
        error_log('upload ok');
    } else {
        $image_name =  $_POST['prev_photoname'];
    }

    // ===== 更新処理 =====
    $sql = "UPDATE PRODUCT SET DATE = ?, NAME = ?, MONEY = ?, CATEGORY = ?, PHOTO =? , MEMO = ? where num = ?";
    $_POST["name"];

    if($stmt = $mysql -> prepare($sql)){
        error_log("call prepared statement");
        //SQLの実行準備成功
        //変数のバインド（商品番号,商品名,カテゴリ,値段）
        $stmt -> bind_param("ssisssi",$image_name,$_POST["date"],$_POST["category"],$_POST["money"],$_POST["name"], $_POST['num'],$_POST['MEMO']);
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
    if (isset($_FILES['PHOTO']) && is_uploaded_file($_FILES['PHOTO']['tmp_name'])) {
        error_log('upload');
        $image_name = uploadImage($_FILES['PHOTO']);
        error_log('upload ok');
    }

    $sql = "INSERT INTO PRODUCT(date,name,money,category,photo,memo) VALUES(?,?,?,?,?,?)";
    //$_POST["name"];
    /*<p class="text-red-600"><?= $errmessage?></p>*/
    if($stmt = $mysql -> prepare($sql)){
        error_log("call prepared statement");
        //SQLの実行準備成功
        //変数のバインド（商品番号,商品名,カテゴリ,値段）
        $stmt -> bind_param("ssisss",$image_name,$_POST["date"],$_POST["name"],$_POST["money"],$_POST["category"],$_POST["photo"],$_POST["memo"]);
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
    $sql = "DELETE FROM PHOTO WHERE PHOTONUM = ? ";

    $photonum = $_POST["photonum"];
    //$_POST["name"];

    //SQLの実行準備
    if($stmt = $mysql -> prepare($sql)){
        //SQLの実行準備成功
        //変数のバインド（商品番号,商品名,カテゴリ,値段）
        $stmt -> bind_param("s",$photonum);
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
        <link rel="stylesheet" href="moneykeeps.css">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.5.0/main.min.css">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.5.0/main.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
        <link href="https://fonts.googleapis.com/css?family=Amatic+SC:700 rel="stylesheet">        <title>moneykeeps</title>
    </head>
    <body>
        <div class="header">moneykeeps</div>
        <div class="contents">
            <div class="list">
                <ul>
                    <li><a href="#">入力</a></li>
                    <li><a href="#">グラフ</a></li>
                </ul>
            </div>
        </div>
        <div class="contents">
            <div id="calendar" ></div>
        </div>
        <div class="contents">
            <div id="msgarea" class="form-group mb-3">
                <form action="./gohan.php" method="post" enctype="multipart/form-data" name="gohan">
                <div class="container">
                <input type="hidden" name="photonum" id="photonum" value="">
                <input type="hidden" name="prev_photoname" id="prev_photoname" value="">
                    <div class="col-xs-2">
                    <br>
                    <input type="text" class="form-control" name="date" placeholder="日付" value="" id="date">
                    <br>
                    <input type="text" class="form-control" name="name" placeholder="商品名" value="" id="name">
                    <br>
                    <input type="text" class="form-control" name="calory" placeholder="" value="" id="calory">
                    <br>
                    <select name="category" id="category" class="form-select form-select-sm">
                        <option value="1">朝ごはん</option>
                        <option value="2">昼ごはん</option>
                        <option value="3">夜ごはん</option>
                        <option value="4">間食</option>
                    </select><br>
                    </div>

                    <div class="col-xs-1">
                        <input type="file" name="photoname" class="form-control" id="photoname" onchange="changeImage()">
                    </div>
                    <div id="imgcampus"></div>
                    <br>

                    <input type="hidden" name="operation" value="" id="operation">
                    <input type="button" class="btn btn-info btn-sm" id="submitbutton" value="新規作成" onclick="createGohan()">
                    <input type="button" class="btn btn-outline-danger btn-sm" id="deletebutton" value="消去" onclick="submit_gohan('delete')">
                    <br>
                </div>
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
                    calory: "<?php echo $meal[3]; ?>",
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
                    document.getElementById("photonum").value = "";
                    document.getElementById("photoname").value = "";
                    document.getElementById("date").value = date.dateStr;
                    document.getElementById("name").value = "";
                    document.getElementById("calory").value = "";
                    document.getElementById("category").value = "1";
                    var b_obj = document.getElementById("submitbutton");
                    b_obj.value = "新規作成"
                    b_obj.onclick = "";
                    b_obj.className = "btn btn-info btn-sm";
                    b_obj.addEventListener("click", createGohan);
                    var d_obj = document.getElementById("deletebutton");
                    d_obj.style.display = "none";
                    var div_obj = document.getElementById("imgcampus");
                    div_obj.innerHTML = "";

                },
                eventClick: function(info) {
                    console.log('event');
                    document.getElementById("photonum").value = info.event.id;
                    document.getElementById("date").value = (info.event.startStr).split('T')[0];
                    document.getElementById("name").value = info.event.title;
                    document.getElementById("calory").value = info.event.extendedProps.calory;
                    document.getElementById("category").value = info.event.groupId;
                    document.getElementById("photoname").value = "";

                    // 更新ボタンの表示
                    var b_obj = document.getElementById("submitbutton");
                    b_obj.value = "更新"
                    b_obj.onclick = "";
                    b_obj.className="btn btn-outline-info btn-sm";
                    b_obj.addEventListener("click", updateGohan);
                    // 削除ボタンの表示
                    var d_obj = document.getElementById("deletebutton");
                    d_obj.style.display = "block";
                    // 写真の表示
                    if (info.event.description != "") {
                        var div_obj = document.getElementById("imgcampus");
                        div_obj.innerHTML = "";
                        var new_img = document.createElement("img");
                        new_img.src = info.event.extendedProps.description;
                        document.getElementById("prev_photoname").value = info.event.extendedProps.description;
                        new_img.width = 280;
                        div_obj.appendChild(new_img);
                    } else {
                        var div_obj = document.getElementById("imgcampus");
                        div_obj.innerHTML = "";
                    }

                },
            });

            // ボタン押すメソッド
            function createGohan() {
                submit_gohan('create');
            }
            function updateGohan() {
                submit_gohan('update');
            }
            // valueに日本語入れたいから．operationをhiddenにしてパラメータとして送るように変更
            function submit_gohan(op) {
                opobj = document.getElementById('operation');
                opobj.value = op;
                document.gohan.submit();
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
