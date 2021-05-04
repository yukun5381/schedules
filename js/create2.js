const eventName = document.getElementById('eventName');
const memo = document.getElementById('memo');

const clientHeight = memo.clientHeight;

const lastMonth = document.getElementById('lastMonth');
const thisMonth = document.getElementById('thisMonth');
const nextMonth = document.getElementById('nextMonth');

const calendar = document.getElementById('calendar');

const setDate = document.getElementById('setDate');
const addTime = document.getElementById('addTime');
const setTime = document.getElementById('setTime');
const setTimeForm = document.getElementById('setTimeForm');

let calendarWeeks, calendarDates;

const dateTimeList = document.getElementById('dateTimeList');

const reset = document.getElementById('reset');

const weekList = [
    {'ja' : '日', 'en' : 'Sun'},
    {'ja' : '月', 'en' : 'Mon'},
    {'ja' : '火', 'en' : 'Tue'},
    {'ja' : '水', 'en' : 'Wed'},
    {'ja' : '木', 'en' : 'Thu'},
    {'ja' : '金', 'en' : 'Fri'},
    {'ja' : '土', 'en' : 'Sat'}
];

let status = '';

// カレンダーのデータを取得する関数
const getCalendar = (year, month) => {
    const lastDayObject = new Date(year, month, 0);
    const lastDay = lastDayObject.getDate();
    const calendarArray = [];
    let line, column;
    let order = 0; //カレンダーの左上から数えて何番目か？（左上は0番目）

    // 1日から月末日までループ
    for (let date = 1; date <= lastDay; date++) {
        const dateObject = new Date(year, month-1, date);
        const weekNum = dateObject.getDay();
        if (date === 1) {
            for (let index = 0; index < weekNum; index++) {
                line = 0; //行
                column = order; //列
                // 日曜〜1日の曜日の前日まで空白をセット
                if (!calendarArray[line]) {
                    calendarArray[line] = [];
                    // console.log('初期化');
                }
                calendarArray[line][column] = '';
                // console.log(calendarArray[line][column]);
                order++;
            }
        }
        line = Math.floor(order / 7); //行
        column = order % 7; //列
        if (!calendarArray[line]) {
            calendarArray[line] = [];
            // console.log('初期化');
        }
        calendarArray[line][column] = date;
        // console.log(calendarArray[line][column]);
        order++;
        if (date === lastDay) {
            for (let index = weekNum + 1; index <= 6; index++) {
                line = Math.floor(order / 7);
                column = order % 7;
                // 月末日の次の日から土曜日まで空白をセット
                calendarArray[line][column] = '';
                order++;
            }
        }
    }
    return calendarArray;
}

// htmlでカレンダーを出力する関数
const displayCalendar = (year, month, calendarArray) => {
    const p = document.createElement('p');
    p.innerText = year + '年' + month + '月';
    const table = document.createElement('table');
    table.classList.add('calendar');

    const trTop = document.createElement('tr');
    for (let weekNum = 0; weekNum < weekList.length; weekNum++) {
        const th = document.createElement('th');
        th.classList.add('calendar-week');
        th.classList.add('week-' + weekNum + '-top');
        th.innerText = weekList[weekNum]['ja'];
        trTop.appendChild(th);
    }
    table.appendChild(trTop);

    for (let rowNum = 0; rowNum < calendarArray.length; rowNum++) {
        const line = calendarArray[rowNum];
        const calendarRow = rowNum + 1;
        const tr = document.createElement('tr');
        tr.classList.add('calendar-row-' + calendarRow);

        for (let weekNum = 0; weekNum < line.length; weekNum++) {
            const value = line[weekNum];
            const td = document.createElement('td');
            td.classList.add('calendar-date');
            td.classList.add('week-' + weekNum);
            td.dataset.week = weekNum;
            td.dataset.month = month;
            if (value) {
                td.classList.add('calendar-date-filled');
                td.dataset.date = value;
                td.innerText = value;
            }
            tr.appendChild(td);
        }

        table.appendChild(tr);
    }

    calendar.appendChild(p);
    calendar.appendChild(table);
    // console.log(table);
}

// カレンダーの日付が選択されたときの動作
const calendarDateSelected = () => {
    for (let dateIndex = 0; dateIndex < calendarDates.length; dateIndex++) {
        const calendarDate = calendarDates[dateIndex];
        calendarDate.addEventListener('click', () => {
            const month = calendarDate.dataset.month;
            const date = calendarDate.dataset.date;
            const week = calendarDate.dataset.week;
            const time = setTime.value;
            calendar_selected(month, date, week, time);
        });
    }
}

// カレンダーの曜日が選択されたときの動作
const calendarWeekSelected = () => {
    for (let weekNum = 0; weekNum < weekList.length; weekNum++) {
        const calendarWeek = calendarWeeks[weekNum];
        const datesOfWeek = document.getElementsByClassName('week-' + weekNum);
        // console.log(datesOfWeek);
        calendarWeek.addEventListener('click', () => {
            for (let index = 0; index < datesOfWeek.length; index++) {
                const dateOfWeek = datesOfWeek[index];
                if (dateOfWeek.dataset.date) {
                    const month = dateOfWeek.dataset.month;
                    const date = dateOfWeek.dataset.date;
                    const week = dateOfWeek.dataset.week;
                    const time = setTime.value;
                    calendar_selected(month, date, week, time);
                }
            }
        });
    }
}

const today = new Date();
let year = today.getFullYear();
let month = today.getMonth() + 1;
const calendarArray = getCalendar(year, month);
displayCalendar(year, month, calendarArray);
calendarWeeks = document.getElementsByClassName('calendar-week');
calendarDates = document.getElementsByClassName('calendar-date-filled');
calendarWeekSelected();
calendarDateSelected();

// 「前の月へ」ボタンなどが押されたときの動作
lastMonth.addEventListener('click', () => {
    // 現在のカレンダーを削除
    const table = calendar.lastChild;
    calendar.removeChild(table);
    const p = calendar.lastChild;
    calendar.removeChild(p);
    // 年月を更新
    month--;
    if (month < 1) {
        month = 12;
        year--;
    }
    // 新たなカレンダーを生成
    const NewCalendarArray = getCalendar(year, month);
    displayCalendar(year, month, NewCalendarArray);
    calendarWeeks = document.getElementsByClassName('calendar-week');
    calendarDates = document.getElementsByClassName('calendar-date-filled');
    calendarWeekSelected();
    calendarDateSelected();
});

thisMonth.addEventListener('click', () => {
    // 現在のカレンダーを削除
    const table = calendar.lastChild;
    calendar.removeChild(table);
    const p = calendar.lastChild;
    calendar.removeChild(p);
    // 年月を更新
    year = today.getFullYear();
    month = today.getMonth() + 1;
    // 新たなカレンダーを生成
    const NewCalendarArray = getCalendar(year, month);
    displayCalendar(year, month, NewCalendarArray);
    calendarWeeks = document.getElementsByClassName('calendar-week');
    calendarDates = document.getElementsByClassName('calendar-date-filled');
    calendarWeekSelected();
    calendarDateSelected();
});

nextMonth.addEventListener('click', () => {
    // 現在のカレンダーを削除
    const table = calendar.lastChild;
    calendar.removeChild(table);
    const p = calendar.lastChild;
    calendar.removeChild(p);
    // 年月を更新
    month++;
    if (month > 12) {
        month = 1;
        year++;
    }
    // 新たなカレンダーを生成
    const NewCalendarArray = getCalendar(year, month);
    displayCalendar(year, month, NewCalendarArray);
    calendarWeeks = document.getElementsByClassName('calendar-week');
    calendarDates = document.getElementsByClassName('calendar-date-filled');
    calendarWeekSelected();
    calendarDateSelected();
});



// メモ欄が入力されたとき、テキストの高さを自動調整する
memo.addEventListener('input', () => {
    memo.style.height = clientHeight + 'px';
    memo.style.height = memo.scrollHeight + 'px';
});

// Array.sort();

// 月日の情報を受け取り、設定した日時一覧に反映させる関数
const calendar_selected = (month, date, week, time) => {
    let dateTime = month + '/' + date + '(' + weekList[week]['ja'] + ')';
    if (time) {
        dateTime += ' ' + time;
    }
    dateTime += '\n';
    // if (!dateTimeList.textContent) {
    //     dateTimeList.textContent = dateTime;
    // } else {
    //     dateTimeList.textContent += dateTime;
    // }
    dateTimeList.value += dateTime;
    // 高さの自動調整
    dateTimeList.style.height = clientHeight + 'px';
    dateTimeList.style.height = dateTimeList.scrollHeight + 'px';
}

// 「日付の後に時刻を追加する」にチェックをしたときの動作
addTime.addEventListener('change', () => {
    if (addTime.checked) {
        setTimeForm.classList.remove('set-time-form');
        setTimeForm.classList.add('set-time-form-displayed');
        setTime.value = '13:00〜14:00';
    } else {
        setTimeForm.classList.remove('set-time-form-displayed');
        setTimeForm.classList.add('set-time-form');
        setTime.value = '';
    }
});

// リセットボタンが押されたときの動作
reset.addEventListener('click', () => {
    eventName.value = '';
    memo.value = '';
    setTime.value = '';
    dateTimeList.value = '';
});
