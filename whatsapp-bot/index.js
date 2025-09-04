require('dotenv').config();

const { default: makeWASocket, useMultiFileAuthState } = require('@whiskeysockets/baileys');
const express = require('express');
const cors = require('cors');
const qrcode = require('qrcode');
const axios = require('axios');

const app = express();
app.use(cors());
app.use(express.json({ limit: '10mb' }));

const LARAVEL_API = process.env.LARAVEL_API_URL || 'http://127.0.0.1:8000';
let sock;
let qrCodeData = null;
let isConnected = false;
let reconnectAttempts = 0;
const maxReconnectAttempts = 5;

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
      browser: [process.env.BROWSER_NAME || 'LoodgieterApp', process.env.BROWSER_VERSION || 'Chrome', process.env.BROWSER_OS || '1.0.0'],
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

    // Incoming -> Laravel
    sock.ev.on('messages.upsert', async (m) => {
      try {
        const msg = m?.messages?.[0];
        if (!msg || !msg.message || msg.key.fromMe) return;

        const id = msg.key.id;
        if (!remember(id)) return;

        const from = msg.key.remoteJid;
        const plain =
          msg.message.conversation ||
          msg.message.extendedTextMessage?.text ||
          msg.message?.ephemeralMessage?.message?.extendedTextMessage?.text ||
          '';

        const buttonId = msg.message?.buttonsResponseMessage?.selectedButtonId;
        const listRowId = msg.message?.listResponseMessage?.singleSelectReply?.selectedRowId;

        const inputRaw = (buttonId || listRowId || plain || '').trim();
        if (!inputRaw) return;

        console.log(`📩 From ${from} ->`, inputRaw);

        const res = await axios.post(`${LARAVEL_API}/api/wa/incoming`, {
          from: from.replace('@s.whatsapp.net', ''),
          message: inputRaw,
          normalized: inputRaw.toLowerCase()
        }, { timeout: 20000 });

        const reply = res?.data?.reply;
        if (reply) await sendFromPayload(from, reply);
      } catch (err) {
        console.error('❌ incoming error', err?.response?.data || err.message || err);
      }
    });

  } catch (error) {
    console.error('❌ Error connecting to WhatsApp:', error);
    setTimeout(connectToWhatsApp, 5000);
  }
}

/* ---------- Outbound ---------- */

async function sendButtons(jidTo, payload) {
  const { body, options } = payload;
  const textMessage = body + '\n\n' + (options || []).map((b, i) => `${i + 1}) ${b.text || `Option ${i + 1}`}`).join('\n');
  await sock.sendMessage(jidTo, { text: textMessage });
}

async function sendList(jidTo, payload) {
  const { body, options } = payload;
  let textMessage = body || '';
  if (options && options.length > 0) {
    textMessage += '\n\n';
    options.forEach((section) => {
      if (section.title) textMessage += `${section.title}:\n`;
      if (section.rows && section.rows.length > 0) {
        section.rows.forEach((row, i) => {
          textMessage += `${i + 1}) ${row.title}\n`;
        });
      }
    });
  }
  await sock.sendMessage(jidTo, { text: textMessage });
}

async function sendFromPayload(jidTo, payload) {
  const type = payload.type;
  if (type === 'buttons') return sendButtons(jidTo, payload);
  if (type === 'list')    return sendList(jidTo, payload);

  const text = payload.body || '';
  if (!text) return;
  await sock.sendMessage(jidTo, { text });
}

/* ---------- Admin ---------- */

app.get('/status', (req, res) => {
  if (isConnected && sock?.user) return res.json({ status: 'Connected', user: sock.user });
  if (qrCodeData) return res.json({ status: 'Awaiting QR scan' });
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

// Laravel -> send text
app.post('/send-message', async (req, res) => {
  try {
    const { number, message } = req.body;
    if (!number || !message) return res.status(400).json({ error: 'Missing number or message' });
    await sock.sendMessage(jid(number), { text: message });
    return res.json({ status: 'success', number, message });
  } catch (error) {
    console.error('❌ send-message error:', error);
    return res.status(500).json({ error: 'Failed to send message', details: error.message });
  }
});

const PORT = process.env.PORT || 3000;
const HOST = process.env.HOST || '127.0.0.1';

app.listen(PORT, HOST, () => {
  console.log(`🚀 WhatsApp bot running on http://${HOST}:${PORT}`);
  connectToWhatsApp();
});
