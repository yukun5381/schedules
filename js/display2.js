const editLink = document.getElementById('editLink');
const deleteLink = document.getElementById('deleteLink');

const statusList = document.getElementsByClassName('status');

const deletePersons = document.getElementsByClassName('deletePersons');

const statusPulldowns = document.getElementsByClassName('status-pulldown');
const newStatusPulldowns = document.getElementsByClassName('new-status-pulldown');

const groupStatusList = document.getElementsByClassName('groupStatus');

const link = document.getElementById('link');
const copyButton = document.getElementById('copyButton');

const optionList = ['◯', '△', '×', '-'];

// チェックボックスを作る関数
const makeCheckbox = (statusCheckbox, newPerson) => {
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
            if (status === '-') {
                input.checked = true;
            }
        } else {
            input.name = 'status[' + statusCheckbox.dataset.personDateId + ']';
            input.disabled = true;
            if (status === statusCheckbox.dataset.personDateStatus) {
                input.checked = true;
            }
        }
        label.appendChild(input);

        // spanタグ
        if (status !== '-') {
            const span = document.createElement('span');
            span.classList.add('test-value'); //css用
            span.textContent = status;
            label.appendChild(span);
        }

        statusCheckbox.appendChild(label);

    });
};

// チェックボックスがクリックされたときの動作
const checkboxClicked = (tests, testValues) => {
    for (let checkboxIndex = 0; checkboxIndex < testValues.length; checkboxIndex++) {
        const test = tests[checkboxIndex];
        const testValue = testValues[checkboxIndex];
        testValue.addEventListener('click', () => {
            // すべて選択解除→選択解除後にクリックしたものが選択される
            if (test.checked) {
                // チェックしてある状態から解除するとき
                for (let index3 = 0; index3 < tests.length; index3++) {
                    const test = tests[index3];
                    if (test.value === '-') {
                        test.checked = true;
                    }
                }
            } else {
                // チェックしていない状態からチェックするとき
                for (let index3 = 0; index3 < tests.length; index3++) {
                    const test = tests[index3];
                    test.checked = false;
                }
            }
        });
    }
}

// リンクを編集、削除するときに使う、addressを送るための関数
const sendAddress = (status) => {
    // statusはeditかdelete
    if (status === 'edit') {
        confirmWord = '日程調整を編集しますか？';
        inputName = 'edit_id';
    } else if (status === 'delete') {
        confirmWord = '日程調整を削除しますか？';
        inputName = 'delete_id';
    }
    if (confirm(confirmWord)) {
        const form = document.createElement('form');
        form.method = 'post';
        form.action = 'create.php';
        const input = document.createElement('input');
        input.name = inputName;
        input.value = deleteLink.dataset.editId;
        form.appendChild(input);
        document.body.appendChild(form);
        console.log(form);
        form.submit();
    }
}

// ページが読み込まれたとき、ステータスを編集するためのチェックボックスを作成する
window.onload = (e) => {
    // 既存ユーザのステータス
    for (let statusIndex = 0; statusIndex < statusPulldowns.length; statusIndex++) {
        const statusCheckbox = statusPulldowns[statusIndex];
        // console.log(statusCheckbox);
        makeCheckbox(statusCheckbox, 0);
        // プルダウン（チェックボックス）の1つの要素が押されたとき、他の要素の選択を解除する
        const tests = statusCheckbox.getElementsByClassName('test');
        const testValues = statusCheckbox.getElementsByClassName('test-value');
        checkboxClicked(tests, testValues);
    }

    // 新規ユーザのステータス
    for (let index = 0; index < newStatusPulldowns.length; index++) {
        const newStatusCheckbox = newStatusPulldowns[index];
        makeCheckbox(newStatusCheckbox, 1);
        // プルダウン（チェックボックス）の1つの要素が押されたとき、他の要素の選択を解除する
        const tests = newStatusCheckbox.getElementsByClassName('test');
        const testValues = newStatusCheckbox.getElementsByClassName('test-value');
        checkboxClicked(tests, testValues);
    }

};

// 予定を編集するリンクが押されたとき、イベントのaddressをcreate.phpに送る
editLink.addEventListener('click', () => {
    sendAddress('edit');
});

// 予定を削除するリンクが押されたとき、イベントのaddressをcreate.phpに送る
deleteLink.addEventListener('click', () => {
    sendAddress('delete');
});

// 予定情報が押されたとき、チェックリストを表示して編集できるようにする
for (let index = 0; index < statusList.length; index++) {
    const status = statusList[index];
    // console.log(status);
    status.addEventListener('click', (e) => {
        status.getElementsByClassName('status-display')[0].style.display = 'none';
        status.getElementsByClassName('status-pulldown')[0].style.display = 'block';
        const inputList = status.getElementsByTagName('input');
        for (let inputIndex = 0; inputIndex < inputList.length; inputIndex++) {
            inputList[inputIndex].disabled = false;
        }
        status.classList.remove('status-css');
    });
}


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

// グループ情報が押されたとき、登録するためにidなどを送信する
for (let index = 0; index < groupStatusList.length; index++) {
    const status = groupStatusList[index];
    status.addEventListener('click', () => {
        const form = status.getElementsByTagName('form')[0];
        console.log(form);
        form.submit();
    });
}

// リンクをコピーする
copyButton.addEventListener('click', () => {
    //コピーボタンが押されたとき、パスワードをクリップボードに保存する
    console.log('test');
    link.select();
    document.execCommand('Copy');
});