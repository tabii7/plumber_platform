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

const LARAVEL_API = process.env.LARAVEL_API_URL || 'http://127.0.0.1:8001';
let sock;
let qrCodeData = null;
let isConnected = false;

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
  const { state, saveCreds } = await useMultiFileAuthState('./auth_info');

  sock = makeWASocket({
    auth: state,
    printQRInTerminal: false, // we render in /get-qr
    browser: ['LoodgieterApp', 'Chrome', '1.0.0'],
    syncFullHistory: false
  });

  sock.ev.on('connection.update', (update) => {
    const { connection, qr, lastDisconnect } = update;

    if (qr) {
      qrCodeData = qr;
      isConnected = false;
      console.log('📲 QR received (open admin page to scan).');
    }

    if (connection === 'open') {
      qrCodeData = null;
      isConnected = true;
      console.log('✅ WhatsApp connected!');
    }

    if (connection === 'close') {
      isConnected = false;
      console.log('❌ Connection closed, reconnecting...', lastDisconnect?.error?.message || '');
      setTimeout(connectToWhatsApp, 1500);
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

      // Send to Laravel runtime; Laravel decides the flow/state
      const res = await axios.post(`${LARAVEL_API}/api/wa/incoming`, {
        from: from.replace('@s.whatsapp.net', ''),
        message: normalized
      }, { timeout: 20000 });

      const reply = res?.data?.reply;
      if (reply) {
        await sendFromPayload(from, reply);
      }
    } catch (err) {
      console.error('❌ incoming error', err?.response?.data || err.message || err);
    }
  });
}

/* ---------- Renderers ---------- */

function isInteractivePayload(p) {
  return p && (p.type === 'buttons' || p.type === 'list');
}

async function sendButtons(jidTo, payload) {
  const { body, footer, options } = payload;
  const buttons = (options || []).map((b, i) => ({
    buttonId: (b.id || `opt_${i}`).toString(),
    buttonText: { displayText: b.text || `Option ${i + 1}` },
    type: 1
  }));

  await sock.sendMessage(jidTo, {
    text: body || '',
    footer: footer || '',
    buttons,
    headerType: 1
  });
}

async function sendList(jidTo, payload) {
  const { title, body, options } = payload;
  const sections = (options || []).map(sec => ({
    title: sec.title || '',
    rows: (sec.rows || []).map((r, i) => ({
      title: r.title || `Option ${i + 1}`,
      rowId: (r.id || `row_${i}`).toString()
    }))
  }));

  await sock.sendMessage(jidTo, {
    text: body || '',
    buttonText: title || 'Select',
    sections
  });
}

async function sendFromPayload(jidTo, payload) {
  try {
    const type = payload.type;

    if (type === 'buttons') {
      return sendButtons(jidTo, payload);
    }
    if (type === 'list') {
      return sendList(jidTo, payload);
    }

    // treat text / collect_text / dispatch all as plain text outbound
    const text = payload.body || '';
    if (!text) return;
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

    if (!sock || !sock.user || !isConnected) {
      return res.status(500).json({ status: 'error', error: 'WhatsApp not connected' });
    }
    if (!number || !message) {
      return res.status(422).json({ status: 'error', error: 'number and message are required' });
    }

    await sock.sendMessage(jid(number), { text: message });
    res.json({ status: 'success', number, message });
  } catch (err) {
    console.error('Send message error:', err);
    res.status(500).json({ status: 'error', error: err.message });
  }
});

app.get('/health', (req, res) => res.json({ ok: true }));

app.listen(3000, () => {
  console.log('🚀 WhatsApp bot running on http://127.0.0.1:3000');
});

connectToWhatsApp();
