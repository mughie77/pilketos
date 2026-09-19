<?php
require_once __DIR__ . '/config.php';
?>
<!DOCTYPE html>
<html lang="id" class="dark">
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
        @keyframes pulse-glow {
            0%, 100% { opacity: 0.4; }
            50% { opacity: 0.8; }
        }
        .animate-pulse-glow { animation: pulse-glow 3s infinite ease-in-out; }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex flex-col justify-between selection:bg-indigo-500 selection:text-white relative overflow-x-hidden">

    <!-- Glowing Background Gradients -->
    <div class="absolute -top-40 left-1/2 -translate-x-1/2 w-[800px] h-[350px] bg-gradient-to-tr from-indigo-600/20 via-purple-600/20 to-pink-500/10 rounded-full blur-3xl pointer-events-none animate-pulse-glow"></div>

    <!-- Header Navigation -->
    <header class="bg-slate-900/80 backdrop-blur-xl border-b border-slate-800 sticky top-0 z-40 shadow-xl">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3.5 flex items-center justify-between">
            <div class="flex items-center space-x-3.5">
                <div class="w-11 h-11 bg-gradient-to-tr from-indigo-500 via-purple-500 to-pink-500 rounded-2xl flex items-center justify-center shadow-lg shadow-indigo-500/25">
                    <i class="fa-solid fa-chart-column text-xl text-white"></i>
                </div>
                <div>
                    <div class="flex items-center space-x-2">
                        <span class="font-extrabold text-lg text-white tracking-tight">PEMILU OSIS</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-2xs font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 animate-pulse">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 mr-1"></span> LIVE COUNT
                        </span>
                    </div>
                    <p class="text-xs text-slate-400 font-medium">Hasil Perhitungan Suara Realtime</p>
                </div>
            </div>

            <div class="flex items-center space-x-3">
                <a href="voter/login.php" class="px-4 py-2.5 bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white font-semibold rounded-xl text-xs sm:text-sm shadow-lg shadow-indigo-500/25 transition duration-200 transform hover:-translate-y-0.5 flex items-center space-x-2">
                    <i class="fa-solid fa-vote-yea"></i>
                    <span>Masuk Bilik Suara</span>
                </a>
                <a href="admin/login.php" class="p-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white rounded-xl border border-slate-700 text-xs transition" title="Login Admin">
                    <i class="fa-solid fa-user-shield text-sm"></i>
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-1 space-y-8">

        <!-- Hero & Refresh Indicator Bar -->
        <div class="flex flex-col md:flex-row items-center justify-between gap-4 bg-slate-900/60 border border-slate-800/80 rounded-2xl p-5 backdrop-blur-md">
            <div>
                <h1 class="text-2xl font-black text-white tracking-tight">Realtime Quick Count OSIS</h1>
                <p class="text-slate-400 text-xs mt-1">Data diperbarui secara otomatis setiap <span class="text-indigo-400 font-bold">5 detik</span> via AJAX</p>
            </div>

            <div class="flex items-center space-x-3 bg-slate-950/80 px-4 py-2 rounded-xl border border-slate-800">
                <div class="w-3 h-3 rounded-full bg-indigo-500 animate-ping"></div>
                <span class="text-xs text-slate-300 font-mono">Update Terakhir: <span id="lastUpdate" class="font-bold text-white">--:--:--</span></span>
            </div>
        </div>

        <!-- Metric Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Total DPT -->
            <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-5 shadow-lg relative overflow-hidden group hover:border-slate-700 transition">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total DPT Siswa</span>
                    <div class="w-10 h-10 bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 rounded-xl flex items-center justify-center">
                        <i class="fa-solid fa-users"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-baseline justify-between">
                    <span id="statTotalPemilih" class="text-3xl font-extrabold text-white">0</span>
                    <span class="text-2xs text-slate-500">Siswa Terdaftar</span>
                </div>
            </div>

            <!-- Sudah Memilih -->
            <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-5 shadow-lg relative overflow-hidden group hover:border-slate-700 transition">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Suara Masuk</span>
                    <div class="w-10 h-10 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-xl flex items-center justify-center">
                        <i class="fa-solid fa-vote-yea"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-baseline justify-between">
                    <span id="statTotalSudah" class="text-3xl font-extrabold text-emerald-400">0</span>
                    <span id="statPersentase" class="text-xs font-bold text-emerald-400 bg-emerald-500/10 px-2 py-0.5 rounded-md">0%</span>
                </div>
            </div>

            <!-- Belum Memilih -->
            <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-5 shadow-lg relative overflow-hidden group hover:border-slate-700 transition">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Belum Memilih</span>
                    <div class="w-10 h-10 bg-amber-500/10 border border-amber-500/20 text-amber-400 rounded-xl flex items-center justify-center">
                        <i class="fa-solid fa-hourglass-half"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-baseline justify-between">
                    <span id="statTotalBelum" class="text-3xl font-extrabold text-amber-400">0</span>
                    <span class="text-2xs text-slate-500">Siswa</span>
                </div>
            </div>

            <!-- Partisipasi -->
            <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-5 shadow-lg relative overflow-hidden group hover:border-slate-700 transition">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Tingkat Partisipasi</span>
                    <div class="w-10 h-10 bg-purple-500/10 border border-purple-500/20 text-purple-400 rounded-xl flex items-center justify-center">
                        <i class="fa-solid fa-chart-line"></i>
                    </div>
                </div>
                <div class="mt-4">
                    <div class="w-full bg-slate-950 rounded-full h-3.5 border border-slate-800 overflow-hidden p-0.5">
                        <div id="progressBar" class="bg-gradient-to-r from-indigo-500 to-purple-500 h-full rounded-full transition-all duration-700" style="width: 0%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart Section -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Bar / Column Chart -->
            <div class="lg:col-span-2 bg-slate-900/80 border border-slate-800 rounded-3xl p-6 shadow-xl flex flex-col justify-between">
                <div class="flex items-center justify-between mb-6 pb-4 border-b border-slate-800">
                    <h3 class="text-lg font-bold text-white flex items-center space-x-2">
                        <i class="fa-solid fa-chart-simple text-indigo-400"></i>
                        <span>Grafik Perolehan Suara Paslon</span>
                    </h3>
                    <span class="text-xs text-slate-400">Bar Chart Realtime</span>
                </div>
                <div class="relative h-72 sm:h-80 w-full flex items-center justify-center">
                    <canvas id="barChart"></canvas>
                </div>
            </div>

            <!-- Doughnut Chart -->
            <div class="bg-slate-900/80 border border-slate-800 rounded-3xl p-6 shadow-xl flex flex-col justify-between">
                <div class="flex items-center justify-between mb-6 pb-4 border-b border-slate-800">
                    <h3 class="text-lg font-bold text-white flex items-center space-x-2">
                        <i class="fa-solid fa-chart-pie text-purple-400"></i>
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
            <h2 class="text-2xl font-bold text-white mb-6 flex items-center space-x-2">
                <i class="fa-solid fa-id-card-clip text-indigo-400"></i>
                <span>Detail Pasangan Calon & Perolehan Suara</span>
            </h2>

            <div id="candidatesContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Dynamically Populated via AJAX -->
                <div class="col-span-full py-12 text-center text-slate-500">
                    <i class="fa-solid fa-spinner fa-spin text-3xl mb-3 text-indigo-500"></i>
                    <p>Memuat data perolehan suara realtime...</p>
                </div>
            </div>
        </div>

    </main>

    <!-- Footer -->
    <footer class="border-t border-slate-900 bg-slate-950/80 py-6 mt-12 text-center text-xs text-slate-400">
        E-Voting OSIS Webbased &bull; PHP Native, Tailwind CSS, MySQL
    </footer>

    <script>
        let barChartInstance = null;
        let doughnutChartInstance = null;

        const colorPalette = [
            { bg: 'rgba(99, 102, 241, 0.85)', border: '#6366f1' },   // Indigo
            { bg: 'rgba(168, 85, 247, 0.85)', border: '#a855f7' },   // Purple
            { bg: 'rgba(236, 72, 153, 0.85)', border: '#ec4899' },   // Pink
            { bg: 'rgba(16, 185, 129, 0.85)', border: '#10b981' },   // Emerald
            { bg: 'rgba(245, 158, 11, 0.85)', border: '#f59e0b' }    // Amber
        ];

        function fetchResults() {
            fetch('api/results.php')
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
                                ticks: { color: '#94a3b8', font: { family: 'Plus Jakarta Sans', weight: '600' } },
                                grid: { display: false }
                            },
                            y: {
                                beginAtZero: true,
                                ticks: { color: '#94a3b8', stepSize: 1, precision: 0 },
                                grid: { color: 'rgba(51, 65, 85, 0.4)' }
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
                            borderColor: '#0f172a',
                            borderWidth: 3
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { color: '#cbd5e1', font: { family: 'Plus Jakarta Sans', size: 11 }, padding: 15 }
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
                    <div class="col-span-full bg-slate-900/60 border border-slate-800 rounded-3xl p-12 text-center text-slate-500">
                        <i class="fa-solid fa-users-slash text-4xl mb-3"></i>
                        <p>Belum ada pasangan calon yang terdaftar.</p>
                    </div>`;
                return;
            }

            let html = '';
            candidates.forEach((c, idx) => {
                const color = colorPalette[idx % colorPalette.length].border;
                html += `
                    <div class="bg-slate-900/80 border border-slate-800 rounded-3xl overflow-hidden shadow-xl hover:border-slate-700 transition flex flex-col justify-between">
                        <div>
                            <div class="bg-slate-950/80 p-4 border-b border-slate-800 flex items-center justify-between">
                                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Kandidat 0${c.nomor_urut}</span>
                                <span class="px-3 py-1 bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 font-extrabold rounded-lg text-sm">
                                    0${c.nomor_urut}
                                </span>
                            </div>

                            <div class="p-6 space-y-4">
                                <div class="flex items-center space-x-4">
                                    <div class="w-20 h-20 rounded-2xl overflow-hidden border-2 border-slate-800 flex-shrink-0 bg-slate-950">
                                        <img src="${c.foto}" alt="${c.nama_ketua}" class="w-full h-full object-cover">
                                    </div>
                                    <div>
                                        <h3 class="text-base font-bold text-white">${c.nama_ketua}</h3>
                                        ${c.nama_wakil ? `<p class="text-xs text-slate-400 mt-0.5">Wakil: ${c.nama_wakil}</p>` : ''}
                                    </div>
                                </div>

                                <div class="bg-slate-950/60 rounded-2xl p-4 border border-slate-800/80 space-y-2">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs text-slate-400 font-medium">Perolehan Suara:</span>
                                        <span class="text-lg font-extrabold text-white">${c.total_suara} <span class="text-xs text-slate-500 font-normal">suara</span></span>
                                    </div>
                                    <div class="w-full bg-slate-900 rounded-full h-2.5 overflow-hidden">
                                        <div class="h-full rounded-full transition-all duration-500" style="width: ${c.persentase}%; background-color: ${color}"></div>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-xs font-bold text-indigo-400">${c.persentase}%</span>
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
