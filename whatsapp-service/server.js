const express = require('express');
const cors = require('cors');
const qrcode = require('qrcode');
const pino = require('pino');
const { default: makeWASocket, useMultiFileAuthState, DisconnectReason, fetchLatestBaileysVersion } = require('@whiskeysockets/baileys');
const path = require('path');

const app = express();
const PORT = process.env.WHATSAPP_PORT || 3000;

app.use(cors());
app.use(express.json());

const sessionPath = path.join(__dirname, 'session');
const logger = pino({ level: 'silent' });

let sock = null;
let isConnected = false;
let currentQR = null;
let phoneNumber = null;
const messageLogs = [];
const MAX_LOGS = 100;

async function connectToWhatsApp() {
    const { state, saveCreds } = await useMultiFileAuthState(sessionPath);
    const { version } = await fetchLatestBaileysVersion();

    sock = makeWASocket({
        version,
        logger,
        auth: state,
        printQRInTerminal: true,
        browser: ['Tahseel', 'Chrome', '1.0.0'],
    });

    sock.ev.on('creds.update', saveCreds);

    sock.ev.on('connection.update', async (update) => {
        const { connection, lastDisconnect, qr } = update;

        if (qr) {
            isConnected = false;
            phoneNumber = null;
            try {
                currentQR = await qrcode.toDataURL(qr);
            } catch (e) {
                currentQR = null;
            }
            console.log('QR code generated');
        }

        if (connection === 'close') {
            const shouldReconnect = lastDisconnect?.error?.output?.statusCode !== DisconnectReason.loggedOut;
            isConnected = false;
            phoneNumber = null;
            currentQR = null;
            console.log(`Connection closed: ${lastDisconnect?.error?.output?.statusCode}`);
            if (shouldReconnect) {
                setTimeout(connectToWhatsApp, 3000);
            }
        } else if (connection === 'open') {
            isConnected = true;
            phoneNumber = sock.user?.id ? `+${sock.user.id.split(':')[0]}` : null;
            currentQR = null;
            console.log(`WhatsApp connected: ${phoneNumber}`);
        }
    });

    sock.ev.on('messages.upsert', (m) => {
        if (m.type === 'notify' && m.messages[0]) {
            console.log('Received message from:', m.messages[0].key.remoteJid);
        }
    });
}

connectToWhatsApp();

app.get('/status', (req, res) => {
    res.json({
        connected: isConnected,
        phone: phoneNumber
    });
});

app.get('/qr', (req, res) => {
    if (isConnected) {
        return res.json({ qr: null, connected: true });
    }
    res.json({ qr: currentQR, connected: false });
});

app.post('/send', async (req, res) => {
    const { phone, message } = req.body;

    if (!phone || !message) {
        return res.status(400).json({ success: false, error: 'Phone and message required' });
    }

    if (!isConnected || !sock) {
        return res.status(503).json({ success: false, error: 'WhatsApp not connected' });
    }

    try {
        const cleanPhone = phone.replace(/[^0-9]/g, '');
        const jid = `${cleanPhone}@s.whatsapp.net`;

        await sock.sendMessage(jid, { text: message });

        const log = {
            phone: phone,
            message: message.substring(0, 200),
            status: 'sent',
            timestamp: new Date().toISOString()
        };
        messageLogs.unshift(log);
        if (messageLogs.length > MAX_LOGS) messageLogs.pop();

        res.json({ success: true });
    } catch (error) {
        const log = {
            phone: phone,
            message: message.substring(0, 200),
            status: 'failed',
            error: error.message,
            timestamp: new Date().toISOString()
        };
        messageLogs.unshift(log);
        if (messageLogs.length > MAX_LOGS) messageLogs.pop();

        res.status(500).json({ success: false, error: error.message });
    }
});

app.get('/logs', (req, res) => {
    const limit = parseInt(req.query.limit) || 50;
    res.json(messageLogs.slice(0, limit));
});

app.listen(PORT, '127.0.0.1', () => {
    console.log(`WhatsApp service (Baileys) running on port ${PORT}`);
});
