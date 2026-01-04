<?php
require_once 'auth.php';
include 'partials/header.php';
?>

<div class="page-header mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="page-title mb-0">
                <i class="fas fa-file-alt me-2"></i>Laporan Energi
            </h1>
            <p class="page-subtitle mb-0">Ringkasan bulanan dan analisis tren konsumsi</p>
        </div>
        <button class="btn btn-primary btn-sm" id="reloadReport">
            <i class="fas fa-sync-alt me-2"></i>Perbarui
        </button>
    </div>
</div>

<!-- Summary Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card card-status">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <p class="stat-label">Total 30 Hari</p>
                        <h3 class="stat-value" id="reportTotal">-</h3>
                        <p class="stat-unit">kWh</p>
                    </div>
                    <div style="font-size: 2.5rem; color: rgba(13, 110, 253, 0.2);">
                        <i class="fas fa-calendar-alt"></i>
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
                        <h3 class="stat-value" id="reportAvg" style="color: #0dcaf0;">-</h3>
                        <p class="stat-unit">kWh</p>
                    </div>
                    <div style="font-size: 2.5rem; color: rgba(13, 202, 240, 0.2);">
                        <i class="fas fa-chart-bar"></i>
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
                        <h4 class="stat-value" id="reportStatus" style="color: #6f42c1;">-</h4>
                        <p class="stat-unit" id="reportStatusNote">-</p>
                    </div>
                    <div style="font-size: 2.5rem; color: rgba(111, 66, 193, 0.2);">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Monthly Report Table -->
<div class="card">
    <div class="card-header">
        <h6 class="mb-0">
            <i class="fas fa-table me-2"></i>Laporan Konsumsi Bulanan
        </h6>
        <small class="text-muted">6 bulan terakhir - data agregat per bulan</small>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr class="table-light">
                        <th style="width: 60%;">
                            <i class="fas fa-calendar-check me-2"></i>Bulan
                        </th>
                        <th style="width: 40%;" class="text-end">
                            <i class="fas fa-bolt me-2"></i>Total Konsumsi
                        </th>
                    </tr>
                </thead>
                <tbody id="monthlyTable">
                    <tr>
                        <td colspan="2" class="text-center text-muted py-4">
                            <i class="fas fa-spinner fa-spin me-2"></i>Memuat data laporan...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            <p class="text-muted small mb-0" id="reportInfo"></p>
        </div>
    </div>
</div>

<!-- Info Box -->
<div class="row g-4 mt-2">
    <div class="col-md-6">
        <div class="alert alert-info border-0" role="alert">
            <i class="fas fa-info-circle me-2"></i>
            <strong>Catatan Laporan:</strong> Data menunjukkan konsumsi energi kumulatif per bulan. Gunakan data ini untuk analisis tren dan perencanaan efisiensi.
        </div>
    </div>
    <div class="col-md-6">
        <div class="alert alert-success border-0" role="alert">
            <i class="fas fa-leaf me-2"></i>
            <strong>Tips Hemat:</strong> Monitor tren bulanan untuk identifikasi puncak konsumsi dan ambil tindakan penghematan energi.
        </div>
    </div>
</div>

<script>
const API_BASE = document.body.dataset.apiBase || 'backend/api.php';
const monthlyTable = document.getElementById('monthlyTable');

async function loadSummary() {
    try {
        const res = await fetch(`${API_BASE}?route=energy/summary&days=30`);
        if (!res.ok) throw new Error(`API ${res.status}`);
        const data = await res.json();
        document.getElementById('reportTotal').textContent = data.total_kwh.toLocaleString('id-ID');
        document.getElementById('reportAvg').textContent = data.avg_kwh.toFixed(2);
        document.getElementById('reportStatus').textContent = data.status === 'peringatan' ? '⚠️ Peringatan' : '✅ Normal';
        document.getElementById('reportStatusNote').textContent = data.status === 'peringatan'
            ? 'Konsumsi melampaui batas'
            : 'Dalam kondisi stabil';
    } catch (e) {
        console.error('Gagal memuat ringkasan', e);
    }
}

async function loadMonthly() {
    document.getElementById('reportInfo').textContent = 'Memuat...';
    try {
        const res = await fetch(`${API_BASE}?route=energy/aggregate&mode=monthly&window=6`);
        if (!res.ok) throw new Error(`API ${res.status}`);
        const rows = await res.json();
        monthlyTable.innerHTML = '';
        if (rows.length === 0) {
            monthlyTable.innerHTML = `
                <tr>
                    <td colspan="2" class="text-center text-muted py-4">
                        <i class="fas fa-inbox me-2"></i>Tidak ada data laporan
                    </td>
                </tr>
            `;
            document.getElementById('reportInfo').textContent = '';
            return;
        }
        rows.forEach((r, idx) => {
            const tr = document.createElement('tr');
            const monthYear = new Date(r.label).toLocaleDateString('id-ID', { month: 'long', year: 'numeric' });
            const value = Number(r.total_kwh).toLocaleString('id-ID');
            tr.innerHTML = `
                <td>
                    <span class="badge bg-light text-dark me-2">${idx + 1}</span>
                    ${monthYear}
                </td>
                <td class="text-end">
                    <span class="badge bg-primary">${value} kWh</span>
                </td>
            `;
            monthlyTable.appendChild(tr);
        });
        document.getElementById('reportInfo').innerHTML = `<i class="fas fa-check-circle text-success me-1"></i><strong>${rows.length}</strong> bulan data`;
    } catch (e) {
        document.getElementById('reportInfo').textContent = 'Gagal memuat data laporan';
        monthlyTable.innerHTML = `
            <tr>
                <td colspan="2" class="text-center text-danger py-4">
                    <i class="fas fa-exclamation-circle me-2"></i>Error memuat data
                </td>
            </tr>
        `;
        console.error(e);
    }
}

document.getElementById('reloadReport').addEventListener('click', () => {
    loadSummary();
    loadMonthly();
});

loadSummary();
loadMonthly();
</script>

<?php include 'partials/footer.php'; ?>
