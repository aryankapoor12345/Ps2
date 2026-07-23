# NTPC Safety Portal - Firebase Deployment Guide

This guide details the step-by-step procedure to initialize, seed, and deploy the migrated NTPC Online Safety Portal to Firebase Hosting and Cloud Firestore.

---

## Prerequisites

1. **Node.js**: Ensure Node.js is installed on your local machine.
2. **Firebase CLI**: Install the Firebase command-line tools globally:
   ```bash
   npm install -g firebase-tools
   ```
3. **Firebase Project**: Create a new project in the [Firebase Console](https://console.firebase.google.com/).
   - Enable **Authentication** (Email/Password sign-in method).
   - Enable **Cloud Firestore** in test mode (or production mode; rules in `firestore.rules` will secure it).
   - Enable **Firebase Storage** (rules in `storage.rules` will secure it).
   - Register a **Web App** in your Firebase project to get the config credentials.

---

## Local Setup & Configuration

1. **Clone/Open Workspace**: Make sure you are in the project root directory.
2. **Firebase CLI Login**:
   ```bash
   firebase login
   ```
3. **Link Firebase Project**:
   ```bash
   firebase use --add
   ```
   Select the Firebase project ID you created.

---

## Seeding the Database

Before using the portal, you must seed the default roles, departments, safety zones, agencies, categories, and test user accounts.

1. **Start a Local Server**:
   You can run a local server in the workspace directory using:
   ```bash
   npx serve .
   ```
2. **Open Diagnostics Checker**:
   Navigate to the setup checker in your browser:
   `http://localhost:3000/setup_check.html` (or matching port).
3. **Configure API Keys**:
   - Copy the Firebase Web App SDK configuration object from your Firebase Console.
   - Paste the values into the fields in the **2. Configure Firebase Project** section.
   - Click **Save Credentials**.
4. **Trigger Seeding**:
   - Once saved and the page reloads, verify that the **3. Database Seeding Tool** section is visible and states "Connected".
   - Click **Seed Firebase Database**.
   - Monitor the console logs until it displays: `✔ Firestore Database seeding completed successfully!`.

---

## Deploying to Production

Once local seeding is done, you can deploy the static site and security rules directly to Firebase:

```bash
firebase deploy
```

This command deploys:
1. `firebase.json` hosting settings (mapped to index.html for static asset serving).
2. `firestore.rules` database access constraints.
3. `storage.rules` file upload access constraints.
4. All local static `.html`, `.css`, and `.js` assets.

---

## Demo Credentials

After seeding, the following default test accounts are available:

| Username | Role | Temporary Password |
| :--- | :--- | :--- |
| `admin` | Administrator | `password123` |
| `bhupendra` | Sub Leader | `password123` |
| `ashok` | Zone Leader | `password123` |
| `eic_user` | Engineer In-Charge | `password123` |
| `agency_user` | Agency Contractor | `password123` |
| `employee_user` | Safety Observer (User) | `password123` |

*Note: Logging in for the first time with a temporary password triggers client-side lazy-registration, automatically creating the corresponding Firebase Authentication credentials securely.*
