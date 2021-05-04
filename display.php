<?php

date_default_timezone_set('Asia/Tokyo');

require("../db.php");

$pdo = connectDB();

if (!empty($_GET['address'])) {
    $address = $_GET['address'];
    // イベントの取得
    $sql = $pdo -> prepare('SELECT id FROM events WHERE address = :address');
    $sql -> bindParam(':address', $address, PDO::PARAM_STR);
    $sql -> execute();
    $event_id = $sql -> fetch();
}

// $event_id = 1;

// var_dump($_POST['test']);

if (!empty($_POST['new_person'])) {
    // 新規参加者の情報を登録
    if (empty($_POST['new_person_name'])) {
        // onclickで判定をしているので、ここに来ることはない
    } else {
        // 新規参加者の名前を登録
        $sql = $pdo -> prepare('INSERT INTO persons SET event_id = :event_id, `name` = :name');
        $sql -> bindParam(':event_id', $event_id, PDO::PARAM_INT);
        $sql -> bindParam(':name', $_POST['new_person_name'], PDO::PARAM_STR);
        $sql -> execute();
        $person_id = $pdo->lastInsertId();
        
        // 新規参加者の参加可否の情報を登録
        $insert_sql_statement = "INSERT INTO persons_dates (person_id, date_id, status) VALUES ";
        $insert_sql_statement_values = array();
        foreach ($_POST['new_status'] as $date_id => $status) {
            $insert_sql_statement_values[] = "({$person_id}, {$date_id}, '{$status}')";
        }
        $insert_sql_statement .= implode(", ", $insert_sql_statement_values);
        echo $insert_sql_statement;
        $sql = $pdo -> prepare($insert_sql_statement);
        $sql -> execute();
        // リロード
        header("Location: ./display.php?address={$address}");
    }
}

if (!empty($_POST['update_status'])) {
    if (empty($_POST['status'])) {

    } else {
        // 編集した予定情報を保存するためのSQL文を作成
        $update_id_list = array();
        $update_sql_statement = "UPDATE persons_dates SET `status` = CASE id ";
        foreach ($_POST['status'] as $id => $status) {
            $update_sql_statement .= "WHEN {$id} THEN '{$status}' ";
            $update_id_list[] = $id;
        }
        $update_sql_statement .= "END ";
        $update_sql_statement .= "WHERE id IN (";
        $update_sql_statement .= implode(",", $update_id_list);
        $update_sql_statement .= ")";
        // SQL文のイメージ：
        // UPDATE persons_dates SET `status` = 
        //     CASE id
        //         WHEN 1 THEN o 
        //         WHEN 2 THEN x
        //     END
        // WHERE id IN (1,2)
    
        // 編集した予定情報を保存
        $sql = $pdo -> prepare($update_sql_statement);
        $sql -> execute();
        // リロード
        header("Location: ./display.php?address={$address}");
    }
}

if (!empty($_POST['delete_id'])) {
    // 削除対象の人のデータをpersons_datesテーブルから削除
    $sql = $pdo -> prepare('DELETE FROM persons_dates WHERE person_id = :person_id');
    $sql -> bindParam(':person_id', $_POST['delete_id'], PDO::PARAM_INT);
    $sql -> execute();
    // 削除対象の人のデータをpersonsテーブルから削除
    $sql = $pdo -> prepare('DELETE FROM persons WHERE id = :id');
    $sql -> bindParam(':id', $_POST['delete_id'], PDO::PARAM_INT);
    $sql -> execute();
    // リロード
    header("Location: ./display.php?address={$address}");
}

// イベントの日時の情報を取得
$sql = $pdo -> prepare('SELECT id, `date` FROM dates WHERE event_id = :id');
$sql -> bindParam(':id', $event_id, PDO::PARAM_INT);
$sql -> execute();
$dates = $sql -> fetchAll();

// イベントに参加する人の情報を取得
$sql = $pdo -> prepare('SELECT id, comment, `name` FROM persons WHERE event_id = :id');
$sql -> bindParam(':id', $event_id, PDO::PARAM_INT);
$sql -> execute();
$persons = $sql -> fetchAll();

// 日程に対する人の参加可否の情報を取得（例：aaaさんは4/2に参加可能）
$sql = $pdo -> prepare('SELECT persons_dates.* FROM persons_dates JOIN persons ON persons_dates.person_id = persons.id JOIN dates ON persons_dates.date_id = dates.id WHERE persons.event_id = :id ORDER BY persons_dates.date_id, persons_dates.person_id');
$sql -> bindParam(':id', $event_id, PDO::PARAM_INT);
$sql -> execute();
$results = $sql -> fetchAll();
// var_dump($results);

// 日程調整の情報を表示するために整形
foreach ($results as $key => $result) {
    $display_results[$result['date_id']][$result['person_id']] = [
        'status' => $result['status'],
        'id' => $result['id']
    ];
}
// var_dump($display_results);
?>

<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>日程調整アプリ</title>
        <link rel="stylesheet" href="css/display.css">
        <link rel="stylesheet" href="css/styles.css">

    </head>
    <body>
        <header>
            <h1 class='title'>日程調整アプリ</h1>
            <a class='create-link' href="create.php">予定を作る</a>
            <a class='edit-link' href="">予定を編集する</a>
            <a class='delete-link' href="">予定を削除する</a>
        </header>

        <main>

            <h1>イベントの表示</h1>

            <div class='content detail'>
                <p>「o」「x」の欄を押すと、ステータスを編集することができます。</p>
    
                <p>保存ボタンを押すと、ステータスを保存することができます。</p>
    
                <p>表の上をスクロールすると、続きを表示できます。</p>
            </div>
        
            <div class='content form'>
                <form action="?address=<?php echo $address; ?>" method='post' name="events">
                    <div class='scroll'>
                        <table>
                            <tr>
                                <th class='display-date'></th>
                                <?php foreach ($persons as $person): ?>
                                <th class='display-person'><?php echo $person['name']; ?></th>
                                <?php endforeach; ?>
                                <th class='display-new-person'>
                                    <!-- 新規登録者の名前入力欄 -->
                                    <input type="text" class='new-person-form' name="new_person_name" id="newPersonForm" placeholder='名前を入力'>
                                </th>
                            </tr>
                            <?php foreach ($dates as $date): ?>
                            <tr>
                                <td class='display-date'><?php echo $date['date']; ?></td>
                                <?php foreach ($persons as $person): ?>
                                <?php
                                $display = $display_results[$date['id']][$person['id']]['status'];
                                $id = $display_results[$date['id']][$person['id']]['id'];
                                ?>
                                <td class='status status-css'>
                                    <span class='status-display'><?php echo $display; ?></span>
                                    <span class='status-pulldown' data-person-date-id='<?php echo $id; ?>' data-person-date-status='<?php echo $display; ?>'>
                                        <!-- ここにプルダウンが入る -->
                                    </span>
                                </td>
                                <?php endforeach; ?>
                                <td class='display-new-person'>
                                    <span class='new-status-pulldown' data-date-id='<?php echo $date['id']; ?>'>
                                        <!-- ここに新規登録者用ののプルダウンが入る -->
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <tr>
                                <td class='display-date'>   </td>
                                <?php foreach ($persons as $person): ?>
                                <td>
                                    <button type='button' class='deletePersons' data-person-name='<?php echo $person['name']; ?>' data-person-id='<?php echo $person["id"]; ?>'>削除</button>
                                </td>
                                <?php endforeach; ?>
                                <td class='display-new-person'><input type="submit" class='add-person-button' name="new_person" id="" value='新規参加する' onclick="return validateNewPerson()"></td>
                            </tr>
                        </table>
                    </div>
    
                    <input type="submit" class='submit-button' name='update_status' value='保存'>
                </form>
            </div>


            <div class='content share-link'>
                <h1>リンクの共有</h1>
                <input type="text" class='link' id='link' value="<?php echo 'http://35.73.113.5/schedules/display.php?address='.$address; ?>">
                <button type='button' class='copy-button' id='copyButton'>コピー</button>
            </div>

        </main>

        <footer></footer>


    </body>

    <script src="js/display.js"></script>

</html>
