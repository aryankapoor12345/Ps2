// Firebase Configuration.
// You can edit these values directly to match your Firebase project,
// or configure them dynamically on the diagnostics page (setup_check.html).
const firebaseConfig = {
  apiKey: localStorage.getItem('FB_API_KEY') || "PLACEHOLDER_API_KEY",
  authDomain: localStorage.getItem('FB_AUTH_DOMAIN') || "PLACEHOLDER_AUTH_DOMAIN",
  projectId: localStorage.getItem('FB_PROJECT_ID') || "PLACEHOLDER_PROJECT_ID",
  storageBucket: localStorage.getItem('FB_STORAGE_BUCKET') || "PLACEHOLDER_STORAGE_BUCKET",
  messagingSenderId: localStorage.getItem('FB_MESSAGING_SENDER_ID') || "PLACEHOLDER_MESSAGING_SENDER_ID",
  appId: localStorage.getItem('FB_APP_ID') || "PLACEHOLDER_APP_ID"
};

export default firebaseConfig;
