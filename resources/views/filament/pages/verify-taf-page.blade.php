<x-filament-panels::page>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Tambahan library untuk memunculkan angka di atas chart -->
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0"></script>

    <div wire:poll.300s="refreshData" class="space-y-6">

        {{-- Top Bar & Control Section --}}
        <x-filament::section class="shadow-sm border-t-4 border-t-primary-500">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="p-3 rounded-xl bg-primary-500/10 text-primary-500">
                        <div style="width: 28px; height: 28px;">
                            <x-heroicon-m-signal class="animate-pulse" />
                        </div>
                    </div>
                    <div>
                        <h2 class="text-lg font-extrabold tracking-tight">
                            Verifikasi TAF Realtime
                        </h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">
                            Stasiun Meteorologi Maimun Saleh Sabang (WITN)
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <div class="flex items-center gap-2 bg-gray-50 dark:bg-gray-900 px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-700 text-sm">
                        <div style="width: 16px; height: 16px;">
                            <x-heroicon-o-calendar class="text-gray-400" />
                        </div>
                        <span class="text-gray-500 dark:text-gray-400 font-semibold">Tanggal:</span>
                        <input type="date" 
                               wire:model.live="selectedDate" 
                               class="bg-transparent border-0 p-0 text-sm font-bold text-primary-600 dark:text-primary-400 focus:ring-0 cursor-pointer [color-scheme:dark]">
                    </div>

                    <div class="flex items-center gap-2 bg-gray-50 dark:bg-gray-900 px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-700 text-sm">
                        <div style="width: 16px; height: 16px;">
                            <x-heroicon-o-map-pin class="text-gray-400" />
                        </div>
                        <span class="text-gray-500 dark:text-gray-400 font-semibold">Stasiun:</span>
                        <span class="font-mono font-bold text-emerald-600 dark:text-emerald-400">WITN</span>
                    </div>

                    <div class="h-8 w-px bg-gray-200 dark:bg-gray-700 hidden sm:block"></div>

                    <x-filament::button wire:click="refreshData" icon="heroicon-m-arrow-path" size="sm">
                        Sync Data
                    </x-filament::button>

                    {{ $this->exportExcelAction }}
                </div>
            </div>
        </x-filament::section>

        {{-- SECTION QC TAFOR --}}
        <x-filament::section icon="heroicon-o-shield-check" header="Quality Control Sandi TAFOR Sebelum Kirim">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                
                <!-- Kiri: Form Input -->
                <div class="space-y-4">
                    <div>
                        <label class="flex items-center gap-2 text-sm font-bold mb-2">
                            <div style="width: 16px; height: 16px;">
                                <x-heroicon-m-pencil-square class="text-primary-500"/>
                            </div>
                            INPUT SANDI TAFOR
                        </label>
                        <textarea wire:model.defer="rawTaf" rows="6" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm font-mono leading-relaxed" placeholder="Ketik atau paste sandi TAFOR di sini...&#10;Contoh: FTID40 WITN 290500 CCA TAF COR..."></textarea>
                    </div>
                    <div class="flex gap-3">
                        <x-filament::button wire:click="checkQualityControl" color="primary" size="sm">
                            Verifikasi TAFOR
                        </x-filament::button>
                        <x-filament::button wire:click="clearQualityControl" color="gray" size="sm">
                            Bersihkan
                        </x-filament::button>
                    </div>
                </div>

                <!-- Kanan: Hasil QC -->
                <div class="flex flex-col h-full">
                    @if(!empty($qcResult))
                        <div class="mb-4 p-4 rounded-lg text-sm flex items-start gap-3 border {{ $qcResult['is_valid'] ? 'bg-emerald-50 border-emerald-200 text-emerald-800 dark:bg-emerald-900 dark:border-emerald-700 dark:text-emerald-300' : 'bg-rose-50 border-rose-200 text-rose-800 dark:bg-rose-900 dark:border-rose-700 dark:text-rose-300' }}">
                            @if($qcResult['is_valid'])
                                <div style="width: 24px; height: 24px; flex-shrink: 0;">
                                    <x-heroicon-m-check-circle class="text-emerald-500"/>
                                </div>
                                <div>
                                    <span class="font-bold block text-base text-emerald-900 dark:text-emerald-400">Sandi TAF Valid</span>
                                    <span class="opacity-90">Tidak ditemukan kesalahan penulisan format standar.</span>
                                </div>
                            @else
                                <div style="width: 24px; height: 24px; flex-shrink: 0;">
                                    <x-heroicon-m-x-circle class="text-rose-500"/>
                                </div>
                                <div>
                                    <span class="font-bold block text-base text-rose-900 dark:text-rose-400">Sandi TAF perlu diperbaiki</span>
                                    <span class="opacity-90">Ditemukan <strong class="font-extrabold">{{ $qcResult['errors'] }}</strong> elemen yang tidak sesuai standar pengiriman.</span>
                                </div>
                            @endif
                        </div>
                        
                        <div class="overflow-x-auto border rounded-lg dark:border-gray-700 flex-1">
                            <table class="w-full text-sm text-left">
                                <thead class="border-b dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
                                    <tr>
                                        <th class="p-3 border-r dark:border-gray-700 font-bold text-gray-900 dark:text-gray-200">Elemen</th>
                                        <th class="p-3 border-r dark:border-gray-700 font-bold text-gray-900 dark:text-gray-200">Aturan QC</th>
                                        <th class="p-3 font-bold text-gray-900 dark:text-gray-200">Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900">
                                    @foreach($qcResult['results'] as $res)
                                        <tr class="{{ $res['is_valid'] ? 'text-gray-800 dark:text-gray-300' : 'bg-rose-50 dark:bg-rose-900/40 text-rose-800 dark:text-rose-300' }}">
                                            <td class="p-3 border-r dark:border-gray-700 font-mono font-bold text-primary-600 dark:text-primary-400">{{ $res['element'] }}</td>
                                            <td class="p-3 border-r dark:border-gray-700 font-medium">{{ $res['rule'] }}</td>
                                            <td class="p-3 flex items-center gap-2.5 font-medium">
                                                @if($res['is_valid'])
                                                    <div class="flex items-center justify-center w-5 h-5 rounded-full bg-emerald-100 dark:bg-emerald-900 text-emerald-600 dark:text-emerald-400 flex-shrink-0">
                                                        <x-heroicon-m-check style="width: 12px; height: 12px;"/>
                                                    </div>
                                                @else
                                                    <div class="flex items-center justify-center w-5 h-5 rounded-full bg-rose-100 dark:bg-rose-900 text-rose-600 dark:text-rose-400 flex-shrink-0">
                                                        <x-heroicon-m-x-mark style="width: 12px; height: 12px;"/>
                                                    </div>
                                                @endif
                                                {{ $res['remark'] }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="h-full min-h-[200px] flex flex-col items-center justify-center text-sm text-gray-500 dark:text-gray-400 italic border-2 border-dashed rounded-lg p-8 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
                            <div style="width: 48px; height: 48px; margin-bottom: 12px;">
                                <x-heroicon-o-document-magnifying-glass class="text-gray-400 dark:text-gray-500"/>
                            </div>
                            <p class="text-center">Silakan masukkan sandi TAFOR di kolom kiri<br>lalu klik <strong class="font-semibold text-primary-500">Verifikasi TAFOR</strong>.</p>
                        </div>
                    @endif
                </div>

            </div>
        </x-filament::section>

        @php
            $summary = $this->realtimeData['summary'];
            $rows = $this->realtimeData['rows'];
            
            // Ambil row paling atas (terbaru) untuk ditampilkan di card TAF/METAR aktif
            $latestRow = $rows[0] ?? null;
            $activeTafRaw = $latestRow['taf_raw'] ?? 'Data tidak tersedia';
            $activeMetarRaw = $latestRow['metar_raw'] ?? 'Data tidak tersedia';
        @endphp

        <div class="grid grid-cols-1 gap-6 items-start">
            
            {{-- BAR CHART PERSENTASE VERIFIKASI PARAMETER CUACA --}}
            <x-filament::section icon="heroicon-m-chart-bar" header="Persentase Verifikasi Parameter Cuaca">
                <div class="text-sm text-gray-500 mb-4">Rata-rata hasil verifikasi otomatis berdasarkan observasi yang tersedia.</div>
                <div class="relative w-full h-64">
                    <canvas id="parameterChart"></canvas>
                </div>
            </x-filament::section>

            {{-- 2 KOLOM UNTUK SANDI TAF & METAR AKTIF --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- TAF YANG SEDANG BERLAKU --}}
                <x-filament::section header="TAF YANG SEDANG BERLAKU">
                    <div class="text-sm text-gray-500 mb-2">Pesan TAF lengkap yang digunakan untuk jam aktif.</div>
                    <div class="p-4 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg font-mono text-sm text-gray-800 dark:text-gray-200 whitespace-pre-wrap">
                        {{ $activeTafRaw }}
                    </div>
                </x-filament::section>

                {{-- METAR/SPECI PADA JAM AKTIF --}}
                <x-filament::section header="METAR/SPECI PADA JAM AKTIF">
                    <div class="text-sm text-gray-500 mb-2">Laporan yang digunakan; COR diprioritaskan pada waktu/jenis yang sama.</div>
                    <div class="p-4 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg font-mono text-sm text-gray-800 dark:text-gray-200 whitespace-pre-wrap">
                        {{ $activeMetarRaw }}
                    </div>
                </x-filament::section>
            </div>

            {{-- TABEL VERIFIKASI PER JAM --}}
            <x-filament::section icon="heroicon-m-table-cells" header="Verifikasi Per Jam">
                <div class="text-sm text-gray-500 mb-4">Setiap jam yang memiliki observasi akan dihitung ulang. Klik pada baris untuk melihat detail TAF dan METAR.</div>
                <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                    <table class="w-full text-center text-xs border-collapse">
                        <thead>
                            <tr class="bg-gray-50 dark:bg-gray-800 font-bold text-gray-700 dark:text-gray-300 border-b border-gray-200 dark:border-gray-700">
                                <th class="p-3">Jam UTC</th>
                                <th class="p-3">TAF</th>
                                <th class="p-3">METAR/SPECI</th>
                                <th class="p-3">Arah</th>
                                <th class="p-3">Kec. Angin</th>
                                <th class="p-3">Gust</th>
                                <th class="p-3">Visibility</th>
                                <th class="p-3">Cuaca</th>
                                <th class="p-3">Cloud</th>
                                <th class="p-3">Cloud Base</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700 text-[11px] bg-white dark:bg-gray-900">
                            @foreach($rows as $row)
                                <tr wire:click="mountAction('viewDetails', { time: '{{ $row['time'] }}' })" class="cursor-pointer hover:bg-primary-50 dark:hover:bg-primary-400/10 transition">
                                    <td class="p-3 font-mono font-bold">{{ $row['time'] }}</td>
                                    <td class="p-3">{{ $row['taf_raw'] !== 'Data TAF Belum Tersedia' ? '1' : '-' }}</td>
                                    <td class="p-3">{{ $row['metar_raw'] !== 'Data METAR Belum Tersedia' ? '1' : '-' }}</td>
                                    
                                    @if(isset($row['scores']) && is_array($row['scores']))
                                        <td class="p-3">{!! isset($row['scores']['wind_dir']) ? ($row['scores']['wind_dir'] ? '<span class="text-emerald-500">100.0% (1/1)</span>' : '<span class="text-rose-500">0.0% (0/1)</span>') : '-' !!}</td>
                                        <td class="p-3">{!! isset($row['scores']['wind_speed']) ? ($row['scores']['wind_speed'] ? '<span class="text-emerald-500">100.0% (1/1)</span>' : '<span class="text-rose-500">0.0% (0/1)</span>') : '-' !!}</td>
                                        <td class="p-3">{!! isset($row['scores']['gust']) ? ($row['scores']['gust'] ? '<span class="text-emerald-500">100.0% (1/1)</span>' : '<span class="text-rose-500">0.0% (0/1)</span>') : '-' !!}</td>
                                        <td class="p-3">{!! isset($row['scores']['visibility']) ? ($row['scores']['visibility'] ? '<span class="text-emerald-500">100.0% (1/1)</span>' : '<span class="text-rose-500">0.0% (0/1)</span>') : '-' !!}</td>
                                        <td class="p-3">{!! isset($row['scores']['precip']) ? ($row['scores']['precip'] ? '<span class="text-emerald-500">100.0% (1/1)</span>' : '<span class="text-rose-500">0.0% (0/1)</span>') : '-' !!}</td>
                                        <td class="p-3">{!! isset($row['scores']['cloud']) ? ($row['scores']['cloud'] ? '<span class="text-emerald-500">100.0% (1/1)</span>' : '<span class="text-rose-500">0.0% (0/1)</span>') : '-' !!}</td>
                                        <td class="p-3">{!! isset($row['scores']['ceiling']) ? ($row['scores']['ceiling'] ? '<span class="text-emerald-500">100.0% (1/1)</span>' : '<span class="text-rose-500">0.0% (0/1)</span>') : '-' !!}</td>
                                    @else
                                        <td class="p-3">-</td><td class="p-3">-</td><td class="p-3">-</td><td class="p-3">-</td><td class="p-3">-</td><td class="p-3">-</td><td class="p-3">-</td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-filament::section>
        </div>
    </div>
    
    {{-- Komponen modal bawaan Filament --}}
    <x-filament-actions::modals />

    <script>
        document.addEventListener('livewire:initialized', () => {
            // Registrasi plugin datalabels
            Chart.register(ChartDataLabels);

            const initChart = () => {
                const ctx = document.getElementById('parameterChart');
                if(ctx) {
                    if(window.tafChart) { window.tafChart.destroy(); }
                    
                    const paramStats = @json($summary['paramStats'] ?? []);
                    const labels = [];
                    const data = [];
                    
                    const colors = ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#06b6d4', '#f43f5e', '#6366f1'];

                    Object.values(paramStats).forEach(stat => {
                        labels.push(stat.label);
                        const percentage = stat.total > 0 ? (stat.pass / stat.total * 100) : 0;
                        data.push(percentage.toFixed(1));
                    });

                    window.tafChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: labels,
                            datasets: [{
                                data: data,
                                backgroundColor: colors,
                                borderRadius: 4,
                                barPercentage: 0.6
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            layout: {
                                padding: {
                                    top: 30 // Padding tambahan agar angka di atas tidak terpotong tepi atas chart
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    max: 100,
                                    ticks: {
                                        callback: function(value) { return value + '%' }
                                    }
                                }
                            },
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            return context.parsed.y + '% Akurat';
                                        }
                                    }
                                },
                                // Konfigurasi datalabels untuk menampilkan nilai statis di atas bar
                                datalabels: {
                                    anchor: 'end',
                                    align: 'top',
                                    color: '#374151',
                                    font: {
                                        weight: 'bold'
                                    },
                                    formatter: function(value) {
                                        return value + '%';
                                    }
                                }
                            }
                        }
                    });
                }
            };
            initChart();
            Livewire.hook('morph.updated', () => { initChart(); });
        });
    </script>
</x-filament-panels::page>