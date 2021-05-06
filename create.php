<?php
    session_start();

    date_default_timezone_set('Asia/Tokyo');
    
    require("../db.php");

    $pdo = connectDB();

    // var_dump($_POST);

    $length = 15;
    $words_list = 'abcdefghijklmnopqrstuvwxyz0123456789';
    $words_list_split = str_split($words_list);

    // addressに使う文字列を生成する関数
    function get_random_letters($length, $words_list_split) {
        $letter = '';
        for ($letter_index=0; $letter_index < $length; $letter_index++) { 
            $words_list_index = random_int(0, count($words_list_split)-1);
            $letter .= $words_list_split[$words_list_index];
        }
        return $letter;
    }

    // display.phpで削除するリンクを押したとき、データを削除する
    if (!empty($_POST['delete_id'])) {
        $delete_id = $_POST['delete_id'];
        // eventsから削除
        $sql = $pdo -> prepare('DELETE FROM events WHERE id = :id');
        $sql -> bindParam(':id', $delete_id, PDO::PARAM_INT);
        $sql -> execute();

        // personsのデータを取得
        $sql = $pdo -> prepare('SELECT id FROM persons WHERE event_id = :event_id');
        $sql -> bindParam(':event_id', $delete_id, PDO::PARAM_INT);
        $sql -> execute();
        $delete_persons_id_list = $sql -> fetchAll(PDO::FETCH_COLUMN);

        // personsから削除
        $sql = $pdo -> prepare('DELETE FROM persons WHERE event_id = :event_id');
        $sql -> bindParam(':event_id', $delete_id, PDO::PARAM_INT);
        $sql -> execute();

        // datesから削除
        $sql = $pdo -> prepare('DELETE FROM dates WHERE event_id = :event_id');
        $sql -> bindParam(':event_id', $delete_id, PDO::PARAM_INT);
        $sql -> execute();

        // persons-datesから削除
        $delete_persons_id = implode(', ', $delete_persons_id_list);
        $persons_dates_delete_sql_statement = "DELETE FROM persons_dates WHERE person_id IN ({$delete_persons_id})";
        // echo $persons_dates_delete_sql_statement;
        $sql = $pdo -> prepare($persons_dates_delete_sql_statement);
        $sql -> execute();

    }
    

    // 送信されたとき、データを登録する
    if (!empty($_POST['submit'])) {
        $address = get_random_letters($length, $words_list_split);
        // eventsへの登録
        $sql = $pdo -> prepare('INSERT INTO events SET address = :address, name = :name, memo = :memo');
        $sql -> bindParam(':address', $address, PDO::PARAM_STR);
        $sql -> bindParam(':name', $_POST['name'], PDO::PARAM_STR);
        $sql -> bindParam(':memo', $_POST['memo'], PDO::PARAM_STR);
        $sql -> execute();
        $event_id = $pdo -> lastInsertId();

        // datesへの登録のためのsql文を作成
        $dates_insert_sql_statement = 'INSERT INTO dates (event_id, date) VALUES';
        $value_array = array();
        if (!empty($_POST['datetime_list'])) {
            $datetime_list = trim($_POST['datetime_list']);
            $datetime_list = str_replace(array( "\r\n", "\r", "\n" ), "\n", $datetime_list);
            $dates_insert_sql_statement_values = explode("\n", $datetime_list);
        }
        foreach ($dates_insert_sql_statement_values as $value) {
            $value_array[] = "({$event_id}, '{$value}')";
        }
        $values = implode(", ", $value_array);
        $dates_insert_sql_statement .= $values;
        // print_r($dates_insert_sql_statement);

        // datesへの登録
        $sql = $pdo -> prepare($dates_insert_sql_statement);
        $sql -> execute();

        // 日程一覧ページに遷移
        header('Location: display.php?address='.$address);
    }
    
    $name = '';
    $memo = '';
    $datetime_list = '';
    if (!empty($_POST['name'])) {
        $name = $_POST['name'];
    }
    if (!empty($_POST['memo'])) {
        $memo = $_POST['memo'];
    }
    if (!empty($_POST['datetime_list'])) {
        $datetime_list = $_POST['datetime_list'];
    }

    // function get_calendar($year, $month) {
    //     $last_day = intval(date('t', strtotime("last day of {$year}-{$month}"))); // 月末日
    //     $calendar_array = array();
    //     $order = 0; // カレンダーの左上から数えて何番目か？（左上は0番目）
    //     // １日から月末日までループ
    //     for ($date = 1; $date <= $last_day; $date++) {
    //         $week_num = date('w', strtotime("{$year}-{$month}-{$date}"));
    //         if ($date === 1) {
    //             for ($index = 0; $index < $week_num; $index++) { 
    //                 $line = 0; // 行
    //                 $column = $order; // 列        
    //                 // 日曜〜１日の曜日の前日まで空白をセット
    //                 $calendar_array[$line][$column] = '';
    //                 $order++;
    //             }
    //         }
    //         $line = intval($order / 7); // 行
    //         $column = $order % 7; // 列
    //         // 日付をセット
    //         $calendar_array[$line][$column] = $date;
    //         $order++;
    //         if ($date === $last_day) {
    //             for ($index = $week_num + 1; $index <= 6; $index++) { 
    //                 $line = intval($order / 7); // 行
    //                 $column = $order % 7; // 列        
    //                 // 月末日の次の日から土曜日まで空白をセット
    //                 $calendar_array[$line][$column] = '';
    //                 $order++;
    //             }
    //         }
    //     }
    //     return $calendar_array;
    // }

    // function display_calendar($year, $month, $calendar_array) {
    //     // htmlでカレンダーを出力する
    //     $week_list = [
    //         ['ja' => '日', 'en' => 'Sun'],
    //         ['ja' => '月', 'en' => 'Mon'],
    //         ['ja' => '火', 'en' => 'Tue'],
    //         ['ja' => '水', 'en' => 'Wed'],
    //         ['ja' => '木', 'en' => 'Thu'],
    //         ['ja' => '金', 'en' => 'Fri'],
    //         ['ja' => '土', 'en' => 'Sat']
    //     ];
    //     $html = '<table class="calendar">';
    //     $html .= '<tr>';
    //     foreach ($week_list as $week_num => $value) {
    //         $html .= "<th class='calendar-week week-{$week_num}-top'>{$value['ja']}</th>";
    //     }
    //     $html .= '</tr>';
    //     foreach ($calendar_array as $key1 => $line) {
    //         $calendar_row = $key1 + 1;
    //         $html .= "<tr class='calendar-row-{$calendar_row}'>";
    //         foreach ($line as $week_num => $value) {
    //             if (!empty($value)) {
    //                 $html .= "<td class='calendar-date week-{$week_num} calendar-date-filled' data-month='{$month}' data-date='{$value}' data-week='$week_num'>{$value}</td>";
    //             } else {
    //                 $html .= "<td class='calendar-date week-{$week_num}' data-week='$week_num' data-month='{$month}'></td>";
    //             }
    //         }
    //         $html .= '</tr>';
    //     }
    //     $html .= '</table>';
    //     return $html;
    // }
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>イベントの作成</title>
    <link rel="stylesheet"　type="text/css" href="css/create2.css">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>

    <header>
        <h1 class='title'>日程調整アプリ</h1>
        <a class='create-link' href="create.php">予定を作る</a>
    </header>

    <main>

        <h1>イベントの作成</h1>

        <div class='content detail'>
            <p>イベントを作成します。</p>
            <p>カレンダーの日付を押すことで、日程を追加できます。</p>
            <p>記入が終わったら、「送信」ボタンを押すことで、イベントの作成が完了します。</p>
        </div>
    
    
        <form action="" method='post'>
            <div class='content'>
                <label>
                    <p>イベント名</p>
                    <input type="text" class='text' name='name' id='eventName' value='<?php echo $name; ?>' required>
                </label>
            </div>
            <div class='content'>
                <label>
                    <p>メモ（伝達事項など）</p>
                    <textarea name="memo" class='text' id="memo"><?php echo $memo; ?></textarea>
                </label>
            </div>
            <div class='content'>
                <div id='calendar'>
                    <p>日付</p>
                    <div class='calendar-button-area'>
                        <button type='button' class='calendar-button' id='lastMonth'>前の月へ</button>
                        <button type='button' class='calendar-button' id='thisMonth'>今月</button>
                        <button type='button' class='calendar-button' id='nextMonth'>次の月へ</button>
                    </div>
                    <!-- ここにカレンダーが入る -->
                </div>
                <label>
                    <input type="checkbox" id='addTime'>日付の後に時刻を追加する
                </label>
                <label class='set-time-form' id='setTimeForm'>
                    <p>時刻</p>
                    <input type="text" id='setTime' class='text' placeholder='例：0:00〜'>
                </label>
            </div>
            <div class='content'>
                <label>
                    <p>設定した日時の一覧</p>
                    <textarea name="datetime_list" class='text' id="dateTimeList"><?php echo $datetime_list; ?></textarea>
                </label>
            </div>
            <button type='button' class='reset' id='reset'>リセット</button>
            <input type="submit" name="submit" class='submit' id="" value='送信'>
        </form>

    </main>

    <footer></footer>

</body>
<script type='text/javascript' src='js/create2.js'></script>
</html>