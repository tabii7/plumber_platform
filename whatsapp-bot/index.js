// whatsapp-bot/index.js
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

        // text variants
        const plain =
          msg.message.conversation ||
          msg.message.extendedTextMessage?.text ||
          msg.message?.ephemeralMessage?.message?.extendedTextMessage?.text ||
          '';

        // (we are not using interactive buttons in outbound; we still parse these if present)
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

/* ---------- Outbound (we render everything as text to avoid double-numbering) ---------- */

async function sendButtons(jidTo, payload) {
  const { body, options } = payload;
  const lines = [];
  lines.push(body || '');
  if (options && options.length) {
    lines.push('');
    options.forEach((opt, i) => {
      const clean = String(opt.text || `Option ${i+1}`).replace(/^\s*\d+[\.\)]\s*/, '');
      lines.push(`${i+1}) ${clean}`);
    });
  }
  await sock.sendMessage(jidTo, { text: lines.join('\n') });
}

async function sendList(jidTo, payload) {
  const { body, options } = payload;
  const lines = [];
  lines.push(body || '');
  if (options && options.length) {
    lines.push('');
    options.forEach((section) => {
      if (section.title) lines.push(`${section.title}:`);
      if (section.rows && section.rows.length) {
        section.rows.forEach((row, i) => {
          const clean = String(row.title || `Option ${i+1}`).replace(/^\s*\d+[\.\)]\s*/, '');
          lines.push(`${i+1}) ${clean}`);
        });
      }
    });
  }
  await sock.sendMessage(jidTo, { text: lines.join('\n') });
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

// Logout endpoint - clears session and disconnects
app.post('/logout', async (req, res) => {
  try {
    console.log('🚪 Logout requested...');
    if (sock) { await sock.logout(); sock = null; }

    const fs = require('fs');
    const path = require('path');

    try {
      if (fs.existsSync('./auth_info')) {
        fs.rmSync('./auth_info', { recursive: true, force: true });
        console.log('✅ Auth info directory cleared');
      }
      ['.wwebjs_auth','sessions','store'].forEach(dir => {
        if (fs.existsSync(dir)) { fs.rmSync(dir, { recursive:true, force:true }); console.log(`✅ ${dir} directory cleared`); }
      });
      (fs.readdirSync('.')).forEach(file => {
        if ((file.includes('auth') || file.includes('session') || file.includes('store')) && file.endsWith('.json')) {
          fs.unlinkSync(file);
          console.log(`✅ ${file} file removed`);
        }
      });
    } catch (fe) { console.log('⚠️ Some files could not be cleared:', fe.message); }

    isConnected = false; qrCodeData = null; reconnectAttempts = 0;
    console.log('✅ Logout completed successfully');
    res.json({ success:true, message:'Logged out successfully. Please restart the bot to reconnect.' });
  } catch (error) {
    console.error('❌ Error during logout:', error);
    res.status(500).json({ success:false, error:'Failed to logout properly' });
  }
});

const PORT = process.env.PORT || 3000;
const HOST = process.env.HOST || '127.0.0.1';

app.listen(PORT, HOST, () => {
  console.log(`🚀 WhatsApp bot running on http://${HOST}:${PORT}`);
  connectToWhatsApp();
});
