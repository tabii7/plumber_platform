// whatsapp-bot/index.js
require('dotenv').config();

const { default: makeWASocket, useMultiFileAuthState } = require('@whiskeysockets/baileys');
const express = require('express');
const cors = require('cors');
const qrcode = require('qrcode');
const axios = require('axios');

const app = express();
app.use(cors());
app.use(express.json());

// const LARAVEL_API = process.env.LARAVEL_API_URL || 'https://your-domain.com';
LARAVEL_API = 'http://127.0.0.1:8000'
let sock;
let qrCodeData = null;
let isConnected = false;
let reconnectAttempts = 0;
const maxReconnectAttempts = 5;

// tiny in-memory dedupe
const seen = new Set();
const SEEN_LIMIT = 500;

function jid(number) {
  return number.includes('@s.whatsapp.net') ? number : number.replace(/\D/g, '') + '@s.whatsapp.net';
}

function remember(id) {
  if (!id) return false;
  if (seen.has(id)) return false;
  seen.add(id);
  if (seen.size > SEEN_LIMIT) {
    // drop oldest-ish (cheap)
    const first = seen.values().next().value;
    seen.delete(first);
  }
  return true;
}

async function connectToWhatsApp() {
  try {
    const { state, saveCreds } = await useMultiFileAuthState('./auth_info');

    sock = makeWASocket({
      auth: state,
      printQRInTerminal: false,
      browser: [
        process.env.BROWSER_NAME || 'LoodgieterApp',
        process.env.BROWSER_VERSION || 'Chrome',
        process.env.BROWSER_OS || '1.0.0'
      ],
      syncFullHistory: false,
      connectTimeoutMs: 30000,
      qrTimeout: 30000,
      defaultQueryTimeoutMs: 30000,
      retryRequestDelayMs: 1000,
      markOnlineOnConnect: false
    });

    sock.ev.on('connection.update', (update) => {
      const { connection, qr, lastDisconnect } = update;

      if (qr) {
        qrCodeData = qr;
        isConnected = false;
        reconnectAttempts = 0;
        console.log('📲 QR received (open admin page to scan).');
      }

      if (connection === 'open') {
        qrCodeData = null;
        isConnected = true;
        reconnectAttempts = 0;
        console.log('✅ WhatsApp connected!');
      }

      if (connection === 'close') {
        isConnected = false;
        console.log('❌ Connection closed, reconnecting...', lastDisconnect?.error?.message || '');
        
        if (reconnectAttempts < maxReconnectAttempts) {
          const delay = Math.pow(2, reconnectAttempts) * 1000;
          console.log(`🔄 Reconnect attempt ${reconnectAttempts + 1}/${maxReconnectAttempts} in ${delay}ms`);
          setTimeout(connectToWhatsApp, delay);
          reconnectAttempts++;
        } else {
          console.log('❌ Max reconnection attempts reached. Please restart the bot manually.');
        }
      }
    });

    sock.ev.on('creds.update', saveCreds);

    // ---- Incoming messages -> Laravel runtime ----
    sock.ev.on('messages.upsert', async (m) => {
      try {
        const msg = m?.messages?.[0];
        if (!msg || !msg.message || msg.key.fromMe) return;

        const id = msg.key.id;
        if (!remember(id)) return; // de-dupe

        const from = msg.key.remoteJid;

        // Extract user text or interactive selections
        const plain =
          msg.message.conversation ||
          msg.message.extendedTextMessage?.text ||
          msg.message?.ephemeralMessage?.message?.extendedTextMessage?.text ||
          '';

        const buttonId = msg.message?.buttonsResponseMessage?.selectedButtonId;
        const listRowId = msg.message?.listResponseMessage?.singleSelectReply?.selectedRowId;

        // normalized input: prefer interactive ids, otherwise plain text
        const inputRaw = (buttonId || listRowId || plain || '').trim();
        if (!inputRaw) return;

        const normalized = inputRaw.toLowerCase();

        console.log(`📩 From ${from} ->`, inputRaw);

        // Send to Laravel runtime
        const res = await axios.post(`${LARAVEL_API}/api/wa/incoming`, {
          from: from.replace('@s.whatsapp.net', ''),
          message: inputRaw,
          normalized: normalized
        }, { timeout: 20000 });

        const reply = res?.data?.reply;
        console.log('📤 Laravel response:', JSON.stringify(reply, null, 2));
        if (reply) {
          await sendFromPayload(from, reply);
        }
      } catch (err) {
        console.error('❌ incoming error', err?.response?.data || err.message || err);
      }
    });

  } catch (error) {
    console.error('❌ Error connecting to WhatsApp:', error);
    setTimeout(connectToWhatsApp, 5000);
  }
}

/* ---------- Renderers ---------- */

function isInteractivePayload(p) {
  return p && (p.type === 'buttons' || p.type === 'list');
}

async function sendButtons(jidTo, payload) {
  const { body, footer, options } = payload;
  
  // Convert options to proper button format
  const buttons = (options || []).map((b, i) => ({
    buttonId: (b.id || `opt_${i}`).toString(),
    buttonText: { displayText: b.text || `Option ${i + 1}` },
    type: 1
  }));

  console.log('🔘 Button payload:', JSON.stringify({ body, footer, buttons }, null, 2));

  // Always send as text message with numbered options for better compatibility
  const textMessage = body + '\n\n' + (options || []).map((b, i) => `${i + 1}) ${b.text}`).join('\n');
  await sock.sendMessage(jidTo, { text: textMessage });
  console.log('📝 Sent text message with options to:', jidTo);
}

async function sendList(jidTo, payload) {
  const { title, body, options } = payload;
  
  // Convert list to text format for better compatibility
  let textMessage = body || '';
  
  if (options && options.length > 0) {
    textMessage += '\n\n';
    options.forEach((section, sectionIndex) => {
      if (section.title) {
        textMessage += `${section.title}:\n`;
      }
      if (section.rows && section.rows.length > 0) {
        section.rows.forEach((row, rowIndex) => {
          textMessage += `${rowIndex + 1}) ${row.title}\n`;
        });
      }
    });
  }
  
  await sock.sendMessage(jidTo, { text: textMessage });
  console.log('📋 Sent list as text to:', jidTo);
}

async function sendFromPayload(jidTo, payload) {
  try {
    console.log('🎯 sendFromPayload called with type:', payload.type);
    const type = payload.type;

    if (type === 'buttons') {
      console.log('🔘 Sending buttons to:', jidTo);
      return sendButtons(jidTo, payload);
    }
    if (type === 'list') {
      console.log('📋 Sending list to:', jidTo);
      return sendList(jidTo, payload);
    }

    // treat text / collect_text / dispatch all as plain text outbound
    const text = payload.body || '';
    if (!text) return;
    console.log('📝 Sending text to:', jidTo);
    await sock.sendMessage(jidTo, { text });
  } catch (e) {
    console.error('❌ sendFromPayload error', e.message);
  }
}

/* ---------- Admin/Integration endpoints ---------- */

app.get('/status', (req, res) => {
  if (isConnected && sock?.user) {
    return res.json({ status: 'Connected', user: sock.user });
  }
  if (qrCodeData) {
    return res.json({ status: 'Awaiting QR scan' });
  }
  return res.json({ status: 'Not connected' });
});

app.get('/get-qr', async (req, res) => {
  try {
    if (qrCodeData) {
      const qrImage = await qrcode.toDataURL(qrCodeData);
      return res.json({ qr: qrImage });
    }
    return res.json({
      message: isConnected ? 'WhatsApp is already connected!' : 'QR not available yet'
    });
  } catch (e) {
    return res.status(500).json({ message: 'Failed to render QR', error: e.message });
  }
});

// Laravel -> Bot send text
app.post('/send-message', async (req, res) => {
  try {
    const { number, message } = req.body;
    if (!number || !message) {
      return res.status(400).json({ error: 'Missing number or message' });
    }

    const jidTo = jid(number);
    await sock.sendMessage(jidTo, { text: message });

    return res.json({ status: 'success', number, message });
  } catch (error) {
    console.error('❌ send-message error:', error);
    return res.status(500).json({ error: 'Failed to send message', details: error.message });
  }
});

// Start the server
const PORT = process.env.PORT || 3000;
const HOST = process.env.HOST || '127.0.0.1';

app.listen(PORT, HOST, () => {
  console.log(`🚀 WhatsApp bot running on http://${HOST}:${PORT}`);
  connectToWhatsApp();
});
