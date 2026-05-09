@extends('layouts.admin')

@section('content')
<div class="container-fluid mb-5">
    <div class="row align-items-center mb-4">
        <div class="col-md-8">
            <h3 class="fw-bold">Rekapitulasi Progres Wilayah</h3>
            <p class="text-muted small mb-0">
                Rekap otomatis berdasarkan filter wilayah (Provinsi > Kabupaten > Kecamatan > Desa).
            </p>
        </div>
    </div>

    <div class="card mb-4 shadow-sm border-0">
        <div class="card-body p-3 bg-light rounded-3">
            <form id="rekap-filter-form">
                <div class="row g-2">
                    <div class="col-md-3">
                        <label for="kode_prov" class="small fw-bold text-muted">Provinsi</label>
                        <select id="kode_prov" name="kode_prov" class="form-select form-select-sm border-0 shadow-sm">
                            <option value="16">16 - Sumatera Selatan</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="kode_kab" class="small fw-bold text-muted">Kabupaten/Kota</label>
                        <select id="kode_kab" name="kode_kab" class="form-select form-select-sm border-0 shadow-sm">
                            <option value="">-- Semua Kabupaten/Kota --</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="kode_kec" class="small fw-bold text-muted">Kecamatan</label>
                        <select id="kode_kec" name="kode_kec" class="form-select form-select-sm border-0 shadow-sm" disabled>
                            <option value="">-- Semua Kecamatan --</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="kode_desa" class="small fw-bold text-muted">Desa/Kelurahan</label>
                        <select id="kode_desa" name="kode_desa" class="form-select form-select-sm border-0 shadow-sm" disabled>
                            <option value="">-- Semua Desa/Kelurahan --</option>
                        </select>
                    </div>
                </div>
                <div class="d-flex justify-content-end mt-3 gap-2">
                    <button type="submit" class="btn btn-dark btn-sm shadow-sm">Terapkan Filter</button>
                    <button type="button" id="btn-reset" class="btn btn-outline-secondary btn-sm shadow-sm">Reset</button>
                </div>
            </form>
        </div>
    </div>

    <p class="text-muted small mb-3" id="rekap-total-sls">Total SLS: —</p>

    <div class="row g-3 mb-4 text-center">
        <div class="col-6 col-md-3">
            <div class="card p-3 h-100 shadow-sm border-0">
                <h6 class="text-muted small fw-bold mt-2">Total Progres Muatan</h6>
                <div id="chartRekapProgres"></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card bg-success text-white p-3 h-100 shadow-sm border-0">
                <h6 class="small fw-bold mt-2">Persentase Selesai</h6>
                <h1 class="fw-bold mb-0" style="font-size: 3rem;" id="pct-selesai">0%</h1>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card bg-warning text-dark p-3 h-100 shadow-sm border-0">
                <h6 class="small fw-bold mt-2">Persentase Sedang Dikerjakan</h6>
                <h1 class="fw-bold mb-0" style="font-size: 3rem;" id="pct-sedang">0%</h1>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card bg-danger text-white p-3 h-100 shadow-sm border-0">
                <h6 class="small fw-bold mt-2">Persentase Belum Dikerjakan</h6>
                <h1 class="fw-bold mb-0" style="font-size: 3rem;" id="pct-belum">0%</h1>
            </div>
        </div>
    </div>

    <div class="table-container shadow-sm bg-white p-4 rounded-3 border-0">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0"><i class="fas fa-table text-primary"></i> Tabel Rekapitulasi</h5>
            <span class="badge bg-primary-subtle text-primary-emphasis" id="rekap-level-label">Level: -</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr class="small text-uppercase">
                        <th class="text-center">No</th>
                        <th>Kode</th>
                        <th>Nama Wilayah/SLS</th>
                        <th class="text-center">Total Data</th>
                        <th class="text-center">Total Selesai</th>
                        <th class="text-center">Total Diperiksa</th>
                        <th class="text-center">Total SLS Selesai (%)</th>
                    </tr>
                </thead>
                <tbody id="rekap-tbody">
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">Memuat data...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
(function () {
    var state = {
        kode_prov: '16',
        kode_kab: '',
        kode_kec: '',
        kode_desa: ''
    };

    var form = document.getElementById('rekap-filter-form');
    var provEl = document.getElementById('kode_prov');
    var kabEl = document.getElementById('kode_kab');
    var kecEl = document.getElementById('kode_kec');
    var desaEl = document.getElementById('kode_desa');
    var tbodyEl = document.getElementById('rekap-tbody');
    var resetEl = document.getElementById('btn-reset');
    var levelLabelEl = document.getElementById('rekap-level-label');
    var totalSlsEl = document.getElementById('rekap-total-sls');
    var pctSelesaiEl = document.getElementById('pct-selesai');
    var pctSedangEl = document.getElementById('pct-sedang');
    var pctBelumEl = document.getElementById('pct-belum');
    var rekapProgresChart = null;

    function buildUrl(base, params) {
        var url = new URL(base, window.location.origin);
        Object.keys(params).forEach(function (key) {
            if (params[key] !== '' && params[key] !== null && params[key] !== undefined) {
                url.searchParams.set(key, params[key]);
            }
        });
        return url.toString();
    }

    function setOptions(selectEl, options, placeholder) {
        selectEl.innerHTML = '';
        var firstOpt = document.createElement('option');
        firstOpt.value = '';
        firstOpt.textContent = placeholder;
        selectEl.appendChild(firstOpt);

        options.forEach(function (item) {
            var opt = document.createElement('option');
            opt.value = item.value;
            opt.textContent = item.kode_bps ? (item.kode_bps + ' - ' + item.label) : item.label;
            selectEl.appendChild(opt);
        });
    }

    function labelLevel(level) {
        var mapping = {
            kabupaten: 'Kabupaten/Kota',
            kecamatan: 'Kecamatan',
            desa: 'Desa/Kelurahan',
            sls: 'SLS'
        };
        return mapping[level] || '-';
    }

    function rowCode(row, level) {
        if (level === 'kabupaten') return row.kode_kab || '-';
        if (level === 'kecamatan') return row.kode_kec || '-';
        if (level === 'desa') return row.kode_desa || '-';
        if (level === 'sls') return row.kode_sls || '-';
        return '-';
    }

    function rowName(row, level) {
        if (level === 'sls') return row.nama_sls || '-';
        return row.nama_wilayah || '-';
    }

    function renderSummary(summary) {
        summary = summary || {};
        var total = Number(summary.total_sls ?? 0);
        totalSlsEl.textContent = 'Total SLS: ' + total;

        var pMuatan = Number(summary.persen_total_progres_muatan ?? 0);
        var pSelesai = Number(summary.persen_selesai ?? 0);
        var pSedang = Number(summary.persen_sedang_dikerjakan ?? 0);
        var pBelum = Number(summary.persen_belum_dikerjakan ?? 0);

        pctSelesaiEl.textContent = pSelesai.toFixed(2) + '%';
        pctSedangEl.textContent = pSedang.toFixed(2) + '%';
        pctBelumEl.textContent = pBelum.toFixed(2) + '%';

        if (typeof ApexCharts === 'undefined') {
            return;
        }

        if (!rekapProgresChart) {
            rekapProgresChart = new ApexCharts(document.querySelector('#chartRekapProgres'), {
                series: [pMuatan],
                chart: { height: 180, type: 'radialBar' },
                plotOptions: {
                    radialBar: {
                        hollow: { size: '60%' },
                        dataLabels: {
                            name: { show: false },
                            value: { formatter: function (val) { return val + '%'; } }
                        }
                    }
                },
                colors: ['#0d6efd']
            });
            rekapProgresChart.render();
        } else {
            rekapProgresChart.updateSeries([pMuatan]);
        }
    }

    function renderTable(level, rows) {
        levelLabelEl.textContent = 'Level: ' + labelLevel(level);

        if (!rows || !rows.length) {
            tbodyEl.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4">Tidak ada data.</td></tr>';
            return;
        }

        var totalData = 0;
        var totalSelesai = 0;
        var totalDiperiksa = 0;
        var totalFinish = 0;

        var bodyRows = rows.map(function (row, idx) {
            var dataCount = Number(row.total_data ?? 0);
            var selesai = Number(row.total_se26_selesai ?? 0);
            var diperiksa = Number(row.total_se26_diperiksa ?? 0);
            var finish = Number(row.total_se26_is_finish ?? 0);
            var percentage = dataCount > 0 ? ((finish / dataCount) * 100) : 0;

            totalData += dataCount;
            totalSelesai += selesai;
            totalDiperiksa += diperiksa;
            totalFinish += finish;

            return '<tr>' +
                '<td class="text-center">' + (idx + 1) + '</td>' +
                '<td>' + rowCode(row, level) + '</td>' +
                '<td>' + rowName(row, level) + '</td>' +
                '<td class="text-center">' + dataCount + '</td>' +
                '<td class="text-center">' + selesai + '</td>' +
                '<td class="text-center">' + diperiksa + '</td>' +
                '<td class="text-center">' + finish + ' (' + percentage.toFixed(2) + '%)</td>' +
                '</tr>';
        }).join('');

        var totalPercentage = totalData > 0 ? ((totalFinish / totalData) * 100) : 0;

        var totalRow = '' +
            '<tr class="table-light fw-bold">' +
            '<td class="text-center">#</td>' +
            '<td colspan="2">Total</td>' +
            '<td class="text-center">' + totalData + '</td>' +
            '<td class="text-center">' + totalSelesai + '</td>' +
            '<td class="text-center">' + totalDiperiksa + '</td>' +
            '<td class="text-center">' + totalFinish + ' (' + totalPercentage.toFixed(2) + '%)</td>' +
            '</tr>';

        tbodyEl.innerHTML = bodyRows + totalRow;
    }

    function loadKabupaten() {
        return fetch('{{ url('/subsls/options/kabupaten') }}')
            .then(function (res) { return res.json(); })
            .then(function (json) {
                setOptions(kabEl, json.data || [], '-- Semua Kabupaten/Kota --');
            });
    }

    function loadKecamatan() {
        if (!state.kode_kab) {
            setOptions(kecEl, [], '-- Semua Kecamatan --');
            kecEl.disabled = true;
            return Promise.resolve();
        }

        var url = buildUrl('{{ url('/subsls/options/kecamatan') }}', { kode_kab: state.kode_kab });
        return fetch(url)
            .then(function (res) { return res.json(); })
            .then(function (json) {
                setOptions(kecEl, json.data || [], '-- Semua Kecamatan --');
                kecEl.disabled = false;
            });
    }

    function loadDesa() {
        if (!state.kode_kab || !state.kode_kec) {
            setOptions(desaEl, [], '-- Semua Desa/Kelurahan --');
            desaEl.disabled = true;
            return Promise.resolve();
        }

        var url = buildUrl('{{ url('/subsls/options/desa') }}', {
            kode_kab: state.kode_kab,
            kode_kec: state.kode_kec
        });
        return fetch(url)
            .then(function (res) { return res.json(); })
            .then(function (json) {
                setOptions(desaEl, json.data || [], '-- Semua Desa/Kelurahan --');
                desaEl.disabled = false;
            });
    }

    function loadRekap() {
        var url = buildUrl('{{ url('/subsls/rekap-data') }}', {
            kode_prov: state.kode_prov,
            kode_kab: state.kode_kab,
            kode_kec: state.kode_kec,
            kode_desa: state.kode_desa
        });

        return fetch(url)
            .then(function (res) {
                return res.json().then(function (body) {
                    return { ok: res.ok, body: body };
                });
            })
            .then(function (r) {
                if (! r.ok) {
                    renderSummary({});
                    levelLabelEl.textContent = 'Level: —';
                    tbodyEl.innerHTML = '<tr><td colspan="7" class="text-center text-danger py-4">' +
                        (r.body && r.body.message ? r.body.message : 'Gagal memuat data.') + '</td></tr>';
                    return;
                }
                var json = r.body;
                renderSummary(json.summary || {});
                renderTable(json.level || '-', json.data || []);
            });
    }

    provEl.addEventListener('change', function () {
        state.kode_prov = provEl.value || '16';
        state.kode_kab = '';
        state.kode_kec = '';
        state.kode_desa = '';
        kabEl.value = '';
        setOptions(kecEl, [], '-- Semua Kecamatan --');
        kecEl.disabled = true;
        setOptions(desaEl, [], '-- Semua Desa/Kelurahan --');
        desaEl.disabled = true;
    });

    kabEl.addEventListener('change', function () {
        state.kode_kab = kabEl.value;
        state.kode_kec = '';
        state.kode_desa = '';
        kecEl.value = '';
        desaEl.value = '';
        loadKecamatan().then(loadDesa);
    });

    kecEl.addEventListener('change', function () {
        state.kode_kec = kecEl.value;
        state.kode_desa = '';
        desaEl.value = '';
        loadDesa();
    });

    desaEl.addEventListener('change', function () {
        state.kode_desa = desaEl.value;
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        loadRekap();
    });

    resetEl.addEventListener('click', function () {
        state.kode_prov = '16';
        state.kode_kab = '';
        state.kode_kec = '';
        state.kode_desa = '';

        provEl.value = '16';
        kabEl.value = '';
        setOptions(kecEl, [], '-- Semua Kecamatan --');
        kecEl.disabled = true;
        setOptions(desaEl, [], '-- Semua Desa/Kelurahan --');
        desaEl.disabled = true;
        loadRekap();
    });

    loadKabupaten().then(function () {
        setOptions(kecEl, [], '-- Semua Kecamatan --');
        setOptions(desaEl, [], '-- Semua Desa/Kelurahan --');
        kecEl.disabled = true;
        desaEl.disabled = true;
        loadRekap();
    });
})();
</script>
@endsection
