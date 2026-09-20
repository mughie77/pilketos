<?php
require_once __DIR__ . '/config.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Count - Pemilihan Ketua OSIS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen flex flex-col justify-between selection:bg-indigo-500 selection:text-white relative">

    <!-- Subtle Background Accent Shapes -->
    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-7xl h-64 bg-gradient-to-b from-indigo-50/80 via-purple-50/40 to-transparent pointer-events-none"></div>

    <!-- Header Navigation -->
    <header class="bg-white/80 backdrop-blur-xl border-b border-slate-200/80 sticky top-0 z-40 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3.5 flex items-center justify-between">
            <div class="flex items-center space-x-3.5">
                <div class="w-11 h-11 bg-gradient-to-tr from-indigo-600 to-purple-600 rounded-2xl flex items-center justify-center shadow-md shadow-indigo-500/20">
                    <i class="fa-solid fa-chart-column text-xl text-white"></i>
                </div>
                <div>
                    <div class="flex items-center space-x-2">
                        <span class="font-extrabold text-lg text-slate-900 tracking-tight">PEMILU OSIS</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-2xs font-bold bg-emerald-100 text-emerald-700 border border-emerald-200 animate-pulse">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1"></span> LIVE COUNT
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 font-medium">Hasil Perhitungan Suara Realtime</p>
                </div>
            </div>

            <div class="flex items-center space-x-3">
                <a href="<?= base_url('hash_generator.php') ?>" class="p-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 hover:text-slate-900 rounded-xl border border-slate-200 text-xs transition" title="Hash Generator">
                    <i class="fa-solid fa-key text-sm"></i>
                </a>
                <a href="<?= base_url('voter/login.php') ?>" class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl text-xs sm:text-sm shadow-md shadow-indigo-600/20 transition duration-200 transform hover:-translate-y-0.5 flex items-center space-x-2">
                    <i class="fa-solid fa-vote-yea"></i>
                    <span>Masuk Bilik Suara</span>
                </a>
                <a href="<?= base_url('admin/login.php') ?>" class="p-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 hover:text-slate-900 rounded-xl border border-slate-200 text-xs transition" title="Login Admin">
                    <i class="fa-solid fa-user-shield text-sm"></i>
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-1 space-y-8 relative z-10">

        <!-- Hero & Refresh Indicator Bar -->
        <div class="flex flex-col md:flex-row items-center justify-between gap-4 bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm">
            <div>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">Realtime Quick Count OSIS</h1>
                <p class="text-slate-500 text-xs mt-1">Data diperbarui secara otomatis setiap <span class="text-indigo-600 font-bold">5 detik</span> via AJAX</p>
            </div>

            <div class="flex items-center space-x-3 bg-slate-50 px-4 py-2 rounded-xl border border-slate-200">
                <div class="w-3 h-3 rounded-full bg-indigo-600 animate-ping"></div>
                <span class="text-xs text-slate-600 font-mono">Update Terakhir: <span id="lastUpdate" class="font-bold text-slate-900">--:--:--</span></span>
            </div>
        </div>

        <!-- Metric Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Total DPT -->
            <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm hover:shadow-md hover:border-slate-300 transition">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total DPT Siswa</span>
                    <div class="w-10 h-10 bg-indigo-50 border border-indigo-100 text-indigo-600 rounded-xl flex items-center justify-center">
                        <i class="fa-solid fa-users"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-baseline justify-between">
                    <span id="statTotalPemilih" class="text-3xl font-extrabold text-slate-900">0</span>
                    <span class="text-2xs text-slate-400">Siswa Terdaftar</span>
                </div>
            </div>

            <!-- Sudah Memilih -->
            <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm hover:shadow-md hover:border-slate-300 transition">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Suara Masuk</span>
                    <div class="w-10 h-10 bg-emerald-50 border border-emerald-100 text-emerald-600 rounded-xl flex items-center justify-center">
                        <i class="fa-solid fa-vote-yea"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-baseline justify-between">
                    <span id="statTotalSudah" class="text-3xl font-extrabold text-emerald-600">0</span>
                    <span id="statPersentase" class="text-xs font-bold text-emerald-700 bg-emerald-100/80 px-2.5 py-0.5 rounded-md">0%</span>
                </div>
            </div>

            <!-- Belum Memilih -->
            <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm hover:shadow-md hover:border-slate-300 transition">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Belum Memilih</span>
                    <div class="w-10 h-10 bg-amber-50 border border-amber-100 text-amber-600 rounded-xl flex items-center justify-center">
                        <i class="fa-solid fa-hourglass-half"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-baseline justify-between">
                    <span id="statTotalBelum" class="text-3xl font-extrabold text-amber-600">0</span>
                    <span class="text-2xs text-slate-400">Siswa</span>
                </div>
            </div>

            <!-- Partisipasi -->
            <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm hover:shadow-md hover:border-slate-300 transition">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Tingkat Partisipasi</span>
                    <div class="w-10 h-10 bg-purple-50 border border-purple-100 text-purple-600 rounded-xl flex items-center justify-center">
                        <i class="fa-solid fa-chart-line"></i>
                    </div>
                </div>
                <div class="mt-4">
                    <div class="w-full bg-slate-100 rounded-full h-3.5 border border-slate-200 overflow-hidden p-0.5">
                        <div id="progressBar" class="bg-gradient-to-r from-indigo-500 to-purple-600 h-full rounded-full transition-all duration-700" style="width: 0%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart Section -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Bar / Column Chart -->
            <div class="lg:col-span-2 bg-white border border-slate-200/80 rounded-3xl p-6 shadow-sm flex flex-col justify-between">
                <div class="flex items-center justify-between mb-6 pb-4 border-b border-slate-100">
                    <h3 class="text-lg font-bold text-slate-900 flex items-center space-x-2">
                        <i class="fa-solid fa-chart-simple text-indigo-600"></i>
                        <span>Grafik Perolehan Suara Paslon</span>
                    </h3>
                    <span class="text-xs text-slate-400">Bar Chart Realtime</span>
                </div>
                <div class="relative h-72 sm:h-80 w-full flex items-center justify-center">
                    <canvas id="barChart"></canvas>
                </div>
            </div>

            <!-- Doughnut Chart -->
            <div class="bg-white border border-slate-200/80 rounded-3xl p-6 shadow-sm flex flex-col justify-between">
                <div class="flex items-center justify-between mb-6 pb-4 border-b border-slate-100">
                    <h3 class="text-lg font-bold text-slate-900 flex items-center space-x-2">
                        <i class="fa-solid fa-chart-pie text-purple-600"></i>
                        <span>Persentase Suara</span>
                    </h3>
                    <span class="text-xs text-slate-400">Doughnut</span>
                </div>
                <div class="relative h-64 sm:h-72 w-full flex items-center justify-center">
                    <canvas id="doughnutChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Candidate Cards Section -->
        <div>
            <h2 class="text-xl font-bold text-slate-900 mb-6 flex items-center space-x-2">
                <i class="fa-solid fa-id-card-clip text-indigo-600"></i>
                <span>Detail Pasangan Calon & Perolehan Suara</span>
            </h2>

            <div id="candidatesContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Dynamically Populated via AJAX -->
                <div class="col-span-full py-12 text-center text-slate-400">
                    <i class="fa-solid fa-spinner fa-spin text-3xl mb-3 text-indigo-600"></i>
                    <p>Memuat data perolehan suara realtime...</p>
                </div>
            </div>
        </div>

    </main>

    <!-- Footer -->
    <footer class="border-t border-slate-200 bg-white py-6 mt-12 text-center text-xs text-slate-500">
        E-Voting OSIS Webbased &bull; Native PHP, Tailwind CSS, MySQL
    </footer>

    <script>
        let barChartInstance = null;
        let doughnutChartInstance = null;
        const BASE_URL = '<?= base_url() ?>';

        const colorPalette = [
            { bg: 'rgba(79, 70, 229, 0.85)', border: '#4f46e5' },   // Indigo
            { bg: 'rgba(147, 51, 234, 0.85)', border: '#9333ea' },   // Purple
            { bg: 'rgba(219, 39, 119, 0.85)', border: '#db2777' },   // Pink
            { bg: 'rgba(5, 150, 105, 0.85)', border: '#059669' },    // Emerald
            { bg: 'rgba(217, 119, 6, 0.85)', border: '#d97706' }     // Amber
        ];

        function fetchResults() {
            fetch(BASE_URL + 'api/results.php')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        updateStats(data.stats, data.timestamp);
                        updateCharts(data.candidates);
                        updateCandidateCards(data.candidates, data.stats.total_sudah);
                    }
                })
                .catch(err => console.error('Error fetching realtime results:', err));
        }

        function updateStats(stats, timestamp) {
            document.getElementById('lastUpdate').innerText = timestamp;
            document.getElementById('statTotalPemilih').innerText = stats.total_pemilih;
            document.getElementById('statTotalSudah').innerText = stats.total_sudah;
            document.getElementById('statTotalBelum').innerText = stats.total_belum;
            document.getElementById('statPersentase').innerText = stats.persentase + '%';
            document.getElementById('progressBar').style.width = stats.persentase + '%';
        }

        function updateCharts(candidates) {
            const labels = candidates.map(c => `Paslon 0${c.nomor_urut} (${c.nama_ketua.split(' ')[0]})`);
            const votes = candidates.map(c => c.total_suara);
            const bgColors = candidates.map((_, i) => colorPalette[i % colorPalette.length].bg);
            const borderColors = candidates.map((_, i) => colorPalette[i % colorPalette.length].border);

            // Bar Chart
            const ctxBar = document.getElementById('barChart').getContext('2d');
            if (barChartInstance) {
                barChartInstance.data.labels = labels;
                barChartInstance.data.datasets[0].data = votes;
                barChartInstance.data.datasets[0].backgroundColor = bgColors;
                barChartInstance.data.datasets[0].borderColor = borderColors;
                barChartInstance.update();
            } else {
                barChartInstance = new Chart(ctxBar, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Jumlah Suara',
                            data: votes,
                            backgroundColor: bgColors,
                            borderColor: borderColors,
                            borderWidth: 2,
                            borderRadius: 12
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            x: {
                                ticks: { color: '#64748b', font: { family: 'Plus Jakarta Sans', weight: '600' } },
                                grid: { display: false }
                            },
                            y: {
                                beginAtZero: true,
                                ticks: { color: '#64748b', stepSize: 1, precision: 0 },
                                grid: { color: '#e2e8f0' }
                            }
                        }
                    }
                });
            }

            // Doughnut Chart
            const ctxDoughnut = document.getElementById('doughnutChart').getContext('2d');
            if (doughnutChartInstance) {
                doughnutChartInstance.data.labels = labels;
                doughnutChartInstance.data.datasets[0].data = votes;
                doughnutChartInstance.data.datasets[0].backgroundColor = bgColors;
                doughnutChartInstance.update();
            } else {
                doughnutChartInstance = new Chart(ctxDoughnut, {
                    type: 'doughnut',
                    data: {
                        labels: labels,
                        datasets: [{
                            data: votes,
                            backgroundColor: bgColors,
                            borderColor: '#ffffff',
                            borderWidth: 3
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { color: '#475569', font: { family: 'Plus Jakarta Sans', size: 11 }, padding: 15 }
                            }
                        },
                        cutout: '65%'
                    }
                });
            }
        }

        function updateCandidateCards(candidates, totalSudah) {
            const container = document.getElementById('candidatesContainer');
            if (candidates.length === 0) {
                container.innerHTML = `
                    <div class="col-span-full bg-white border border-slate-200/80 rounded-3xl p-12 text-center text-slate-400">
                        <i class="fa-solid fa-users-slash text-4xl mb-3 text-slate-300"></i>
                        <p>Belum ada pasangan calon yang terdaftar.</p>
                    </div>`;
                return;
            }

            let html = '';
            candidates.forEach((c, idx) => {
                const color = colorPalette[idx % colorPalette.length].border;
                const photoUrl = c.foto.startsWith('http') ? c.foto : BASE_URL + c.foto;
                html += `
                    <div class="bg-white border border-slate-200/80 rounded-3xl overflow-hidden shadow-sm hover:shadow-md transition flex flex-col justify-between">
                        <div>
                            <div class="bg-slate-50 p-4 border-b border-slate-200/80 flex items-center justify-between">
                                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Kandidat 0${c.nomor_urut}</span>
                                <span class="px-3 py-1 bg-indigo-50 text-indigo-600 border border-indigo-200 font-extrabold rounded-lg text-sm">
                                    0${c.nomor_urut}
                                </span>
                            </div>

                            <div class="p-6 space-y-4">
                                <div class="flex items-center space-x-4">
                                    <div class="w-20 h-20 rounded-2xl overflow-hidden border border-slate-200 flex-shrink-0 bg-slate-100">
                                        <img src="${photoUrl}" alt="${c.nama_ketua}" class="w-full h-full object-cover">
                                    </div>
                                    <div>
                                        <h3 class="text-base font-bold text-slate-900">${c.nama_ketua}</h3>
                                        ${c.nama_wakil ? `<p class="text-xs text-slate-500 mt-0.5">Wakil: ${c.nama_wakil}</p>` : ''}
                                    </div>
                                </div>

                                <div class="bg-slate-50 rounded-2xl p-4 border border-slate-200/80 space-y-2">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs text-slate-500 font-medium">Perolehan Suara:</span>
                                        <span class="text-lg font-extrabold text-slate-900">${c.total_suara} <span class="text-xs text-slate-400 font-normal">suara</span></span>
                                    </div>
                                    <div class="w-full bg-slate-200 rounded-full h-2.5 overflow-hidden">
                                        <div class="h-full rounded-full transition-all duration-500" style="width: ${c.persentase}%; background-color: ${color}"></div>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-xs font-bold text-indigo-600">${c.persentase}%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });

            container.innerHTML = html;
        }

        // Initial fetch
        fetchResults();

        // Auto refresh AJAX every 5 seconds
        setInterval(fetchResults, 5000);
    </script>
</body>
</html>
