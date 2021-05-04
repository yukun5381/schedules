const statusList = document.getElementsByClassName('status');
const deletePersons = document.getElementsByClassName('deletePersons');
const statusPulldowns = document.getElementsByClassName('status-pulldown');
const newStatusPulldowns = document.getElementsByClassName('new-status-pulldown');
const link = document.getElementById('link');
const copyButton = document.getElementById('copyButton');
const optionList = ['◯', '△', '×'];

// チェックボックスを作る関数に変更
const makePulldown = (statusCheckbox, newPerson) => {
    optionList.forEach(status => {
        // labelタグ
        const label = document.createElement('label');
        // inputタグ
        const input = document.createElement('input');
        input.type = 'checkbox';
        input.classList.add('test'); //css用
        input.value = status;
        if (newPerson) {
            input.name = 'new_status[' + statusCheckbox.dataset.dateId + ']';
            if (status === optionList[0]) {
                input.checked = true;
            }
        } else {
            input.name = 'status[' + statusCheckbox.dataset.personDateId + ']';
            input.disabled = true;
            if (status === statusCheckbox.dataset.personDateStatus) {
                input.checked = true;
            }
        }
        // spanタグ
        const span = document.createElement('span');
        span.classList.add('test-value'); //css用
        span.textContent = status;

        label.appendChild(input);
        label.appendChild(span);
        statusCheckbox.appendChild(label);
        
    });
};

// ページが読み込まれたとき、ステータスを編集するためのチェックボックスを作成する
window.onload = (e) => {
    // 既存ユーザのステータス
    for (let index = 0; index < statusPulldowns.length; index++) {
        const statusCheckbox = statusPulldowns[index];
        // console.log(statusCheckbox);
        makePulldown(statusCheckbox, 0);
    }

    // 新規ユーザのステータス
    for (let index = 0; index < newStatusPulldowns.length; index++) {
        const newStatusCheckbox = newStatusPulldowns[index];
        makePulldown(newStatusCheckbox, 1);
    }

};

// 予定情報が押されたとき、チェックリストを表示して編集できるようにする
for (let index = 0; index < statusList.length; index++) {
    const status = statusList[index];
    // console.log(status);
    status.addEventListener('click', (e) => {
        status.getElementsByClassName('status-display')[0].style.display = 'none';
        status.getElementsByClassName('status-pulldown')[0].style.display = 'block';
        status.getElementsByTagName('input')[0].disabled = false;
        status.getElementsByTagName('input')[1].disabled = false;
        status.getElementsByTagName('input')[2].disabled = false;
        status.classList.remove('status-css');
    });
}
// console.log(deletePersons);
// 削除ボタンが押されたとき、削除対象の人のidを送信する
for (let index = 0; index < deletePersons.length; index++) {
    const deletePerson = deletePersons[index];
    deletePerson.addEventListener('click', (e) => {
        const personName = deletePerson.dataset.personName;
        if (confirm(personName + 'さんのステータスを消去しますか？')) {
            // 削除対象の人のidを受け渡すためのinputタグを作成
            let request = document.createElement('input');
            request.type = 'hidden';
            request.name = 'delete_id';
            request.value = deletePerson.dataset.personId;
            // 作成したinputタグをフォームの中に入れ、送信
            document.events.appendChild(request);
            document.events.submit();
        }
    });
}
// 新規参加ボタンが押されたとき、名前が入力されているときのみ送信する
const validateNewPerson = () => {
    if (!(document.getElementById('newPersonForm').value)) {
        alert('名前が入力されていません。');
        return false;
    } else {
        return true;
    }
};

// リンクをコピーする
copyButton.addEventListener('click', () => {
    //コピーボタンが押されたとき、パスワードをクリップボードに保存する
    console.log('test');
    link.select();
    document.execCommand('Copy');
});