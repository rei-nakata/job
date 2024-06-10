<?php
////////////////////////////////////////////////////////////////
// 準備
////////////////////////////////////////////////////////////////

// データベースへのログイン情報
$dsn = "mysql:host=localhost; dbname=joblisting; charset=utf8";
$user = "testuser";
$pass = "testpass";

// テーブル表示処理のために必要な初期化
$block = "";

echo '<link rel="stylesheet" type="text/css" href="style.css">';


////////////////////////////////////////////////////////////////
// 本処理
////////////////////////////////////////////////////////////////

// DBに接続する
try {
    $dbh = new PDO($dsn, $user, $pass);
    display();
} catch (PDOException $e) {
    echo "接続失敗..." . $e->getMessage();
}

////////////////////////////////////////////////////////////////
// 関数
////////////////////////////////////////////////////////////////

// お気に入り登録
function favorite()
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

// 現在のタスク一覧表示処理
function display()
{
    // 関数内でも変数を使えるようにする
    global $dbh;
    global $block;

    // sql文を書く
    $sql = <<<sql
    select * from job where flag = 0;
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

    $fh2 = fopen('tmpl/us.tmpl', "r");
    $fs2 = filesize('tmpl/us.tmpl');
    $top = fread($fh2, $fs2);
    fclose($fh2);

    // index.htmlの置き換え
    $top = str_replace("!block!", $block, $top);
    echo $top;
}
