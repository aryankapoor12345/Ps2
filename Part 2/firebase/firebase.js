import { initializeApp } from 'https://www.gstatic.com/firebasejs/10.8.0/firebase-app.js';
import { getAuth } from 'https://www.gstatic.com/firebasejs/10.8.0/firebase-auth.js';
import { getFirestore } from 'https://www.gstatic.com/firebasejs/10.8.0/firebase-firestore.js';
import { getStorage } from 'https://www.gstatic.com/firebasejs/10.8.0/firebase-storage.js';
import firebaseConfig from './config.js';

let app = null;
let auth = null;
let db = null;
let storage = null;

// Initialize Firebase if a valid API Key is provided
if (firebaseConfig.apiKey && firebaseConfig.apiKey !== "PLACEHOLDER_API_KEY") {
  try {
    app = initializeApp(firebaseConfig);
    auth = getAuth(app);
    db = getFirestore(app);
    storage = getStorage(app);
  } catch (error) {
    console.error("Firebase services initialization failed:", error);
  }
}

export { app, auth, db, storage };
