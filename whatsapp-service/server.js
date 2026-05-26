const express = require('express');
const cors = require('cors');
const qrcode = require('qrcode');
const { Client, LocalAuth } = require('whatsapp-web.js');
const path = require('path');

const app = express();
const PORT = process.env.WHATSAPP_PORT || 3000;

app.use(cors());
app.use(express.json());

const sessionPath = path.join(__dirname, 'session');
const client = new Client({
    authStrategy: new LocalAuth({
        dataPath: sessionPath
    }),
    puppeteer: {
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    }
});

let isConnected = false;
let currentQR = null;
let phoneNumber = null;
const messageLogs = [];
const MAX_LOGS = 100;

client.on('qr', async (qr) => {
    isConnected = false;
    phoneNumber = null;
    try {
        currentQR = await qrcode.toDataURL(qr);
    } catch (e) {
        currentQR = null;
    }
});

client.on('ready', () => {
    isConnected = true;
    phoneNumber = client.info?.wid?.user ? `+${client.info.wid.user}` : null;
    currentQR = null;
    console.log(`WhatsApp connected: ${phoneNumber}`);
});

client.on('disconnected', (reason) => {
    isConnected = false;
    phoneNumber = null;
    currentQR = null;
    console.log(`WhatsApp disconnected: ${reason}`);
});

client.on('authenticated', () => {
    console.log('WhatsApp authenticated');
});

client.on('auth_failure', (msg) => {
    console.error('WhatsApp auth failure:', msg);
    isConnected = false;
});

client.initialize();

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

    if (!isConnected) {
        return res.status(503).json({ success: false, error: 'WhatsApp not connected' });
    }

    try {
        const cleanPhone = phone.replace(/[^0-9]/g, '');
        const chatId = `${cleanPhone}@c.us`;

        await client.sendMessage(chatId, message);

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
    console.log(`WhatsApp service running on port ${PORT}`);
});
