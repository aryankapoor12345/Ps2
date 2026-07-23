import { storage } from './firebase.js';
import { ref, uploadBytes, getDownloadURL } from 'https://www.gstatic.com/firebasejs/10.8.0/firebase-storage.js';

// Uploads a File object to Firebase Storage and returns the public URL
export async function uploadFile(fileObj, folder = 'observations') {
  if (!storage) {
    throw new Error("Firebase Storage is not initialized.");
  }
  try {
    const timestamp = Date.now();
    // Sanitize filename to avoid folder breakings
    const cleanFileName = fileObj.name.replace(/[^a-zA-Z0-9.]/g, '_');
    const storagePath = `${folder}/${timestamp}_${cleanFileName}`;
    const storageRef = ref(storage, storagePath);
    
    // Upload standard file blob
    const snapshot = await uploadBytes(storageRef, fileObj);
    const downloadURL = await getDownloadURL(snapshot.ref);
    
    return {
      success: true,
      path: downloadURL,
      original_name: fileObj.name
    };
  } catch (e) {
    console.error("Firebase Storage Upload Error:", e);
    return {
      success: false,
      message: e.message,
      path: null,
      original_name: fileObj.name
    };
  }
}
