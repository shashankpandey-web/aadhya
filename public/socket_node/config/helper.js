const fs = require("fs");
const path = require("path");
const crypto = require("crypto");
const axios = require("axios");

// Load service account key
const serviceAccountPath = path.join(__dirname, "../../aadya-64bb1-firebase-adminsdk-fbsvc-6fe9c8fe99.json");
const serviceAccount = JSON.parse(fs.readFileSync(serviceAccountPath, "utf8"));

/**
 * Generate JWT
 */
function generateJWT() {
  const header = { alg: "RS256", typ: "JWT" };
  const now = Math.floor(Date.now() / 1000);

  const payload = {
    iss: serviceAccount.client_email,
    scope: "https://www.googleapis.com/auth/firebase.messaging",
    aud: "https://oauth2.googleapis.com/token",
    iat: now,
    exp: now + 3600
  };

  const base64url = (obj) =>
    Buffer.from(JSON.stringify(obj))
      .toString("base64")
      .replace(/=/g, "")
      .replace(/\+/g, "-")
      .replace(/\//g, "_");

  const encodedHeader = base64url(header);
  const encodedPayload = base64url(payload);
  const token = `${encodedHeader}.${encodedPayload}`;

  const signer = crypto.createSign("RSA-SHA256");
  signer.update(token);
  const signature = signer.sign(serviceAccount.private_key, "base64")
    .replace(/=/g, "")
    .replace(/\+/g, "-")
    .replace(/\//g, "_");

  return `${token}.${signature}`;
}

/**
 * Get access token
 */
async function getAccessToken() {
  const jwt = generateJWT();
  const response = await axios.post(
    "https://oauth2.googleapis.com/token",
    new URLSearchParams({
      grant_type: "urn:ietf:params:oauth:grant-type:jwt-bearer",
      assertion: jwt
    }).toString(),
    { headers: { "Content-Type": "application/x-www-form-urlencoded" } }
  );
  return response.data.access_token;
}

/**
 * Send push notification via FCM
 */
async function sendCustomerNotification(title, body, token, requestData = {}) {
  const accessToken = await getAccessToken();
  const projectId = "aadya-64bb1";

  const url = `https://fcm.googleapis.com/v1/projects/${projectId}/messages:send`;

  const message = {
    token: token,
    notification: { title, body },
    apns: {
      payload: {
        aps: { "mutable-content": 1, sound: "default" }
      }
    },
    data: Object.keys(requestData).length ? requestData : {}
  };

  const response = await axios.post(url, { message }, {
    headers: {
      Authorization: `Bearer ${accessToken}`,
      "Content-Type": "application/json"
    }
  });

  return response.data;
}

// Export functions
module.exports = sendCustomerNotification;

