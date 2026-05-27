const express = require('express');
const cors = require('cors');
const qrcode = require('qrcode');
const pino = require('pino');
const NodeCache = require('node-cache');
const fs = require('fs');
const { default: makeWASocket, useMultiFileAuthState, DisconnectReason, fetchLatestBaileysVersion, makeCacheableSignalKeyStore, generateMessageID, isJidBroadcast, isJidStatusBroadcast } = require('@whiskeysockets/baileys');
const path = require('path');

const app = express();
const PORT = process.env.WHATSAPP_PORT || 3000;

app.use(cors());
app.use(express.json());

const sessionPath = path.join(__dirname, 'session');
const logger = pino({ level: 'silent' });
const msgRetryCounterCache = new NodeCache({ stdTTL: 300, useClones: false });

let sock = null;
let isConnected = false;
let currentQR = null;
let phoneNumber = null;
const messageLogs = [];
const MAX_LOGS = 100;
const sentMessages = new Map();
const messageTimestamps = new Map();
let isConnectionReady = false;
const READINESS_DELAY = 10000; // 10 seconds for key sync

async function connectToWhatsApp() {
    const { state, saveCreds } = await useMultiFileAuthState(sessionPath);
    const { version } = await fetchLatestBaileysVersion();

    sock = makeWASocket({
        version,
        logger,
        auth: {
            creds: state.creds,
            keys: makeCacheableSignalKeyStore(state.keys, logger),
        },
        browser: ['Ubuntu', 'Chrome', '120.0.0.0'],
        syncFullHistory: false,
        markOnlineOnConnect: false,
        shouldIgnoreJid: (jid) => isJidBroadcast(jid) || isJidStatusBroadcast(jid),
        msgRetryCounterCache,
        getMessage: async (key) => {
            if (!key?.id) return undefined;
            const msg = sentMessages.get(key.id);
            if (msg) {
                console.log('getMessage: found full proto for', key.id);
                return msg;
            }
            console.log('getMessage: not found for', key.id);
            return undefined;
        },
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
            const statusCode = lastDisconnect?.error?.output?.statusCode;
            isConnected = false;
            isConnectionReady = false;
            phoneNumber = null;
            currentQR = null;
            console.log(`Connection closed: ${statusCode}`);
            if (statusCode === DisconnectReason.loggedOut) {
                console.log('Session logged out — clearing stale session and reconnecting...');
                try {
                    const files = fs.readdirSync(sessionPath);
                    files.forEach(f => fs.rmSync(path.join(sessionPath, f), { recursive: true, force: true }));
                } catch (e) {
                    console.error('Failed to clear session:', e.message);
                }
                setTimeout(connectToWhatsApp, 3000);
            } else {
                setTimeout(connectToWhatsApp, 3000);
            }
        } else if (connection === 'open') {
            isConnected = true;
            isConnectionReady = false;
            phoneNumber = sock.user?.id ? `+${sock.user.id.split(':')[0]}` : null;
            currentQR = null;
            console.log(`WhatsApp connected: ${phoneNumber}`);
            console.log(`Waiting ${READINESS_DELAY / 1000}s for key sync...`);
            setTimeout(() => {
                isConnectionReady = true;
                console.log('Connection ready for sending');
            }, READINESS_DELAY);
        }
    });

    sock.ev.on('messages.upsert', async (m) => {
        if (m.messages[0]) {
            const msg = m.messages[0];
            const remoteJid = msg.key?.remoteJid || 'unknown';
            const msgId = msg.key?.id || 'unknown';
            const msgType = msg.message ? Object.keys(msg.message)[0] : 'none';
            console.log('messages.upsert type:', m.type, '| jid:', remoteJid, '| id:', msgId, '| type:', msgType);

            // Capture the ACTUAL proto message from Baileys after sending
            if (msg.key?.fromMe && msg.message) {
                sentMessages.set(msgId, msg.message);
                messageTimestamps.set(msgId, Date.now());
                console.log('Captured actual proto for sent message:', msgId);
            }
        }
    });

    sock.ev.on('messages.update', async (updates) => {
        for (const update of updates) {
            console.log('messages.update:', JSON.stringify(update));
        }
    });
}

connectToWhatsApp();

// Cleanup old stored sent messages every 30 minutes
setInterval(() => {
    const cutoff = Date.now() - 30 * 60 * 1000;
    for (const [id, ts] of messageTimestamps.entries()) {
        if (ts < cutoff) {
            sentMessages.delete(id);
            messageTimestamps.delete(id);
        }
    }
}, 30 * 60 * 1000);

app.get('/status', (req, res) => {
    res.json({
        connected: isConnected,
        ready: isConnectionReady,
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
    if (!isConnectionReady) {
        return res.status(503).json({ success: false, error: 'WhatsApp is syncing keys — please wait a few seconds' });
    }

    try {
        const cleanPhone = phone.replace(/[^0-9]/g, '');
        const jid = `${cleanPhone}@s.whatsapp.net`;

        // Send presence update to ensure connection is fully established
        await sock.sendPresenceUpdate('available', jid);
        await new Promise(r => setTimeout(r, 1000));

        // Pre-generate message ID and store the FULL proto object BEFORE sending
        const msgId = generateMessageID();
        const messageProto = { extendedTextMessage: { text: message } };
        sentMessages.set(msgId, messageProto);
        messageTimestamps.set(msgId, Date.now());

        const result = await sock.sendMessage(jid, { text: message }, { messageId: msgId });

        // Wait 3s to allow encryption handshake to finalize
        await new Promise(r => setTimeout(r, 3000));

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
