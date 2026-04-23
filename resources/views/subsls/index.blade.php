<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Subsls — {{ config('app.name', 'Laravel') }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
    <style>
        body { background: #f5f6f8; }
        .page-wrap { max-width: 1400px; }
        .table thead th { white-space: nowrap; vertical-align: middle; }
        .table td { vertical-align: middle; }
    </style>
</head>
<body>
<div class="container-fluid py-4 page-wrap">
    <h1 class="h4 mb-3">Monitoring SE2026 BPS Provinsi Sumsel</h1>

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <form id="filter-form">
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="kode_kab">Kabupaten/Kota</label>
                        <select class="form-control" id="kode_kab" name="kode_kab">
                            <option value="">-- Semua Kabupaten/Kota --</option>
                        </select>
                    </div>

                    <div class="form-group col-md-4">
                        <label for="kode_kec">Kecamatan</label>
                        <select class="form-control" id="kode_kec" name="kode_kec" disabled>
                            <option value="">-- Semua Kecamatan --</option>
                        </select>
                    </div>

                    <div class="form-group col-md-4">
                        <label for="kode_desa">Desa/Kelurahan</label>
                        <select class="form-control" id="kode_desa" name="kode_desa" disabled>
                            <option value="">-- Semua Desa/Kelurahan --</option>
                        </select>
                    </div>
                </div>

                <div class="d-flex">
                    <button type="submit" class="btn btn-primary mr-2">Filter</button>
                    <button type="button" id="btn-reset" class="btn btn-outline-secondary">Reset</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover mb-0">
                    <thead class="thead-light">
                    <tr class="text-center">
                        <th>No</th>
                        <th>Nama SLS</th>
                        <th>Kabupaten/Kota</th>
                        <th>Kecamatan</th>
                        <th>Desa/Kelurahan</th>
                        <th>se26_selesai</th>
                        <th>se26_diperiksa</th>
                        <th>se26_is_finish</th>
                    </tr>
                    </thead>
                    <tbody id="subsls-tbody">
                    @forelse ($items as $row)
                        <tr>
                            <td class="text-center">{{ $items->firstItem() + $loop->index }}</td>
                            <td>{{ $row->nama_sls }}</td>
                            <td>{{ $row->kab ?: '—' }}</td>
                            <td>{{ $row->kecamatan ?: '—' }}</td>
                            <td>{{ $row->desa ?: '—' }}</td>
                            <td class="text-center">{{ $row->se26_selesai }}</td>
                            <td class="text-center">{{ $row->se26_diperiksa }}</td>
                            <td class="text-center">{{ $row->se26_is_finish }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">Tidak ada data.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white d-flex justify-content-between align-items-center">
            <small class="text-muted" id="pagination-info">
                Menampilkan {{ $items->firstItem() ?? 0 }} - {{ $items->lastItem() ?? 0 }} dari {{ $items->total() }} data
            </small>
            <ul class="pagination mb-0" id="subsls-pagination"></ul>
        </div>
    </div>
</div>
<script>
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

            if (meta.last_page <= 1) {
                return;
            }

            var makeItem = function (label, page, disabled, active) {
                return '<li class="page-item ' + (disabled ? 'disabled' : '') + ' ' + (active ? 'active' : '') + '">' +
                    '<a class="page-link" href="#" data-page="' + page + '">' + label + '</a></li>';
            };

            paginationEl.innerHTML += makeItem('&laquo;', meta.current_page - 1, meta.current_page <= 1, false);
            for (var i = 1; i <= meta.last_page; i++) {
                paginationEl.innerHTML += makeItem(i, i, false, i === meta.current_page);
            }
            paginationEl.innerHTML += makeItem('&raquo;', meta.current_page + 1, meta.current_page >= meta.last_page, false);
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

        var initialMeta = {
            current_page: {{ $items->currentPage() }},
            last_page: {{ $items->lastPage() }},
            total: {{ $items->total() }},
            from: {{ $items->firstItem() ?? 0 }},
            to: {{ $items->lastItem() ?? 0 }}
        };
        renderPagination(initialMeta);

        loadKabupaten().then(function () {
            setOptions(kecEl, [], '-- Semua Kecamatan --');
            setOptions(desaEl, [], '-- Semua Desa/Kelurahan --');
            kecEl.disabled = true;
            desaEl.disabled = true;
        });
    })();
</script>
</body>
</html>
