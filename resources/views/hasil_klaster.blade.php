@extends('layouts.admin')

@section('content')
<div class="container-fluid mb-5">
    <div class="card shadow-sm border-0 mb-4 bg-dark text-white p-4" style="border-radius: 15px;">
        <div class="row g-3 align-items-center">
            <div class="col-md-4 border-end border-secondary pe-4">
                <div class="d-flex align-items-center mb-3">
                    <i class="fas fa-brain fa-2x text-success me-3"></i>
                    <div>
                        <h4 class="fw-bold mb-0 text-success">Hasil Klaster AI</h4>
                        <p class="small mb-0 opacity-75">Status: <span class="text-warning fw-bold">Semua Wilayah</span></p>
                    </div>
                </div>
                <button class="btn btn-success btn-sm w-100 py-2 shadow-lg fw-bold" style="border-radius: 10px;">
                    <i class="fas fa-robot me-2"></i> Jalankan Klasterisasi Terbaru
                </button>
            </div>
            <div class="col-md-6 px-4">
                <div class="row g-2">
                    <div class="col-md-4"><label class="x-small opacity-75 d-block mb-1" style="font-size: 0.7rem;">Filter Tanggal</label><input type="date" class="form-control form-control-sm bg-secondary text-white border-0"></div>
                    <div class="col-md-4"><label class="x-small opacity-75 d-block mb-1" style="font-size: 0.7rem;">Kabupaten</label><select class="form-select form-select-sm border-0"><option>-- Semua --</option></select></div>
                    <div class="col-md-4"><label class="x-small opacity-75 d-block mb-1" style="font-size: 0.7rem;">Kecamatan</label><select class="form-select form-select-sm border-0"><option>-- Semua --</option></select></div>
                </div>
            </div>
            <div class="col-md-2 text-center border-start border-secondary ps-4">
                <div class="small opacity-75 mb-1">Total Wilayah</div>
                <div class="h3 fw-bold text-success mb-0">0</div>
                <small class="text-muted" style="font-size: 0.6rem;">Terklasterisasi</small>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6"><div class="card shadow-sm border-0 h-100"><div class="card-header bg-white py-3 border-0"><h6 class="fw-bold mb-0"><i class="fas fa-chart-pie me-2 text-success"></i>Proporsi Klaster Wilayah</h6></div><div class="card-body"><div id="chartDistribusi"></div></div></div></div>
        <div class="col-md-6"><div class="card shadow-sm border-0 h-100"><div class="card-header bg-white py-3 border-0"><h6 class="fw-bold mb-0"><i class="fas fa-chart-line me-2 text-primary"></i>Rata-Rata Progres (%) per Klaster</h6></div><div class="card-body"><div id="chartRata"></div></div></div></div>
    </div>

    <div class="card shadow-sm border-0 mb-4 overflow-hidden">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center gap-3">
            <h6 class="fw-bold mb-0"><i class="fas fa-map-marked-alt text-danger me-2"></i>Analisis Kecamatan</h6>
            <div class="btn-group shadow-sm">
                <button type="button" class="btn btn-sm btn-outline-dark active">Hasil Klaster</button>
                <button type="button" class="btn btn-sm btn-outline-dark">Rata-rata Progres</button>
                <button type="button" class="btn btn-sm btn-outline-dark">Total Kendala</button>
            </div>
        </div>
        <div class="card-body p-0">
            <div style="height: 500px; overflow-y: auto; padding: 15px;">
                <div id="bigMainChart"></div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-dark text-white py-3 px-4">
            <h6 class="fw-bold mb-0 text-white"><i class="fas fa-calendar-alt me-2 text-info"></i>Detail Data Per Hari</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light fw-bold">
                        <tr class="text-dark small text-uppercase">
                            <th class="ps-4">Lokasi (Kec / Desa)</th>
                            <th>Nama SLS / ID</th>
                            <th class="text-center">Muatan</th>
                            <th class="text-center">Selesai</th>
                            <th class="text-center">Diperiksa</th>
                            <th>Persentase (%)</th>
                            <th class="text-center">Kendala</th>
                            <th class="text-center pe-4">Klaster AI</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td colspan="8" class="text-center py-5 text-muted">Template layout siap, data belum diisi.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
new ApexCharts(document.querySelector("#chartDistribusi"), {
    series: [0, 0, 0],
    labels: ['Lancar', 'Waspada', 'Terkendala'],
    chart: { type: 'pie', height: 250 },
    colors: ['#198754', '#ffc107', '#dc3545']
}).render();

new ApexCharts(document.querySelector("#chartRata"), {
    series: [{ name: 'Rata-rata % Selesai', data: [0, 0, 0] }],
    chart: { type: 'bar', height: 250, toolbar: { show: false } },
    colors: ['#198754', '#ffc107', '#dc3545'],
    plotOptions: { bar: { distributed: true, borderRadius: 4, columnWidth: '50%' } },
    xaxis: { categories: ['Lancar', 'Waspada', 'Terkendala'] }
}).render();

new ApexCharts(document.querySelector("#bigMainChart"), {
    series: [{ name: 'Lancar', data: [0, 0, 0] }, { name: 'Waspada', data: [0, 0, 0] }, { name: 'Terkendala', data: [0, 0, 0] }],
    chart: { type: 'bar', height: 450, stacked: true },
    plotOptions: { bar: { horizontal: true } },
    xaxis: { categories: ['Wilayah A', 'Wilayah B', 'Wilayah C'] },
    colors: ['#329c68', '#f1c40f', '#e74c3c']
}).render();
</script>
@endsection
