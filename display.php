<?php

date_default_timezone_set('Asia/Tokyo');

require("../db.php");

$pdo = connectDB();

if (!empty($_GET['address'])) {
    $address = $_GET['address'];
    // イベントの取得
    $sql = $pdo -> prepare('SELECT * FROM events WHERE address = :address');
    $sql -> bindParam(':address', $address, PDO::PARAM_STR);
    $is_connected = $sql -> execute();
    if ($is_connected) {
        $event = $sql -> fetch();
        if (!empty($event)) {
            $event_id = $event['id'];
            $event_name = $event['name'];
            $event_memo = $event['memo'];
        } else {
            $event_id = '';
            $event_name = '';
            $event_memo = '';
            header('Location: create.php');
        }
    }
}

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
        // echo $insert_sql_statement;
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

if (!empty($_POST['new_group_name'])) {
    // グループを新規作成
    $sql = $pdo -> prepare('INSERT INTO `groups` SET name = :name, event_id = :id');
    $sql -> bindParam(':name', $_POST['new_group_name'], PDO::PARAM_STR);
    $sql -> bindParam(':id', $event_id, PDO::PARAM_INT);    
    $sql -> execute();
    header("Location: ./display.php?address={$address}");
}

if (!empty($_POST['delete_id'])) {
    // 削除対象の人のデータをpersons_groupsテーブルから削除
    $sql = $pdo -> prepare('DELETE FROM persons_groups WHERE person_id = :person_id');
    $sql -> bindParam(':person_id', $_POST['delete_id'], PDO::PARAM_INT);
    $sql -> execute();    
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

if (!empty($_POST['delete_group_id'])) {
    // 削除対象のグループのデータをpersons_groupsテーブルから削除
    $sql = $pdo -> prepare('DELETE FROM persons_groups WHERE group_id = :group_id');
    $sql -> bindParam(':group_id', $_POST['delete_group_id'], PDO::PARAM_INT);
    $sql -> execute();
    // 削除対象の人のデータをgroupsテーブルから削除
    $sql = $pdo -> prepare('DELETE FROM `groups` WHERE id = :id');
    $sql -> bindParam(':id', $_POST['delete_group_id'], PDO::PARAM_INT);
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

// グループの取得
$sql = $pdo -> prepare('SELECT * FROM `groups` WHERE event_id = :event_id');
$sql -> bindParam(':event_id', $event_id, PDO::PARAM_INT);
$sql -> execute();
$groups = $sql -> fetchAll();

// 人がグループに属しているかの情報を取得
$person_id_list = array();
foreach ($persons as $person) {
    $person_id_list[] = $person['id'];
}
$person_sql = implode(', ', $person_id_list);
$sql = $pdo -> prepare("SELECT * FROM persons_groups WHERE person_id IN ({$person_sql})");
$sql -> execute();
// $sql = $pdo -> prepare('SELECT persons_dates.* FROM persons_dates JOIN persons ON persons_dates.person_id = persons.id JOIN dates ON persons_dates.date_id = dates.id WHERE persons.event_id = :id ORDER BY persons_dates.date_id, persons_dates.person_id');
// $sql -> bindParam(':id', $event_id, PDO::PARAM_INT);
// $sql -> execute();
$persons_groups = $sql -> fetchAll(PDO::FETCH_ASSOC);



// 日程調整の情報を表示するために整形
foreach ($results as $person_date) {
    $date_id = $person_date['date_id'];
    $person_id = $person_date['person_id'];
    $status = $person_date['status'];
    $id = $person_date['id'];
    $display_results[$date_id][$person_id] = [
        'status' => $status,
        'id' => $id
    ];
}

// グループの情報を表示するために整形
$display_persons_groups = array();
foreach ($persons_groups as $person_group) {
    $group_id = $person_group['group_id'];
    $person_id = $person_group['person_id'];
    $status = $person_group['status'];
    $id = $person_group['id'];
    $display_persons_groups[$group_id][$person_id] = [
        'status' => $status,
        'id' => $id
    ];
}
// var_dump($display_persons_groups);
var_dump($_POST);
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
            <a class='edit-link' id='editLink' data-edit-id='<?php echo $event_id; ?>' href="#">予定を編集する</a>
            <a class='delete-link' id='deleteLink' data-edit-id='<?php echo $event_id; ?>' href="#">予定を削除する</a>
        </header>

        <main>

            <h1><?php echo $event_name; ?></h1>

            <?php if (!empty($event_memo)) : ?>
            <div class='content memo'><?php echo $event_memo; ?></div>
            <?php endif; ?>

            <div class='content detail'>
                <p>「o」「x」の欄を押すと、ステータスを編集することができます。</p>
    
                <p>保存ボタンを押すと、ステータスを保存することができます。</p>
    
                <p>表の上をスクロールすると、続きを表示できます。</p>
            </div>
        
            <div class='content form'>
                <form action="" method='post' name="events">
                    <div class='scroll'>
                        <table>
                            <!-- 先頭の行、人の名前が表示 -->
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
                            <!-- 日程とその人の予定（○×など）が表示 -->
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
                            <!-- 削除ボタンと新規作成ボタンが表示 -->
                            <tr>
                                <td class='display-date'>   </td>
                                <?php foreach ($persons as $person): ?>
                                <td>
                                    <button type='button' class='deletePersons' data-person-name='<?php echo $person['name']; ?>' data-person-id='<?php echo $person["id"]; ?>'>削除</button>
                                </td>
                                <?php endforeach; ?>
                                <td class='display-new-person'>
                                    <input type="submit" class='add-person-button' name="new_person" id="" value='新規参加する' onclick="return validateNewPerson()">
                                </td>
                            </tr>
                        </table>
                    </div>
    
                    <input type="submit" class='submit-button' name='update_status' value='保存'>
                </form>
                <form action="" method='post'>
                    <div>
                        <label>
                            グループごとの予定を開く
                            <select name="display_group" disabled>
                                <?php foreach ($groups as $group): ?>
                                <option value="all">全体</option>
                                <option value="<?php echo $group['id']; ?>"><?php echo $group['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <input type="submit" value='開く'>
                    </div>
                </form>
            </div>

            <div class='content'>
                <h1>グループの管理</h1>

                <div class='scroll'>
                    
                    <table>
                        <!-- 先頭の行、人の名前が表示 -->
                        <tr>
                            <th class='display-date'></th>
                            <?php foreach ($persons as $person): ?>
                            <th class='display-person'><?php echo $person['name']; ?></th>
                            <?php endforeach; ?>
                            <th></th>
                        </tr>
                        <!-- グループ名と、その人がそのグループに属しているかを表示 -->
                        <?php foreach ($groups as $group): ?>
                        <tr>
                            <td class='display-date'><?php echo $group['name']; ?></td>
                            <?php foreach ($persons as $person): ?>
                            <?php
                            if (!empty($display_persons_groups[$group['id']][$person['id']])) {
                                $display = $display_persons_groups[$group['id']][$person['id']]['status'];
                                $id = $display_persons_groups[$group['id']][$person['id']]['id'];
                            } else {
                                $display = '-';
                                $id = '';
                            }
                            ?>
                            <td class='groupStatus status-css'>
                                <form action="" method='post'>
                                    <?php echo $display; ?>
                                    <input type="hidden" name='edit_persons_groups_id' value='<?php echo $id; ?>'>
                                    <input type="hidden" name='edit_persons_groups_person_id' value='<?php echo $person['id']; ?>'>
                                    <input type="hidden" name='edit_persons_groups_group_id' value='<?php echo $group['id']; ?>'>
                                    <input type="hidden" name='edit_persons_groups_status' value='<?php echo $display; ?>'>
                                </form>
                            </td>
                            <?php endforeach; ?>
                            <form action="" method='post'>
                                <td>
                                    <input type="hidden" name='delete_group_id' value='<?php echo $group['id']; ?>'>
                                    <input type="submit" value='削除'>
                                </td>
                            </form>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <form action="" method='post'>
                    <div>
                        <label>
                            グループの新規作成
                            <input type="text" name="new_group_name" class='new-group-name'>
                        </label>
                        <input type="submit" value='新規作成'>
                    </div>
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

    <script src="js/display2.js"></script>

</html>
