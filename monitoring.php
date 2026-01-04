<?php
require_once 'auth.php';
include 'partials/header.php';
?>

<div class="page-header mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="page-title mb-0">
                <i class="fas fa-eye me-2"></i>Monitoring Energi Real-Time
            </h1>
            <p class="page-subtitle mb-0">Data sensor energi dengan filter tanggal</p>
        </div>
        <button class="btn btn-primary btn-sm" id="refreshBtn">
            <i class="fas fa-sync-alt me-2"></i>Muat Ulang
        </button>
    </div>
</div>

<!-- Filter Card -->
<div class="card mb-4">
    <div class="card-header">
        <h6 class="mb-0">
            <i class="fas fa-filter me-2"></i>Filter Data
        </h6>
    </div>
    <div class="card-body">
        <form class="row g-3 align-items-end" id="filterForm">
            <div class="col-md-5">
                <label class="form-label" for="startDate">
                    <i class="fas fa-calendar-alt me-1"></i>Tanggal Mulai
                </label>
                <input type="date" class="form-control" id="startDate">
            </div>
            <div class="col-md-5">
                <label class="form-label" for="endDate">
                    <i class="fas fa-calendar-alt me-1"></i>Tanggal Selesai
                </label>
                <input type="date" class="form-control" id="endDate">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search me-1"></i>Cari
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Data Table Card -->
<div class="card">
    <div class="card-header">
        <h6 class="mb-0">
            <i class="fas fa-table me-2"></i>Tabel Data Energi
        </h6>
    </div>
    <div class="card-body">
        <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
            <table class="table table-hover table-sm align-middle">
                <thead class="table-sticky">
                    <tr>
                        <th style="width: 5%;">
                            <i class="fas fa-hashtag me-1"></i>#
                        </th>
                        <th style="width: 50%;">
                            <i class="fas fa-clock me-1"></i>Timestamp
                        </th>
                        <th style="width: 30%;">
                            <i class="fas fa-bolt me-1"></i>Konsumsi
                        </th>
                        <th style="width: 15%;" class="text-center">
                            <i class="fas fa-info-circle me-1"></i>Status
                        </th>
                    </tr>
                </thead>
                <tbody id="energyTable">
                    <tr>
                        <td colspan="4" class="text-center text-muted py-5">
                            <i class="fas fa-spinner fa-spin me-2"></i>Memuat data...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="mt-3 d-flex justify-content-between align-items-center">
            <p class="text-muted small mb-0" id="tableInfo"></p>
            <small class="text-muted">Otomatis meng-refresh setiap 30 detik</small>
        </div>
    </div>
</div>

<script>
const API_BASE = document.body.dataset.apiBase || 'backend/api.php';
const tableBody = document.getElementById('energyTable');
const tableInfo = document.getElementById('tableInfo');
let simT = 0;
let simId = 100000;

function renderRows(rows) {
    tableBody.innerHTML = '';
    if (rows.length === 0) {
        tableBody.innerHTML = `
            <tr>
                <td colspan="4" class="text-center text-muted py-5">
                    <i class="fas fa-inbox me-2"></i>Tidak ada data ditemukan
                </td>
            </tr>
        `;
        tableInfo.textContent = '';
        return;
    }
    rows.forEach((row, idx) => {
        const tr = document.createElement('tr');
        const date = new Date(row.reading_time);
        const status = row.energy_kwh > 50 ? 
            '<span class="badge bg-warning text-dark">Tinggi</span>' : 
            '<span class="badge bg-success">Normal</span>';
        tr.innerHTML = `
            <td class="fw-bold">${idx + 1}</td>
            <td>
                <small class="text-muted">${date.toLocaleString('id-ID')}</small>
            </td>
            <td>
                <span class="badge bg-primary">${row.energy_kwh} kWh</span>
            </td>
            <td class="text-center">${status}</td>
        `;
        tableBody.appendChild(tr);
    });
    tableInfo.innerHTML = `<i class="fas fa-check-circle text-success me-1"></i><strong>${rows.length}</strong> sampel ditampilkan`;
}

async function loadData(params = '') {
    tableBody.innerHTML = `
        <tr>
            <td colspan="4" class="text-center text-muted py-5">
                <i class="fas fa-spinner fa-spin me-2"></i>Memuat...
            </td>
        </tr>
    `;
    try {
        let url = `${API_BASE}?route=energy/readings`;
        if (params) {
            const qp = params.startsWith('?') ? params.slice(1) : params;
            url += '&' + qp;
        }
        const res = await fetch(url);
        if (!res.ok) throw new Error(`API ${res.status}`);
        const rows = await res.json();
        renderRows(rows);
    } catch (e) {
        tableBody.innerHTML = `
            <tr>
                <td colspan="4" class="text-center text-danger py-5">
                    <i class="fas fa-exclamation-circle me-2"></i>Gagal memuat data. Pastikan API berjalan.
                </td>
            </tr>
        `;
        console.error(e);
    }
}

document.getElementById('filterForm').addEventListener('submit', (e) => {
    e.preventDefault();
    const start = document.getElementById('startDate').value;
    const end = document.getElementById('endDate').value;
    const query = [];
    if (start) query.push(`startDate=${start}`);
    if (end) query.push(`endDate=${end}`);
    const params = query.length ? `?${query.join('&')}` : '';
    loadData(params);
});

document.getElementById('refreshBtn').addEventListener('click', () => loadData(''));

// Auto-refresh setiap 30 detik
loadData('');
setInterval(() => loadData(''), 30000);

// Hilangkan simulasi pada halaman Monitoring: hanya data API yang ditampilkan
</script>

<?php include 'partials/footer.php'; ?>
