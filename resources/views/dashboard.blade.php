@extends('layouts.admin')

@section('content')
<div class="container-fluid mb-5">
    <div class="row align-items-center mb-4">
        <div class="col-md-8">
            <h3 class="fw-bold">Ringkasan Progres Lapangan</h3>
            <div class="d-flex gap-3">
                <p class="text-muted small"><i class="fas fa-database text-info me-1"></i> Data Masuk: <strong>--:-- WIB</strong></p>
                <p class="text-muted small"><i class="fas fa-robot text-primary me-1"></i> Update Klaster: <strong>--:-- WIB</strong></p>
            </div>
        </div>
        <div class="col-md-4 d-flex flex-column align-items-end gap-2">
            <div class="bg-dark rounded-3 py-2 px-3 text-center shadow-sm" style="border: 1px solid #343a40; min-width: 140px;">
                <div class="fw-bold" style="color: #adb5bd; font-size: 0.65rem;">TOTAL WILAYAH</div>
                <div class="fw-bold text-success mb-0" style="font-size: 1.5rem;">0</div>
            </div>
            <button class="btn btn-primary shadow-sm btn-sm" style="min-width: 140px;">
                <i class="fas fa-robot me-1"></i> Klasterisasi Wilayah
            </button>
        </div>
    </div>

    <div class="card mb-4 shadow-sm border-0">
        <div class="card-body p-3 bg-light rounded-3">
            <form id="filter-form">
                <div class="row g-2">
                    <div class="col-md-4">
                        <label for="kode_kab" class="small fw-bold text-muted">Kabupaten/Kota</label>
                        <select id="kode_kab" name="kode_kab" class="form-select form-select-sm border-0 shadow-sm">
                            <option value="">-- Semua Kabupaten/Kota --</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="kode_kec" class="small fw-bold text-muted">Kecamatan</label>
                        <select id="kode_kec" name="kode_kec" class="form-select form-select-sm border-0 shadow-sm" disabled>
                            <option value="">-- Semua Kecamatan --</option>
                        </select>
                    </div>
                    <div class="col-md-4">
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

    <div class="row g-3 mb-3 text-center">
        <div class="col-6 col-md-3"><div class="card p-3 h-100"><h6 class="text-muted small fw-bold mt-2">TOTAL PROGRES MUATAN</h6><div id="chartProgres"></div></div></div>
        <div class="col-6 col-md-3"><div class="card bg-success text-white p-3 h-100"><h6 class="small fw-bold mt-2">WILAYAH LANCAR</h6><h1 class="fw-bold mb-0" style="font-size: 3rem;">0</h1></div></div>
        <div class="col-6 col-md-3"><div class="card bg-warning text-dark p-3 h-100"><h6 class="small fw-bold mt-2">WILAYAH WASPADA</h6><h1 class="fw-bold mb-0" style="font-size: 3rem;">0</h1></div></div>
        <div class="col-6 col-md-3"><div class="card bg-danger text-white p-3 h-100"><h6 class="small fw-bold mt-2">WILAYAH TERKENDALA</h6><h1 class="fw-bold mb-0" style="font-size: 3rem;">0</h1></div></div>
    </div>

    <div class="table-container shadow-sm bg-white p-4 rounded-3 border-0">
        <h5 class="fw-bold mb-3"><i class="fas fa-map-marked-alt text-primary"></i> Detail Progres Wilayah</h5>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr class="small text-uppercase">
                        <th class="text-center">No</th>
                        <th>Nama SLS</th>
                        <th>Kabupaten/Kota</th>
                        <th>Kecamatan</th>
                        <th>Desa/Kelurahan</th>
                        <th class="text-center">Jumlah Selesai</th>
                        <th class="text-center">Jumlah Diperiksa</th>
                        <th class="text-center">Status SLS Selesai</th>
                    </tr>
                </thead>
                <tbody id="subsls-tbody">
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            Memuat data...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-between align-items-center pt-2 small">
            <span class="text-muted" id="pagination-info">
                Menampilkan 0 - 0 dari 0 data
            </span>
            <ul class="pagination pagination-sm mb-0" id="subsls-pagination"></ul>
        </div>
    </div>
</div>

<script>
new ApexCharts(document.querySelector("#chartProgres"), {
    series: [0],
    chart: { height: 180, type: 'radialBar' },
    plotOptions: { radialBar: { hollow: { size: '60%' }, dataLabels: { name: { show: false }, value: { formatter: function (val) { return val + "%"; } } } } },
    colors: ['#0d6efd']
}).render();

(function () {
    var state = {
        page: 1,
        kode_kab: '',
        kode_kec: '',
        kode_desa: ''
    };

    var form = document.getElementById('filter-form');
    var kabEl = document.getElementById('kode_kab');
    var kecEl = document.getElementById('kode_kec');
    var desaEl = document.getElementById('kode_desa');
    var tbodyEl = document.getElementById('subsls-tbody');
    var paginationEl = document.getElementById('subsls-pagination');
    var infoEl = document.getElementById('pagination-info');
    var resetEl = document.getElementById('btn-reset');

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

    function renderTable(rows, from) {
        if (!rows.length) {
            tbodyEl.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-4">Tidak ada data.</td></tr>';
            return;
        }

        tbodyEl.innerHTML = rows.map(function (row, idx) {
            var nomor = (from || 1) + idx;
            var finish = row.se26_is_finish !== undefined ? row.se26_is_finish : row.se2026_is_finish;
            return '<tr>' +
                '<td class="text-center">' + nomor + '</td>' +
                '<td>' + (row.nama_sls || '—') + '</td>' +
                '<td>' + (row.kab || '—') + '</td>' +
                '<td>' + (row.kecamatan || '—') + '</td>' +
                '<td>' + (row.desa || '—') + '</td>' +
                '<td class="text-center">' + (row.se26_selesai ?? 0) + '</td>' +
                '<td class="text-center">' + (row.se26_diperiksa ?? 0) + '</td>' +
                '<td class="text-center">' + (finish ?? 0) + '</td>' +
                '</tr>';
        }).join('');
    }

    function renderPagination(meta) {
        paginationEl.innerHTML = '';
        infoEl.textContent = 'Menampilkan ' + (meta.from || 0) + ' - ' + (meta.to || 0) + ' dari ' + meta.total + ' data';

        var last = meta.last_page || 1;
        if (last <= 1) {
            return;
        }

        var current = meta.current_page || 1;
        var delta = 2;

        var makeItem = function (label, page, disabled, active) {
            return '<li class="page-item ' + (disabled ? 'disabled' : '') + ' ' + (active ? 'active' : '') + '">' +
                '<a class="page-link" href="#" data-page="' + page + '">' + label + '</a></li>';
        };

        var makeEllipsis = function () {
            return '<li class="page-item disabled"><span class="page-link user-select-none">&hellip;</span></li>';
        };

        var pageSet = {};
        pageSet[1] = true;
        pageSet[last] = true;
        for (var i = current - delta; i <= current + delta; i++) {
            if (i >= 1 && i <= last) {
                pageSet[i] = true;
            }
        }
        var pages = Object.keys(pageSet).map(function (k) { return parseInt(k, 10); }).sort(function (a, b) { return a - b; });

        paginationEl.innerHTML += makeItem('&laquo;', current - 1, current <= 1, false);

        var prev = 0;
        for (var j = 0; j < pages.length; j++) {
            var p = pages[j];
            if (prev && p - prev > 1) {
                paginationEl.innerHTML += makeEllipsis();
            }
            paginationEl.innerHTML += makeItem(String(p), p, false, p === current);
            prev = p;
        }

        paginationEl.innerHTML += makeItem('&raquo;', current + 1, current >= last, false);
    }

    function loadSubsls(page) {
        state.page = page || 1;
        var url = buildUrl('{{ url('/subsls/data') }}', {
            page: state.page,
            kode_kab: state.kode_kab,
            kode_kec: state.kode_kec,
            kode_desa: state.kode_desa
        });

        return fetch(url)
            .then(function (res) { return res.json(); })
            .then(function (json) {
                renderTable(json.data || [], json.meta ? json.meta.from : 1);
                renderPagination(json.meta || {current_page: 1, last_page: 1, total: 0, from: 0, to: 0});
            });
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
        loadSubsls(1);
    });

    paginationEl.addEventListener('click', function (e) {
        var target = e.target;
        if (!target.matches('a.page-link')) {
            return;
        }
        e.preventDefault();
        var page = parseInt(target.getAttribute('data-page') || '1', 10);
        if (isNaN(page) || page < 1) {
            return;
        }
        loadSubsls(page);
    });

    resetEl.addEventListener('click', function () {
        state.kode_kab = '';
        state.kode_kec = '';
        state.kode_desa = '';
        kabEl.value = '';
        setOptions(kecEl, [], '-- Semua Kecamatan --');
        kecEl.disabled = true;
        setOptions(desaEl, [], '-- Semua Desa/Kelurahan --');
        desaEl.disabled = true;
        loadSubsls(1);
    });

    // Initial state
    renderPagination({ current_page: 1, last_page: 1, total: 0, from: 0, to: 0 });

    loadKabupaten().then(function () {
        setOptions(kecEl, [], '-- Semua Kecamatan --');
        setOptions(desaEl, [], '-- Semua Desa/Kelurahan --');
        kecEl.disabled = true;
        desaEl.disabled = true;
    });

    // load first page
    loadSubsls(1);
})();
</script>
@endsection
