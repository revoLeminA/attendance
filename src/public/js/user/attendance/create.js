// 日付・時刻を表示する要素を取得
const dateElement = document.getElementById('real-current-date');
const timeElement = document.getElementById('real-current-time');

// ボタン押下時の打刻時刻をinputに設定
const setCurrentTime = (type) => {
    const now = new Date();
    const jstString = now.toLocaleString('ja-JP', {
        timeZone: 'Asia/Tokyo',
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    }).replace(/\//g, '-').replace(/年|月/g, '-').replace('日', '').replace(/\s/, ' ');

    if (type === 'start') {
        document.getElementById('start-button').value = jstString;
    } else if (type === 'end') {
        document.getElementById('end-button').value = jstString;
    }
}

// 表示時刻を更新する関数
const updateTimeDisplay = () => {
    const now = new Date();
    const nowYear = now.getFullYear();
    const nowMonth = now.getMonth() + 1;
    const nowDate = now.getDate();
    const nowHours = now.getHours();
    const nowMinutes = now.getMinutes();
    const formattedDate = nowYear + "年" + nowMonth + "月" + nowDate + "日";
    const formattedTime = (nowHours < 10 ? "0" : "") + nowHours + ":" + (nowMinutes < 10 ? "0" : "") + nowMinutes;
    document.getElementById("real-current-date").textContent = formattedDate;
    document.getElementById("real-current-time").textContent = formattedTime;
}

setInterval(updateTimeDisplay, 1000); // 1秒ごとに更新
updateTimeDisplay(); // 初回実行
