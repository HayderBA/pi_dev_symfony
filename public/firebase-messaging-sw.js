importScripts('https://www.gstatic.com/firebasejs/10.7.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/10.7.0/firebase-messaging-compat.js');

const firebaseConfig = {
    apiKey: "AIzaSyCr5Zp_5-IwxqPFgPZa1Xtpv07NKc9NQMs",
    authDomain: "growmind-a5559.firebaseapp.com",
    projectId: "growmind-a5559",
    storageBucket: "growmind-a5559.firebasestorage.app",
    messagingSenderId: "675370108793",
    appId: "1:675370108793:web:1cc21d37dd099a174611a8"
};

firebase.initializeApp(firebaseConfig);
const messaging = firebase.messaging();

messaging.onBackgroundMessage((payload) => {
    console.log('Message en arrière-plan:', payload);
    const notificationTitle = payload.notification.title;
    const notificationOptions = {
        body: payload.notification.body,
        icon: '/favicon.ico'
    };
    self.registration.showNotification(notificationTitle, notificationOptions);
});