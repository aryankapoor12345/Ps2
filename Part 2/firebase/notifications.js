import { db } from './firebase.js';
import { 
  collection, addDoc, getDocs, updateDoc, doc, 
  query, where, orderBy, limit, onSnapshot, serverTimestamp 
} from 'https://www.gstatic.com/firebasejs/10.8.0/firebase-firestore.js';

// Creates a workflow notification document in Firestore
export async function createNotification(userId, title, message, relatedType = null, relatedId = null) {
  if (!db || !userId) return false;
  try {
    await addDoc(collection(db, 'notifications'), {
      user_id: userId,
      title: title,
      message: message,
      related_type: relatedType,
      related_id: relatedId,
      is_read: false,
      created_at: serverTimestamp()
    });
    return true;
  } catch (e) {
    console.error("Error creating notification:", e);
    return false;
  }
}

// Marks a single notification document as read
export async function markAsRead(notificationId) {
  if (!db) return;
  try {
    const docRef = doc(db, 'notifications', notificationId);
    await updateDoc(docRef, { is_read: true });
  } catch (e) {
    console.error("Error marking notification read:", e);
  }
}

// Sets up a real-time listener for user unread notifications
export function setupNotificationListener(userId, callback) {
  if (!db || !userId) return () => {};
  
  const q = query(
    collection(db, 'notifications'), 
    where('user_id', '==', userId), 
    where('is_read', '==', false)
  );
  
  return onSnapshot(q, (snapshot) => {
    const notifs = snapshot.docs.map(doc => ({ id: doc.id, ...doc.data() }));
    callback(notifs);
  }, (error) => {
    console.error("Notification listener subscription error:", error);
  });
}

// Fetches the last N notifications for a user (history)
export async function getNotificationsHistory(userId, limitCount = 50) {
  if (!db || !userId) return [];
  try {
    const q = query(
      collection(db, 'notifications'), 
      where('user_id', '==', userId), 
      orderBy('created_at', 'desc'), 
      limit(limitCount)
    );
    const snapshot = await getDocs(q);
    return snapshot.docs.map(doc => ({ id: doc.id, ...doc.data() }));
  } catch (e) {
    console.error("Error fetching notifications history:", e);
    return [];
  }
}
