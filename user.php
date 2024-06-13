<?php
////////////////////////////////////////////////////////////////
// 準備
////////////////////////////////////////////////////////////////

// データベースへのログイン情報
$dsn = "mysql:host=localhost; dbname=joblisting; charset=utf8";
$user = "testuser";
$pass = "testpass";

echo '<link rel="stylesheet" type="text/css" href="style.css">';


////////////////////////////////////////////////////////////////
// 本処理
////////////////////////////////////////////////////////////////

// データをmanager.htmlから受け取る
$input = [];
if (isset($_POST)) {
    $input += $_POST;
}

// DBに接続する
try {
    $block = "";
    $fh2 = fopen('user.html', "r");
    $fs2 = filesize('user.html');
    $top = fread($fh2, $fs2);
    fclose($fh2);
    $top = str_replace("!block!", $block, $top);
    // topをグローバルして、displayのみechoする、ほかの関数はtopを取ってくる

    $dbh = new PDO($dsn, $user, $pass);
    if (isset($input["mode"])) {
        if ($input["mode"] == "search") {
            search();
        } else if ($input["mode"] == "narabikae") {
            narabikae();
        } else if ($input["mode"] == "favorite") {
            favorite_register();
        } else if ($input["mode"] == "favodele") {
            favorite_delete();
        }
    }
    // else{
    favorite_display();
    display();
    // }

} catch (PDOException $e) {
    echo "接続失敗..." . $e->getMessage();
}

////////////////////////////////////////////////////////////////
// 関数
////////////////////////////////////////////////////////////////

// お気に入り登録
function favorite_register()
{
    // 関数内でも変数で使えるようにする
    global $dbh;
    global $input;

    // sql文を書く
    $sql = <<<sql
    update job set flag = 1 where id = ?;
    sql;

    // 実行する
    $stmt = $dbh->prepare($sql);
    $stmt->bindParam(1, $input["id"]);
    $stmt->execute();
}

// お気に入り表示
function favorite_display()
{
    // 関数内でも変数を使えるようにする
    global $dbh;
    global $block;
    global $top;

    // sql文を書く
    $sql = <<<sql
    select * from job where flag = 1;
    sql;

    // 実行する
    $stmt = $dbh->prepare($sql);
    $stmt->execute();

    // テンプレートファイルの読み込み
    $fh = fopen('tmpl/favorite.tmpl', "r");
    $fs = filesize('tmpl/favorite.tmpl');
    $insert_tmpl = fread($fh, $fs);
    fclose($fh);

    // 繰り返してすべての行を取ってくる
    while ($row = $stmt->fetch()) {
        // 差し込み用テンプレートを初期化する
        $insert = $insert_tmpl;

        // 値を変数に入れなおす
        $id = $row["id"];
        $shop = $row["店名"];
        $catchcopy = $row["キャッチコピー"];
        $job = $row["職種"];
        $station = $row["最寄り駅"];
        $money = $row["時給"];

        // テンプレートファイルの文字置き換え
        $insert = str_replace("!id!", $id, $insert);
        $insert = str_replace("!店名!", $shop, $insert);
        $insert = str_replace("!キャッチコピー!", $catchcopy, $insert);
        $insert = str_replace("!職種!", $job, $insert);
        $insert = str_replace("!最寄り駅!", $station, $insert);
        $insert = str_replace("!時給!", $money, $insert);

        // index.htmlに差し込む変数に格納する
        $block .= $insert;
    }

    // ファイルの読み込み
    // $fh2 = fopen('user.html', "r");
    // $fs2 = filesize('user.html');
    // $top = fread($fh2, $fs2);
    // fclose($fh2);

    // index.htmlの置き換え
    $top = str_replace("現在お気に入りはありません。", $block, $top);
    // echo $top;
}

// お気に入り削除
function favorite_delete()
{
    // 関数内でも変数で使えるようにする
    global $dbh;
    global $input;

    // sql文を書く
    $sql = <<<sql
    update job set flag = 0 where id = ?
    sql;

    // 実行する
    $stmt = $dbh->prepare($sql);
    $stmt->bindParam(1, $input["id"]);
    $stmt->execute();
    if (!$stmt->execute()) {
        // エラーが発生した場合の処理
        print_r($stmt->errorInfo()); // エラー情報を表示
        exit(); // プログラムの実行を停止
    }
}

//並び替え検索
function narabikae()
{
    // 関数内でも変数で使えるようにする
    global $dbh;
    global $block;
    global $input;
    global $top;

    if (isset($input["narabikae"])) {
        if ($input["narabikae"] == "新着順") {
            $sql = <<<sql
            select * from job where flag in (0,1) order by id desc;
            sql;
        } else if ($input["narabikae"] == "時給順") {
            $sql = <<<sql
            select * from job where flag in (0,1) order by 時給 desc;
            sql;
        } else if ($input["narabikae"] == "---") {
            $sql = <<<sql
            select * from job where flag in (0,1);
            sql;
        }
        // 実行する
        $stmt = $dbh->prepare($sql);
        $stmt->execute();

        // テンプレートファイルの読み込み
        $fh = fopen('tmpl/user.tmpl', "r");
        $fs = filesize('tmpl/user.tmpl');
        $insert_tmpl = fread($fh, $fs);
        fclose($fh);

        // 繰り返してすべての行を取ってくる
        while ($row = $stmt->fetch()) {
            // 差し込み用テンプレートを初期化する
            $insert = $insert_tmpl;

            // 値を変数に入れなおす
            $id = $row["id"];
            $shop = $row["店名"];
            $catchcopy = $row["キャッチコピー"];
            $job = $row["職種"];
            $station = $row["最寄り駅"];
            $money = $row["時給"];

            // テンプレートファイルの文字置き換え
            $insert = str_replace("!id!", $id, $insert);
            $insert = str_replace("!店名!", $shop, $insert);
            $insert = str_replace("!キャッチコピー!", $catchcopy, $insert);
            $insert = str_replace("!職種!", $job, $insert);
            $insert = str_replace("!最寄り駅!", $station, $insert);
            $insert = str_replace("!時給!", $money, $insert);

            // index.htmlに差し込む変数に格納する
            $block .= $insert;
        }
        $fh2 = fopen('user.html', "r");
        $fs2 = filesize('user.html');
        $top = fread($fh2, $fs2);
        fclose($fh2);

        // index.htmlの置き換え
        $top = str_replace("!block!", $block, $top);
        // echo $top;
    }
}

// 検索
function search()
{
    // 関数内でも変数で使えるようにする
    global $dbh;
    global $block;
    global $input;
    global $top;

    if ($input["search"] == "全て") {
        // sql文を書く
        $sql = <<<sql
            select * from job where flag in (0,1);
            sql;

        // 実行する
        $stmt = $dbh->prepare($sql);
        $stmt->execute();
    } else {

        // sql文を書く
        $sql = <<<sql
            select * from job where 職種 = ? and flag in (0,1);
            sql;

        // 実行する
        $stmt = $dbh->prepare($sql);
        $stmt->bindParam(1, $input["search"]);
        $stmt->execute();
    }
    // テンプレートファイルの読み込み
    $fh = fopen('tmpl/user.tmpl', "r");
    $fs = filesize('tmpl/user.tmpl');
    $insert_tmpl = fread($fh, $fs);
    fclose($fh);

    // 繰り返してすべての行を取ってくる
    while ($row = $stmt->fetch()) {
        // 差し込み用テンプレートを初期化する
        $insert = $insert_tmpl;

        // 値を変数に入れなおす
        $id = $row["id"];
        $shop = $row["店名"];
        $catchcopy = $row["キャッチコピー"];
        $job = $row["職種"];
        $station = $row["最寄り駅"];
        $money = $row["時給"];

        // テンプレートファイルの文字置き換え
        $insert = str_replace("!id!", $id, $insert);
        $insert = str_replace("!店名!", $shop, $insert);
        $insert = str_replace("!キャッチコピー!", $catchcopy, $insert);
        $insert = str_replace("!職種!", $job, $insert);
        $insert = str_replace("!最寄り駅!", $station, $insert);
        $insert = str_replace("!時給!", $money, $insert);

        // index.htmlに差し込む変数に格納する
        $block .= $insert;
    }
    $fh2 = fopen('user.html', "r");
    $fs2 = filesize('user.html');
    $top = fread($fh2, $fs2);
    fclose($fh2);

    // index.htmlの置き換え
    $top = str_replace("!block!", $block, $top);
    // echo $top;
}

// 現在のタスク一覧表示処理
function display()
{
    // 関数内でも変数を使えるようにする
    global $dbh;
    global $block;
    global $top;

    // sql文を書く
    $sql = <<<sql
    select * from job where flag in (0,1);
    sql;

    // 実行する
    $stmt = $dbh->prepare($sql);
    $stmt->execute();

    // テンプレートファイルの読み込み
    $fh = fopen('tmpl/user.tmpl', "r");
    $fs = filesize('tmpl/user.tmpl');
    $insert_tmpl = fread($fh, $fs);
    fclose($fh);

    // 繰り返してすべての行を取ってくる
    while ($row = $stmt->fetch()) {
        // 差し込み用テンプレートを初期化する
        $insert = $insert_tmpl;

        // 値を変数に入れなおす
        $id = $row["id"];
        $shop = $row["店名"];
        $catchcopy = $row["キャッチコピー"];
        $job = $row["職種"];
        $station = $row["最寄り駅"];
        $money = $row["時給"];

        // テンプレートファイルの文字置き換え
        $insert = str_replace("!id!", $id, $insert);
        $insert = str_replace("!店名!", $shop, $insert);
        $insert = str_replace("!キャッチコピー!", $catchcopy, $insert);
        $insert = str_replace("!職種!", $job, $insert);
        $insert = str_replace("!最寄り駅!", $station, $insert);
        $insert = str_replace("!時給!", $money, $insert);

        // index.htmlに差し込む変数に格納する
        $block .= $insert;
    }

    // // ファイルの読み込み
    // $fh2 = fopen('user.html', "r");
    // $fs2 = filesize('user.html');
    // $top = fread($fh2, $fs2);
    // fclose($fh2);

    // index.htmlの置き換え
    // $top = str_replace("!block!", $block, $top);
    echo $top;
}
