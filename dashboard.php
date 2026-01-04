<?php
require_once __DIR__ . '/config.php';
require_once BASE_PATH . '/auth.php';
include BASE_PATH . '/partials/header.php';
?>

<div class="page-header mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="page-title mb-0">
                    <i class="fas fa-chart-line me-2"></i>Dashboard Energi
                </h1>
                <p class="page-subtitle mb-0">Monitoring operasional gedung - Ringkasan real-time</p>
            </div>
            <button class="btn btn-refresh btn-sm" onclick="location.reload()">
                <i class="fas fa-sync-alt me-2"></i>Refresh
            </button>
        </div>
    </div>

<!-- Stat Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card card-status">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <p class="stat-label">Total Konsumsi</p>
                        <h3 class="stat-value" id="totalKwh">-</h3>
                        <p class="stat-unit">kWh (30 hari)</p>
                    </div>
                    <div style="font-size: 2.5rem; color: rgba(13, 110, 253, 0.2);">
                        <i class="fas fa-bolt"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card card-status">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <p class="stat-label">Rata-rata Per Sampel</p>
                        <h3 class="stat-value" id="avgKwh" style="color: #0dcaf0;">-</h3>
                        <p class="stat-unit">kWh</p>
                    </div>
                    <div style="font-size: 2.5rem; color: rgba(13, 202, 240, 0.2);">
                        <i class="fas fa-tachometer-alt"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card card-status">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <p class="stat-label">Status Sistem</p>
                        <h4 class="stat-value" id="statusText" style="color: #6f42c1;">-</h4>
                        <p class="stat-unit" id="statusDesc">-</p>
                    </div>
                    <div style="font-size: 2.5rem; color: rgba(111, 66, 193, 0.2);">
                        <i class="fas fa-heartbeat"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Chart Section -->
<div class="card mb-4">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h5 class="mb-0">
                    <i class="fas fa-chart-area me-2"></i>Grafik Konsumsi Energi
                </h5>
                <small class="text-muted">Visualisasi konsumsi berdasarkan periode waktu</small>
            </div>
            <div class="col-md-6 text-end">
                <select id="chartMode" class="form-select d-inline-block" style="width: auto;">
                    <option value="daily">📅 Harian</option>
                    <option value="weekly">📊 Mingguan</option>
                    <option value="monthly">📈 Bulanan</option>
                    <option value="live" selected>🔴 Live</option>
                </select>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div style="position: relative; height: 350px;">
            <canvas id="energyChart"></canvas>
        </div>
    </div>
</div>

<!-- Info Section -->
<div class="row g-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>Informasi Terbaru
                </h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <small class="text-muted d-block mb-1">Sampel Terakhir Terekam</small>
                    <p class="mb-0 fw-500" id="latestInfo">
                        <span class="spinner-border spinner-border-sm me-2"></span>Memuat...
                    </p>
                </div>
                <hr>
                <div>
                    <small class="text-muted d-block mb-1">Ambang Peringatan (24 jam)</small>
                    <p class="mb-0 fw-500" id="thresholdInfo">
                        <span class="spinner-border spinner-border-sm me-2"></span>Memuat...
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-lightbulb me-2"></i>Tips Efisiensi
                </h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        <small>Monitor konsumsi secara berkala untuk identifikasi pola</small>
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        <small>Periksa laporan untuk analisis tren jangka panjang</small>
                    </li>
                    <li>
                        <i class="fas fa-check-circle text-success me-2"></i>
                        <small>Buat catatan untuk setiap perbaikan atau pemeliharaan</small>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- AI Chat -->
<div class="card mt-4" id="ai-chat">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0"><i class="fas fa-brain me-2"></i>AI Chat</h5>
            <small class="text-muted">Asisten monitoring energi</small>
        </div>
        <div class="d-flex gap-2">
            <button id="aiNewChat" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-plus me-1"></i>New Chat
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <h6 class="mb-2">Sessions</h6>
                <div id="aiSessionList" class="list-group small" style="max-height: 320px; overflow-y: auto;">
                    <div class="list-group-item text-muted">Memuat...</div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0">Percakapan</h6>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-outline-secondary" id="btnQuickSummary">Ringkas 24 jam</button>
                        <button class="btn btn-sm btn-outline-warning" id="btnQuickAnomaly">Cek anomali 1 jam</button>
                    </div>
                </div>
                <div id="aiChatMessages" class="border rounded p-3 mb-2" style="height: 320px; overflow-y: auto; background:#f8f9fa;">
                    <p class="text-muted mb-0">Pilih sesi atau buat chat baru.</p>
                </div>
                <div class="input-group">
                    <textarea id="aiChatInput" class="form-control" rows="2" placeholder="Tulis pesan..." style="resize: none;"></textarea>
                    <button id="aiSendBtn" class="btn btn-primary">
                        <i class="fas fa-paper-plane me-1"></i>Kirim
                    </button>
                </div>
                <div id="aiChatStatus" class="small text-muted mt-2"></div>
            </div>
        </div>
    </div>
</div>

<script>
const API_BASE = document.body.dataset.apiBase || 'backend/api.php';
const totalKwhEl = document.getElementById('totalKwh');
const avgKwhEl = document.getElementById('avgKwh');
const statusTextEl = document.getElementById('statusText');
const statusDescEl = document.getElementById('statusDesc');
const latestInfoEl = document.getElementById('latestInfo');
const thresholdInfoEl = document.getElementById('thresholdInfo');
const chartModeEl = document.getElementById('chartMode');
const REFRESH_SUMMARY_MS = 15000;
const REFRESH_CHART_MS = 20000;
const DUMMY_INTERVAL_MS = 15000;
const LIVE_FEED_INTERVAL_MS = 15000;
let summaryTimer;
let chartTimer;
let dummyTimer;
let liveApiTimer;
let chart;
let liveTimer;
let simT = 0;
let summaryLoading = false;
let chartLoading = false;

function showApiError(message) {
    statusTextEl.textContent = '⚠️ Error';
    statusDescEl.textContent = message || 'Pastikan API berjalan di ' + API_BASE;
    statusTextEl.classList.remove('status-normal');
    statusTextEl.classList.add('status-warning');
}

function getFeedFromStorage() {
    try {
        const raw = localStorage.getItem('monitorFeed');
        return raw ? JSON.parse(raw) : [];
    } catch (err) {
        return [];
    }
}

function updateSummaryFromFeed(feed) {
    if (!feed.length) return;
    const latest = feed[feed.length - 1];
    const last30 = feed.slice(-30);
    const total = last30.reduce((sum, item) => sum + (item.v || 0), 0);
    const avg = last30.length ? total / last30.length : 0;
    totalKwhEl.textContent = total.toLocaleString('id-ID');
    avgKwhEl.textContent = avg.toFixed(2);
    statusTextEl.textContent = avg > 60 ? '⚠️ Simulasi Waspada' : '✅ Simulasi Normal';
    statusTextEl.classList.toggle('status-warning', avg > 60);
    statusTextEl.classList.toggle('status-normal', avg <= 60);
    statusDescEl.textContent = avg > 60 ? 'Simulasi: konsumsi tinggi' : 'Simulasi: dalam batas aman';
    const dt = new Date(latest.t);
    latestInfoEl.innerHTML = `<strong>${dt.toLocaleString('id-ID')}</strong><br><span class="badge bg-primary">${latest.v} kWh</span>`;
    thresholdInfoEl.textContent = 'Simulasi: 75 kWh / 24 jam';
}

async function loadSummary() {
    if (summaryLoading) return;
    summaryLoading = true;
    try {
        startDummyLoop();
        const feed = getFeedFromStorage();
        updateSummaryFromFeed(feed);
    } finally {
        summaryLoading = false;
    }
}

function renderChart(labels, values, mode) {
    const ctx = document.getElementById('energyChart').getContext('2d');
    if (chart) chart.destroy();
    chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels,
            datasets: [{
                label: `Konsumsi Energi (${mode})`,
                data: values,
                tension: 0.4,
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13,110,253,0.1)',
                borderWidth: 3,
                fill: true,
                pointRadius: 5,
                pointBackgroundColor: '#0d6efd',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointHoverRadius: 7,
                pointHoverBackgroundColor: '#0b5ed7',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    labels: { usePointStyle: true, padding: 15 }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { callback: (v) => v + ' kWh' }
                }
            }
        }
    });
}

async function loadChart(mode = 'daily') {
    if (chartLoading) return;
    chartLoading = true;
    try {
        startDummyLoop();
        if (mode === 'live') {
            startLive();
        } else if (mode === 'daily') {
            const agg = aggregateHistory('daily');
            renderChart(agg.labels, agg.values, 'Harian');
        } else if (mode === 'weekly') {
            const agg = aggregateHistory('weekly');
            renderChart(agg.labels, agg.values, 'Mingguan');
        } else if (mode === 'monthly') {
            const agg = aggregateHistory('monthly');
            renderChart(agg.labels, agg.values, 'Bulanan');
        } else {
            renderDummyChart('Simulasi');
        }
    } finally {
        chartLoading = false;
    }
}

function stopChartAutoRefresh() {
    if (chartTimer) {
        clearInterval(chartTimer);
        chartTimer = null;
    }
}

function startChartAutoRefresh(mode) {
    stopChartAutoRefresh();
    chartTimer = setInterval(() => loadChart(mode), REFRESH_CHART_MS);
}

function startSummaryAutoRefresh() {
    if (summaryTimer) clearInterval(summaryTimer);
    summaryTimer = setInterval(() => loadSummary(), REFRESH_SUMMARY_MS);
}

chartModeEl.addEventListener('change', (e) => {
    const m = e.target.value;
    stopLive();
    stopChartAutoRefresh();
    if (m === 'live') {
        startLive();
    } else {
        loadChart(m);
        startChartAutoRefresh(m);
    }
});

loadSummary();
startSummaryAutoRefresh();
const initialMode = chartModeEl.value || 'daily';
if (initialMode === 'live') {
    startLive();
} else {
    loadChart(initialMode);
    startChartAutoRefresh(initialMode);
}

function nextEnergy() {
    simT += 1;
    const base = 45 + 35 * Math.sin(simT / 3);
    const noise = (Math.random() * 10) - 5;
    return Math.max(5, Math.round(base + noise));
}

function startLive() {
    stopLive();
    startLiveApiLoop();
    let feed = [];
    try {
        const raw = localStorage.getItem('monitorFeed');
        feed = raw ? JSON.parse(raw) : [];
    } catch (err) {
        feed = [];
    }
    if (!feed.length) {
        startDummyLoop();
        feed = getFeedFromStorage();
    }
    const labels = feed.map(s => new Date(s.t).toLocaleTimeString('id-ID'));
    const values = feed.map(s => s.v);
    renderChart(labels, values, 'Live');
    liveTimer = setInterval(updateChartFromFeed, 15000);
    window.addEventListener('storage', (e) => {
        if (e.key === 'monitorFeed') updateChartFromFeed();
    });
}

function stopLive() {
    stopLiveApiLoop();
    if (liveTimer) {
        clearInterval(liveTimer);
        liveTimer = null;
    }
}

function updateChartFromFeed() {
    try {
        const raw = localStorage.getItem('monitorFeed');
        const feed = raw ? JSON.parse(raw) : [];
        const labels = feed.map(s => new Date(s.t).toLocaleTimeString('id-ID'));
        const values = feed.map(s => s.v);
        chart.data.labels = labels;
        chart.data.datasets[0].data = values;
        chart.update();
    } catch (err) {}
}

function renderDummyChart(modeLabel = 'Simulasi') {
    const feed = getFeedFromStorage();
    if (!feed.length) return;
    const labels = feed.map(s => new Date(s.t).toLocaleTimeString('id-ID'));
    const values = feed.map(s => s.v);
    renderChart(labels, values, modeLabel);
}

function getHistoryFromStorage() {
    try {
        const raw = localStorage.getItem('monitorHist');
        return raw ? JSON.parse(raw) : [];
    } catch (err) {
        return [];
    }
}

function seedHistoryIfMissing() {
    try {
        const hist = getHistoryFromStorage();
        if (hist.length < 30) {
            const out = hist.slice();
            const need = 30 - hist.length;
            for (let i = need - 1; i >= 0; i--) {
                const d = new Date();
                d.setHours(0, 0, 0, 0);
                d.setDate(d.getDate() - i);
                const base = 1200 + 300 * Math.sin((i + 1) / 2);
                const noise = (Math.random() * 200) - 100;
                const v = Math.max(200, Math.round(base + noise));
                out.push({ d: d.toISOString(), v });
            }
            localStorage.setItem('monitorHist', JSON.stringify(out));
        }
    } catch (err) {}
}

function aggregateHistory(mode) {
    const hist = getHistoryFromStorage();
    if (!hist.length) return { labels: [], values: [] };
    if (mode === 'daily') {
        const labels = hist.map(x => new Date(x.d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short' }));
        const values = hist.map(x => x.v);
        return { labels, values };
    }
    if (mode === 'weekly') {
        const map = new Map();
        hist.forEach(x => {
            const d = new Date(x.d);
            const day = d.getDay();
            const diff = (day + 6) % 7;
            d.setDate(d.getDate() - diff);
            d.setHours(0, 0, 0, 0);
            const key = d.toISOString().slice(0, 10);
            map.set(key, (map.get(key) || 0) + x.v);
        });
        const keys = Array.from(map.keys()).sort();
        const labels = keys.map(k => {
            const d = new Date(k + 'T00:00:00Z');
            return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short' });
        });
        const values = keys.map(k => map.get(k));
        return { labels, values };
    }
    if (mode === 'monthly') {
        const map = new Map();
        hist.forEach(x => {
            const d = new Date(x.d);
            const key = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}`;
            map.set(key, (map.get(key) || 0) + x.v);
        });
        const keys = Array.from(map.keys()).sort();
        const labels = keys.map(k => {
            const [y, m] = k.split('-').map(Number);
            return new Date(y, m - 1, 1).toLocaleDateString('id-ID', { month: 'short', year: 'numeric' });
        });
        const values = keys.map(k => map.get(k));
        return { labels, values };
    }
    return { labels: [], values: [] };
}

function startDummyLoop() {
    if (dummyTimer) return;
    try {
        const current = getFeedFromStorage();
        if (current.length < 30) {
            const seed = current.slice();
            const need = 30 - seed.length;
            for (let i = need - 1; i >= 0; i--) {
                const dt = new Date(Date.now() - i * 60000);
                const energy = nextEnergy();
                seed.push({ t: dt.toISOString(), v: energy });
            }
            localStorage.setItem('monitorFeed', JSON.stringify(seed));
        }
    } catch (err) {}
    seedHistoryIfMissing();
    dummyTimer = setInterval(() => {
        const energy = nextEnergy();
        const now = new Date();
        const feed = getFeedFromStorage();
        feed.push({ t: now.toISOString(), v: energy });
        if (feed.length > 120) feed.shift();
        localStorage.setItem('monitorFeed', JSON.stringify(feed));
        updateSummaryFromFeed(feed);
        if (chartModeEl.value === 'live') {
            updateChartFromFeed();
        } else {
            renderDummyChart('Simulasi');
        }
    }, DUMMY_INTERVAL_MS);
}

async function fetchLiveFeedFromApi() {
    startDummyLoop();
    updateSummaryFromFeed(getFeedFromStorage());
}

function startLiveApiLoop() {
    stopLiveApiLoop();
    startDummyLoop();
    liveApiTimer = setInterval(() => {
        updateChartFromFeed();
        updateSummaryFromFeed(getFeedFromStorage());
    }, LIVE_FEED_INTERVAL_MS);
}

function stopLiveApiLoop() {
    if (liveApiTimer) {
        clearInterval(liveApiTimer);
        liveApiTimer = null;
    }
}
</script>

<script>
// =============== AI Chat ===============
const aiSessionList = document.getElementById('aiSessionList');
const aiChatMessages = document.getElementById('aiChatMessages');
const aiChatInput = document.getElementById('aiChatInput');
const aiSendBtn = document.getElementById('aiSendBtn');
const aiChatStatus = document.getElementById('aiChatStatus');
const aiNewChatBtn = document.getElementById('aiNewChat');
const aiQuickSummary = document.getElementById('btnQuickSummary');
const aiQuickAnomaly = document.getElementById('btnQuickAnomaly');
let aiCurrentSession = null;

function aiSetStatus(text, isError = false) {
    aiChatStatus.textContent = text || '';
    aiChatStatus.classList.toggle('text-danger', isError);
}

async function aiCheckStatus() {
    try {
        const res = await fetch('api/ai/status.php');
        const data = await res.json();
        if (!res.ok) throw new Error(data.message || 'Status AI tidak tersedia');
        if (!data.sodium_active) {
            aiSetStatus('Ext-sodium belum aktif. Aktifkan di php.ini.', true);
            return;
        }
        if (!data.app_master_key_set) {
            aiSetStatus('APP_MASTER_KEY belum diset di Environment.', true);
            return;
        }
        if (!data.has_settings_row) {
            aiSetStatus('Tabel/pengaturan AI belum dibuat. Hubungi admin untuk konfigurasi.', true);
            return;
        }
        if (!data.enabled) {
            aiSetStatus('AI belum diaktifkan. Hubungi admin untuk mengaktifkan.', true);
            return;
        }
        if (!data.has_key) {
            aiSetStatus('API key belum dikonfigurasi. Hubungi admin.', true);
            return;
        }
        aiSetStatus('Siap');
    } catch (err) {
        aiSetStatus(err.message, true);
    }
}
function aiRenderSessions(sessions) {
    aiSessionList.innerHTML = '';
    if (!sessions || sessions.length === 0) {
        const empty = document.createElement('div');
        empty.className = 'list-group-item text-muted';
        empty.textContent = 'Belum ada sesi.';
        aiSessionList.appendChild(empty);
        return;
    }
    sessions.forEach((s) => {
        const item = document.createElement('button');
        item.type = 'button';
        item.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-center';
        item.textContent = s.title;
        if (aiCurrentSession === s.id) {
            item.classList.add('active');
        }
        const time = document.createElement('small');
        time.className = 'text-muted';
        time.textContent = s.updated_at;
        item.appendChild(time);
        item.addEventListener('click', () => {
            aiCurrentSession = s.id;
            aiLoadMessages();
            aiLoadSessions(); // refresh highlight
        });
        aiSessionList.appendChild(item);
    });
}

function aiRenderMessages(messages) {
    aiChatMessages.innerHTML = '';
    if (!messages || messages.length === 0) {
        aiChatMessages.innerHTML = '<p class="text-muted mb-0">Belum ada percakapan.</p>';
        return;
    }
    messages.forEach((m) => {
        const wrap = document.createElement('div');
        wrap.className = m.role === 'user' ? 'd-flex justify-content-end mb-2' : 'd-flex mb-2';
        const bubble = document.createElement('div');
        bubble.className = m.role === 'user' ? 'ai-bubble-user' : 'ai-bubble-assistant';
        const head = document.createElement('div');
        head.className = 'ai-head';
        head.textContent = m.role === 'user' ? 'Anda' : 'AI';
        const body = document.createElement('div');
        body.className = 'small';
        body.textContent = m.content;
        bubble.appendChild(head);
        bubble.appendChild(body);
        wrap.appendChild(bubble);
        aiChatMessages.appendChild(wrap);
    });
    aiChatMessages.scrollTop = aiChatMessages.scrollHeight;
}

async function aiLoadSessions() {
    try {
        const res = await fetch('api/ai/session_list.php');
        const data = await res.json();
        if (!res.ok) throw new Error(data.message || 'Gagal memuat sesi');
        aiRenderSessions(data.sessions || []);
    } catch (err) {
        aiSetStatus(err.message, true);
    }
}

async function aiLoadMessages() {
    if (!aiCurrentSession) return;
    aiSetStatus('Memuat pesan...');
    try {
        const res = await fetch(`api/ai/session_messages.php?session_id=${aiCurrentSession}`);
        const data = await res.json();
        if (!res.ok) throw new Error(data.message || 'Gagal memuat pesan');
        aiRenderMessages(data.messages || []);
        aiSetStatus('Siap');
    } catch (err) {
        aiSetStatus(err.message, true);
    }
}

async function aiNewSession() {
    aiSetStatus('Membuat sesi...');
    try {
        const res = await fetch('api/ai/session_new.php', { method: 'POST' });
        const data = await res.json();
        if (!res.ok) throw new Error(data.message || 'Gagal membuat sesi');
        aiCurrentSession = data.session_id;
        await aiLoadSessions();
        await aiLoadMessages();
        aiSetStatus('Sesi baru siap.');
    } catch (err) {
        aiSetStatus(err.message, true);
    }
}

async function aiSendMessage(text) {
    if (!aiCurrentSession) {
        await aiNewSession();
    }
    if (!aiCurrentSession) return;
    aiSetStatus('Mengirim...');
    aiSendBtn.disabled = true;
    const typingWrap = document.createElement('div');
    typingWrap.className = 'd-flex mb-2';
    const typingBubble = document.createElement('div');
    typingBubble.className = 'ai-bubble-assistant';
    const typingHead = document.createElement('div');
    typingHead.className = 'ai-head';
    typingHead.textContent = 'AI';
    const typingBody = document.createElement('div');
    typingBody.className = 'small ai-typing';
    typingBody.textContent = 'Mengetik';
    typingBubble.appendChild(typingHead);
    typingBubble.appendChild(typingBody);
    typingWrap.appendChild(typingBubble);
    aiChatMessages.appendChild(typingWrap);
    aiChatMessages.scrollTop = aiChatMessages.scrollHeight;
    try {
        const res = await fetch('api/ai/chat_send.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ session_id: aiCurrentSession, message: text })
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.message || 'Gagal mengirim pesan');
        aiChatMessages.removeChild(typingWrap);
        await aiLoadMessages();
        await aiLoadSessions();
        aiSetStatus(data.anomaly ? 'Anomali terdeteksi' : 'Terkirim');
    } catch (err) {
        aiSetStatus(err.message, true);
        typingBody.classList.remove('ai-typing');
        typingBody.textContent = 'Gagal: ' + err.message;
    } finally {
        aiSendBtn.disabled = false;
    }
}

aiSendBtn.addEventListener('click', async () => {
    const txt = aiChatInput.value.trim();
    if (!txt) return;
    aiChatInput.value = '';
    await aiSendMessage(txt);
});

aiChatInput.addEventListener('keydown', async (e) => {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        aiSendBtn.click();
    }
});

aiNewChatBtn.addEventListener('click', aiNewSession);
aiQuickSummary.addEventListener('click', () => {
    aiChatInput.value = 'Ringkas kondisi energi 24 jam terakhir dan sebut anomali.';
});
aiQuickAnomaly.addEventListener('click', () => {
    aiChatInput.value = 'Cek anomali konsumsi 1 jam terakhir dan langkah mitigasi.';
});

aiCheckStatus();
aiLoadSessions();
</script>
<?php include BASE_PATH . '/partials/footer.php'; ?>
