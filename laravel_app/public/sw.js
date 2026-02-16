// サーバーからPush通知が届いた時のイベント
self.addEventListener('push', function (event) {
    if (!event.data) return;

    // 届いたデータを解析
    const data = event.data.json();

    // 通知を表示する
    event.waitUntil(
        self.registration.showNotification(data.title, {
            body: data.body,
            icon: '/icon.png', // もしアイコン画像があれば
            badge: '/badge.png' // Androidなどの通知バー用アイコン
        })
    );
});

// 通知をクリックした時のイベント
self.addEventListener('notificationclick', function (event) {
    event.notification.close();
    // クリックしたらアプリのトップ画面を開く
    event.waitUntil(
        clients.openWindow('/')
    );
});