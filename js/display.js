const statusList = document.getElementsByClassName('status');
const deletePersons = document.getElementsByClassName('deletePersons');
const statusPulldowns = document.getElementsByClassName('status-pulldown');
const newStatusPulldowns = document.getElementsByClassName('new-status-pulldown');
const link = document.getElementById('link');
const copyButton = document.getElementById('copyButton');
const optionList = ['◯', '△', '×', '-'];

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

// ページが読み込まれたとき、ステータスを編集するためのチェックボックスを作成する
window.onload = (e) => {
    // 既存ユーザのステータス
    for (let index = 0; index < statusPulldowns.length; index++) {
        const statusCheckbox = statusPulldowns[index];
        // console.log(statusCheckbox);
        makePulldown(statusCheckbox, 0);
        // プルダウン（チェックボックス）の1つの要素が押されたとき、他の要素の選択を解除する
        const tests = statusCheckbox.getElementsByClassName('test');
        const testValues = statusCheckbox.getElementsByClassName('test-value');
        for (let index2 = 0; index2 < tests.length; index2++) {
            const test = tests[index2];
            const testValue = testValues[index2];
            testValue.addEventListener('click', () => {
                // すべて選択解除→選択解除後にクリックしたものが選択される
                if (test.checked) {
                    // チェックしてある状態から解除するとき
                    tests[3].checked = true;
                } else {
                    // チェックしていない状態からチェックするとき
                    for (let index3 = 0; index3 < testValues.length; index3++) {
                        const test = tests[index3];
                        test.checked = false;
                    }
                }
            });
            if (test !== '-') {
            }
        }
    }

    // 新規ユーザのステータス
    for (let index = 0; index < newStatusPulldowns.length; index++) {
        const newStatusCheckbox = newStatusPulldowns[index];
        makePulldown(newStatusCheckbox, 1);
        // プルダウン（チェックボックス）の1つの要素が押されたとき、他の要素の選択を解除する
        const tests = newStatusCheckbox.getElementsByClassName('test');
        const testValues = newStatusCheckbox.getElementsByClassName('test-value');
        for (let index2 = 0; index2 < testValues.length; index2++) {
            const test = tests[index2];
            const testValue = testValues[index2];
            testValue.addEventListener('click', () => {
                // すべて選択解除→選択解除後にクリックしたものが選択される
                if (test.checked) {
                    tests[3].checked = true;
                } else {
                    for (let index3 = 0; index3 < testValues.length; index3++) {
                        const test = tests[index3];
                        test.checked = false;
                    }
                }
            });
        }

    }

};

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

// リンクをコピーする
copyButton.addEventListener('click', () => {
    //コピーボタンが押されたとき、パスワードをクリップボードに保存する
    console.log('test');
    link.select();
    document.execCommand('Copy');
});