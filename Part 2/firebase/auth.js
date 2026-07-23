import { auth, db } from './firebase.js';
import { 
  signInWithEmailAndPassword, 
  signOut, 
  updatePassword, 
  sendPasswordResetEmail,
  setPersistence,
  browserSessionPersistence,
  browserLocalPersistence
} from 'https://www.gstatic.com/firebasejs/10.8.0/firebase-auth.js';
import { doc, updateDoc, collection, query, where, getDocs } from 'https://www.gstatic.com/firebasejs/10.8.0/firebase-firestore.js';
import { logAudit } from './firestore.js';

let inactivityTimer;
const INACTIVITY_LIMIT = 15 * 60 * 1000; // 15 minutes inactivity limit

// Starts a timer to automatically log out user after inactivity
export function startInactivityWatcher() {
  const resetTimer = () => {
    clearTimeout(inactivityTimer);
    inactivityTimer = setTimeout(async () => {
      if (auth && auth.currentUser) {
        await logoutUser();
        alert("You have been logged out due to inactivity.");
        window.location.href = '/login.html?reason=inactivity';
      }
    }, INACTIVITY_LIMIT);
  };

  window.addEventListener('mousemove', resetTimer);
  window.addEventListener('keypress', resetTimer);
  window.addEventListener('click', resetTimer);
  window.addEventListener('scroll', resetTimer);
  
  resetTimer();
}

// User login function supporting short username mapping and lazy Auth registration
export async function loginUser(username, password, rememberMe = false) {
  if (!auth || !db) {
    throw new Error("Firebase is not initialized. Please configure it in setup_check.html first.");
  }

  const persistence = rememberMe ? browserLocalPersistence : browserSessionPersistence;
  await setPersistence(auth, persistence);

  const cleanUsername = username.trim().toLowerCase();
  const dummyEmail = cleanUsername + "@ntpcsafety.local";

  // Check Firestore first to fetch user record
  const usersRef = collection(db, 'users');
  const q = query(usersRef, where('username', '==', cleanUsername));
  const querySnapshot = await getDocs(q);

  if (querySnapshot.empty) {
    throw new Error("Invalid username or password");
  }

  const userDoc = querySnapshot.docs[0];
  const userData = userDoc.data();

  if (!userData.is_active) {
    throw new Error("Your account has been deactivated. Please contact the administrator.");
  }

  // Handle lazy auth registration (user created by admin in Firestore first)
  if (userData.auth_created === false) {
    if (userData.temp_password !== password) {
      throw new Error("Invalid username or password");
    }

    // Create Firebase Auth credentials
    const { createUserWithEmailAndPassword } = await import('https://www.gstatic.com/firebasejs/10.8.0/firebase-auth.js');
    const userCredential = await createUserWithEmailAndPassword(auth, dummyEmail, password);
    const firebaseUser = userCredential.user;

    // Link user UID and clear temporary password in Firestore
    await updateDoc(doc(db, 'users', userDoc.id), {
      uid: firebaseUser.uid,
      auth_created: true,
      temp_password: null,
      last_login: new Date(),
      updated_at: new Date()
    });

    const profile = { ...userData, id: userDoc.id, uid: firebaseUser.uid, auth_created: true, temp_password: null };
    sessionStorage.setItem('current_user_profile', JSON.stringify(profile));

    await logAudit(firebaseUser.uid, 'User registered & logged in (lazy signup)', 'users', userDoc.id);
    return profile;
  }

  // Standard sign-in via Firebase Authentication
  try {
    const userCredential = await signInWithEmailAndPassword(auth, dummyEmail, password);
    const firebaseUser = userCredential.user;

    await updateDoc(doc(db, 'users', userDoc.id), {
      last_login: new Date()
    });

    const profile = { ...userData, id: userDoc.id, uid: firebaseUser.uid };
    sessionStorage.setItem('current_user_profile', JSON.stringify(profile));

    await logAudit(firebaseUser.uid, 'User logged in', 'users', userDoc.id);
    return profile;
  } catch (error) {
    console.error("Login failure:", error);
    throw new Error("Invalid username or password");
  }
}

// Signs out the user
export async function logoutUser() {
  if (auth) {
    const uid = auth.currentUser ? auth.currentUser.uid : null;
    await signOut(auth);
    if (uid) {
      await logAudit(uid, 'User logged out', 'users', null);
    }
  }
  sessionStorage.removeItem('current_user_profile');
  window.location.href = '/login.html';
}

// Changes user password
export async function changeUserPassword(newPassword) {
  if (!auth || !auth.currentUser) {
    throw new Error("No user logged in.");
  }
  await updatePassword(auth.currentUser, newPassword);
  await logAudit(auth.currentUser.uid, 'User changed password', 'users', null);
}

// Sends password reset email
export async function resetUserPassword(email) {
  if (!auth) {
    throw new Error("Firebase is not initialized.");
  }
  await sendPasswordResetEmail(auth, email);
}

// Route guarding and auth listener check
export function initAuthCheck() {
  return new Promise((resolve) => {
    const currentPath = window.location.pathname;

    if (!auth) {
      if (!currentPath.endsWith('setup_check.html')) {
        window.location.href = '/setup_check.html';
      }
      resolve(null);
      return;
    }

    auth.onAuthStateChanged(async (user) => {
      if (!user) {
        if (!currentPath.endsWith('login.html') && !currentPath.endsWith('setup_check.html')) {
          sessionStorage.removeItem('current_user_profile');
          window.location.href = '/login.html';
        }
        resolve(null);
      } else {
        let profile = null;
        try {
          const cached = sessionStorage.getItem('current_user_profile');
          if (cached) {
            profile = JSON.parse(cached);
          } else {
            const usersRef = collection(db, 'users');
            const q = query(usersRef, where('uid', '==', user.uid));
            const snapshot = await getDocs(q);
            if (!snapshot.empty) {
              profile = { id: snapshot.docs[0].id, ...snapshot.docs[0].data() };
              sessionStorage.setItem('current_user_profile', JSON.stringify(profile));
            }
          }
        } catch (e) {
          console.error("Error fetching user profile:", e);
        }

        if (currentPath.endsWith('login.html') || currentPath.endsWith('index.html') || currentPath === '/') {
          window.location.href = '/dashboard.html';
        } else {
          startInactivityWatcher();
        }
        resolve(profile);
      }
    });
  });
}
