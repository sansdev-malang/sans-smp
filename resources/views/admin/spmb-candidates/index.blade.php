<x-admin-layout>
    <div class="p-6 space-y-6" x-data="spmbCandidateApp()">

        <!-- HEADER / ACTION BAR -->
        <section class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 w-full text-left">
            <div class="flex flex-col gap-0.5">
                <div class="flex items-center gap-2.5">
                    <div class="p-2 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 rounded-xl border border-emerald-500/20">
                        <i data-lucide="user-plus" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-50 flex items-center gap-2">
                            Siswa Baru SPMB
                            <span class="text-xs px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400 font-semibold border border-emerald-200 dark:border-emerald-800">
                                Unit SMP
                            </span>
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Data pendaftar dan calon murid yang masuk dari sistem pendaftaran SPMB Pusat.</p>
                    </div>
                </div>
            </div>

            <!-- ACTION CONTROLS: TAHUN AJARAN & SYNC BUTTON -->
            <div class="flex flex-wrap items-center gap-3 shrink-0">
                <!-- Dropdown Tahun Ajaran -->
                <form id="filter-period-form" method="GET" action="{{ route('spmb.candidates.index') }}" class="flex items-center">
                    <div class="relative">
                        <select name="period" onchange="this.form.submit()" 
                            class="appearance-none pl-8 pr-8 py-2 text-xs font-semibold bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-800 dark:text-slate-200 shadow-xs focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 cursor-pointer">
                            <option value="all" {{ $selectedYear === 'all' ? 'selected' : '' }}>Semua Tahun Ajaran</option>
                            @foreach($academicYears as $year)
                                <option value="{{ $year }}" {{ $selectedYear === $year ? 'selected' : '' }}>
                                    Tahun Ajaran {{ $year }}
                                </option>
                            @endforeach
                            @if(empty($academicYears))
                                <option value="{{ date('Y') . '/' . (date('Y') + 1) }}" selected>
                                    Tahun Ajaran {{ date('Y') . '/' . (date('Y') + 1) }}
                                </option>
                            @endif
                        </select>
                        <i data-lucide="calendar" class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                        <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-slate-400 absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                    </div>
                </form>

                <!-- Tombol Tarik Data dari SPMB -->
                <button type="button" @click="syncData()" :disabled="syncing"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white text-xs font-bold rounded-lg shadow-sm transition-all duration-150 cursor-pointer">
                    <i data-lucide="refresh-cw" class="w-3.5 h-3.5" :class="{ 'animate-spin': syncing }"></i>
                    <span x-text="syncing ? 'Menyinkronkan...' : 'Tarik Data dari SPMB'">Tarik Data dari SPMB</span>
                </button>
            </div>
        </section>

        <!-- STATS CARDS GRID -->
        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Stat 1: Total Pendaftar -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-xs flex flex-col justify-between">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Pendaftar SPMB</p>
                        <h3 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-50 mt-1">{{ number_format($stats['total']) }}</h3>
                    </div>
                    <div class="p-2.5 bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 rounded-xl border border-blue-100 dark:border-blue-900/50">
                        <i data-lucide="users" class="w-5 h-5"></i>
                    </div>
                </div>
                <div class="mt-3 text-[11px] text-slate-500 dark:text-slate-400">
                    Periode: <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $selectedYear === 'all' ? 'Semua Periode' : $selectedYear }}</span>
                </div>
            </div>

            <!-- Stat 2: Terverifikasi / Diterima -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-xs flex flex-col justify-between">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Terverifikasi / Diterima</p>
                        <h3 class="text-2xl font-bold tracking-tight text-emerald-600 dark:text-emerald-400 mt-1">{{ number_format($stats['verified']) }}</h3>
                    </div>
                    <div class="p-2.5 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 rounded-xl border border-emerald-100 dark:border-emerald-900/50">
                        <i data-lucide="badge-check" class="w-5 h-5"></i>
                    </div>
                </div>
                <div class="mt-3 text-[11px] text-slate-500 dark:text-slate-400">
                    Status berkas & pendaftaran valid
                </div>
            </div>

            <!-- Stat 3: Pembayaran Lunas -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-xs flex flex-col justify-between">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Pembayaran Lunas</p>
                        <h3 class="text-2xl font-bold tracking-tight text-indigo-600 dark:text-indigo-400 mt-1">{{ number_format($stats['paid']) }}</h3>
                    </div>
                    <div class="p-2.5 bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 rounded-xl border border-indigo-100 dark:border-indigo-900/50">
                        <i data-lucide="wallet" class="w-5 h-5"></i>
                    </div>
                </div>
                <div class="mt-3 text-[11px] text-slate-500 dark:text-slate-400">
                    Biaya pendaftaran / DU selesai
                </div>
            </div>

            <!-- Stat 4: Masuk Siswa Aktif -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-xs flex flex-col justify-between">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Masuk Siswa Aktif</p>
                        <h3 class="text-2xl font-bold tracking-tight text-purple-600 dark:text-purple-400 mt-1">{{ number_format($stats['enrolled']) }}</h3>
                    </div>
                    <div class="p-2.5 bg-purple-50 dark:bg-purple-950/40 text-purple-600 dark:text-purple-400 rounded-xl border border-purple-100 dark:border-purple-900/50">
                        <i data-lucide="graduation-cap" class="w-5 h-5"></i>
                    </div>
                </div>
                <div class="mt-3 text-[11px] text-slate-500 dark:text-slate-400">
                    Sudah terdaftar di database siswa
                </div>
            </div>
        </section>

        <!-- FILTERS & SEARCH BAR -->
        <section class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-4 shadow-xs w-full">
            <form method="GET" action="{{ route('spmb.candidates.index') }}" class="flex flex-col md:flex-row gap-3 items-stretch md:items-center justify-between">
                <!-- Keep Period in Query -->
                <input type="hidden" name="period" value="{{ $selectedYear }}">

                <!-- Search Input -->
                <div class="relative w-full md:max-w-md">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <i data-lucide="search" class="w-4 h-4 text-slate-400 dark:text-slate-500"></i>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama calon siswa, no. pendaftaran, NIK, ortu..."
                        style="padding-left: 2.25rem;"
                        class="w-full h-9 pr-4 text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 text-slate-900 dark:text-slate-50 placeholder-slate-400 dark:placeholder-slate-500 transition-all shadow-inner">
                </div>

                <!-- Filter Select Toolbar -->
                <div class="flex flex-wrap items-center gap-2 w-full md:w-auto">
                    <!-- Status Pendaftaran -->
                    <select name="status" onchange="this.form.submit()"
                        class="h-9 px-3 text-xs font-medium bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 cursor-pointer shadow-xs">
                        <option value="all">Semua Status Pendaftaran</option>
                        <option value="verified" {{ request('status') === 'verified' ? 'selected' : '' }}>Terverifikasi</option>
                        <option value="accepted" {{ request('status') === 'accepted' ? 'selected' : '' }}>Diterima</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Menunggu Verifikasi</option>
                    </select>

                    <!-- Status Pembayaran -->
                    <select name="payment_status" onchange="this.form.submit()"
                        class="h-9 px-3 text-xs font-medium bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 cursor-pointer shadow-xs">
                        <option value="all">Semua Status Bayar</option>
                        <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Lunas</option>
                        <option value="unpaid" {{ request('payment_status') === 'unpaid' ? 'selected' : '' }}>Belum Lunas</option>
                    </select>

                    <!-- Gelombang -->
                    @if(count($availableWaves) > 1)
                        <select name="wave" onchange="this.form.submit()"
                            class="h-9 px-3 text-xs font-medium bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 cursor-pointer shadow-xs">
                            <option value="all">Semua Gelombang</option>
                            @foreach($availableWaves as $w)
                                <option value="{{ $w }}" {{ request('wave') === $w ? 'selected' : '' }}>{{ $w }}</option>
                            @endforeach
                        </select>
                    @endif

                    @if(request()->hasAny(['search', 'status', 'payment_status', 'wave']))
                        <a href="{{ route('spmb.candidates.index', ['period' => $selectedYear]) }}" 
                            class="h-9 px-3 inline-flex items-center justify-center text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 rounded-lg border border-slate-200 dark:border-slate-700 transition-colors"
                            title="Reset Filter">
                            <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                        </a>
                    @endif
                </div>
            </form>
        </section>

        <!-- TABLE LIST PENDAFTAR -->
        <section class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xs overflow-hidden transition-all w-full">
            <div class="overflow-x-auto">
                <table class="w-full text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/75 dark:bg-slate-900/50">
                            <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-12">No</th>
                            <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">No. Reg & Gelombang</th>
                            <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Calon Siswa</th>
                            <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Target Kelas</th>
                            <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Orang Tua / Kontak</th>
                            <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status SPMB</th>
                            <th class="px-5 py-3.5 text-center text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status Siswa</th>
                            <th class="px-5 py-3.5 text-right text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-28">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                        @forelse($candidates as $index => $c)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors group">
                                <td class="px-5 py-3.5 text-slate-400 font-mono text-[11px]">
                                    {{ $candidates->firstItem() + $index }}
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="flex flex-col">
                                        <span class="font-mono font-bold text-slate-900 dark:text-slate-100 text-xs">
                                            {{ $c->registration_number }}
                                        </span>
                                        <span class="text-[11px] text-slate-500 dark:text-slate-400">
                                            {{ $c->wave ?: 'Gelombang 1' }} &bull; TA {{ $c->academic_year }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-3">
                                        @if($c->student_photo_url)
                                            <img src="{{ $c->student_photo_url }}" alt="{{ $c->full_name }}" class="w-8 h-8 rounded-full object-cover ring-1 ring-slate-200 dark:ring-slate-700 shrink-0">
                                        @else
                                            <div class="w-8 h-8 rounded-full bg-indigo-50 dark:bg-indigo-950/50 border border-indigo-100 dark:border-indigo-900/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-xs shrink-0">
                                                {{ $c->initials }}
                                            </div>
                                        @endif
                                        <div class="flex flex-col">
                                            <span class="font-bold text-slate-900 dark:text-slate-100 text-xs tracking-tight hover:text-emerald-600 dark:hover:text-emerald-400 cursor-pointer" @click="openCandidateDetail({{ $c->id }})">
                                                {{ $c->full_name }}
                                            </span>
                                            <span class="text-[11px] text-slate-400">
                                                {{ $c->gender === 'male' || $c->gender === 'L' ? 'Laki-laki' : ($c->gender === 'female' || $c->gender === 'P' ? 'Perempuan' : '-') }}
                                                @if($c->birth_date)
                                                    &bull; {{ $c->birth_date->age }} th
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                        {{ $c->target_class ?: 'Kelas 7' }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="flex flex-col">
                                        <span class="font-medium text-slate-800 dark:text-slate-200 truncate max-w-[150px]">
                                            {{ $c->father_name ?: ($c->mother_name ?: ($c->guardian_name ?: '-')) }}
                                        </span>
                                        @if($c->parent_phone)
                                            <a href="{{ $c->whatsapp_url }}" target="_blank" class="inline-flex items-center gap-1 text-[11px] text-emerald-600 dark:text-emerald-400 hover:underline font-mono mt-0.5">
                                                <i data-lucide="message-circle" class="w-3 h-3"></i>
                                                {{ $c->parent_phone }}
                                            </a>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="flex flex-col gap-1">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold w-fit bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/60">
                                            {{ ucfirst($c->registration_status) }}
                                        </span>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-semibold w-fit {{ $c->payment_status === 'paid' ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-400' : 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400' }}">
                                            {{ $c->payment_status === 'paid' ? 'Lunas' : 'Belum Lunas' }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    @if($c->is_enrolled)
                                        <div class="flex flex-col items-center">
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-purple-50 text-purple-700 dark:bg-purple-950/50 dark:text-purple-400 border border-purple-200 dark:border-purple-800">
                                                <i data-lucide="check" class="w-3 h-3"></i>
                                                Siswa Aktif
                                            </span>
                                            <span class="text-[10px] font-mono text-slate-400 mt-0.5">
                                                {{ $c->assigned_class ?: ($c->student->classroom->name ?? '-') }}
                                            </span>
                                        </div>
                                    @else
                                        <button type="button" @click="openEnrollModal({{ $c->id }})"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs transition-colors cursor-pointer">
                                            <i data-lucide="user-plus" class="w-3 h-3"></i>
                                            Masuk Siswa
                                        </button>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <button type="button" @click="openCandidateDetail({{ $c->id }})"
                                            class="p-1.5 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 rounded-lg transition-colors cursor-pointer"
                                            title="Detail Pendaftar">
                                            <i data-lucide="eye" class="w-4 h-4"></i>
                                        </button>
                                        @if($c->is_enrolled)
                                            <button type="button" @click="unenrollCandidate({{ $c->id }}, '{{ addslashes($c->full_name) }}')"
                                                class="p-1.5 hover:bg-rose-50 dark:hover:bg-rose-950/40 text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 rounded-lg transition-colors cursor-pointer"
                                                title="Batalkan Status Siswa Aktif">
                                                <i data-lucide="user-x" class="w-4 h-4"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center text-slate-400">
                                    <div class="flex flex-col items-center justify-center max-w-sm mx-auto">
                                        <div class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-950/50 flex items-center justify-center text-emerald-500 mb-3 border border-emerald-100 dark:border-emerald-900/50">
                                            <i data-lucide="user-plus" class="w-6 h-6"></i>
                                        </div>
                                        <h4 class="text-sm font-bold text-slate-800 dark:text-slate-200">Belum Ada Calon Siswa SPMB</h4>
                                        <p class="text-xs text-slate-400 mt-1 mb-4 text-center">
                                            Klik tombol sinkronisasi untuk menarik data pendaftar terbaru dari sistem SPMB Pusat.
                                        </p>
                                        <button type="button" @click="syncData()" :disabled="syncing" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold shadow-xs cursor-pointer">
                                            <i data-lucide="refresh-cw" class="w-3.5 h-3.5" :class="{ 'animate-spin': syncing }"></i>
                                            Tarik Data Sekarang
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- PAGINATION -->
            @if($candidates->hasPages())
                <div class="p-4 border-t border-slate-200 dark:border-slate-800">
                    {{ $candidates->links() }}
                </div>
            @endif
        </section>

        <!-- MODAL ENROLLMENT / MASUKKAN KE SISWA AKTIF -->
        <div x-show="enrollModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background-color: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px);">
            <div @click.outside="enrollModalOpen = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-lg shadow-2xl flex flex-col">
                
                <form @submit.prevent="submitEnroll">
                    <div class="p-5 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-bold text-slate-900 dark:text-slate-50">Tandai Sebagai Siswa Aktif</h3>
                            <p class="text-xs text-slate-400 mt-0.5">Tetapkan NIS dan rombongan belajar bagi calon siswa ini.</p>
                        </div>
                        <button type="button" @click="enrollModalOpen = false" class="p-2 text-slate-400 hover:text-slate-600 rounded-lg">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

                    <div class="p-6 space-y-4 text-xs">
                        <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700/60 flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-indigo-600 text-white font-bold text-sm flex items-center justify-center shrink-0" x-text="enrollData.candidate?.full_name ? enrollData.candidate.full_name.charAt(0) : 'S'"></div>
                            <div>
                                <h4 class="font-bold text-slate-900 dark:text-slate-100" x-text="enrollData.candidate?.full_name"></h4>
                                <p class="text-[11px] text-slate-400" x-text="'No. Reg: ' + (enrollData.candidate?.registration_number || '-') + ' | Target: ' + (enrollData.candidate?.target_class || 'Kelas 7')"></p>
                            </div>
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Nomor Induk Siswa (NIS) <span class="text-rose-500">*</span></label>
                            <input type="text" x-model="enrollForm.nis" required placeholder="Contoh: 27.SMP.001"
                                class="w-full h-9 px-3 text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 font-mono font-bold text-indigo-600 dark:text-indigo-400">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Tahun Ajaran <span class="text-rose-500">*</span></label>
                                <select x-model="enrollForm.academic_year_id" required
                                    class="w-full h-9 px-3 text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 cursor-pointer">
                                    <template x-for="ay in enrollData.academic_years" :key="ay.id">
                                        <option :value="ay.id" x-text="'TA ' + ay.name + (ay.is_active ? ' (Aktif)' : '')"></option>
                                    </template>
                                </select>
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Pilih Rombel <span class="text-rose-500">*</span></label>
                                <select x-model="enrollForm.classroom_id" required
                                    class="w-full h-9 px-3 text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 cursor-pointer">
                                    <option value="">Pilih Rombongan Belajar...</option>
                                    <template x-for="c in enrollData.classrooms" :key="c.id">
                                        <option :value="c.id" x-text="c.name + ' (' + (c.class_level?.name || '') + ') - ' + (c.active_students_count || 0) + '/' + c.capacity + ' Siswa'"></option>
                                    </template>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Tanggal Mulai Terdaftar <span class="text-rose-500">*</span></label>
                            <input type="date" x-model="enrollForm.enrollment_date" required
                                class="w-full h-9 px-3 text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Catatan Tambahan</label>
                            <textarea x-model="enrollForm.notes" rows="2" placeholder="Catatan khusus masuk..."
                                class="w-full p-3 text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500"></textarea>
                        </div>
                    </div>

                    <div class="p-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 flex justify-end gap-2">
                        <button type="button" @click="enrollModalOpen = false" class="px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 rounded-lg text-xs font-semibold">
                            Batal
                        </button>
                        <button type="submit" :disabled="enrolling" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white rounded-lg text-xs font-bold shadow-xs flex items-center gap-1.5">
                            <span x-text="enrolling ? 'Mendaftarkan...' : 'Konfirmasi Masuk Siswa Aktif'"></span>
                        </button>
                    </div>
                </form>

            </div>
        </div>

        <!-- MODAL DETAIL PENDAFTAR SPMB -->
        <div x-show="detailModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background-color: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px);">
            <div @click.outside="detailModalOpen = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-2xl max-h-[85vh] overflow-y-auto shadow-2xl flex flex-col">
                <div class="p-5 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between sticky top-0 bg-white/95 backdrop-blur-sm z-10">
                    <h3 class="text-base font-bold text-slate-900 dark:text-slate-50">Detail Calon Siswa SPMB</h3>
                    <button type="button" @click="detailModalOpen = false" class="p-2 text-slate-400 hover:text-slate-600 rounded-lg">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
                <div class="p-6 space-y-4 text-xs" x-show="selectedCandidate">
                    <div class="flex items-center gap-4 pb-4 border-b border-slate-100">
                        <template x-if="selectedCandidate?.student_photo_url">
                            <img :src="selectedCandidate?.student_photo_url" class="w-14 h-14 rounded-2xl object-cover border border-slate-200">
                        </template>
                        <template x-if="!selectedCandidate?.student_photo_url">
                            <div class="w-14 h-14 rounded-2xl bg-indigo-50 text-indigo-600 font-bold text-lg flex items-center justify-center" x-text="selectedCandidate?.initials || 'S'"></div>
                        </template>
                        <div>
                            <h4 class="text-sm font-bold text-slate-900 dark:text-slate-100" x-text="selectedCandidate?.full_name"></h4>
                            <p class="font-mono text-indigo-600 font-bold" x-text="'No. Reg: ' + (selectedCandidate?.registration_number || '-')"></p>
                            <p class="text-[11px] text-slate-400" x-text="'Target: ' + (selectedCandidate?.target_class || 'Kelas 7') + ' | ' + (selectedCandidate?.wave || 'Gelombang 1')"></p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 text-slate-600 dark:text-slate-300">
                        <div>
                            <span class="text-slate-400 text-[10px] uppercase block">NIK / NISN</span>
                            <span class="font-semibold font-mono" x-text="(selectedCandidate?.nik || '-') + ' / ' + (selectedCandidate?.nisn || '-')"></span>
                        </div>
                        <div>
                            <span class="text-slate-400 text-[10px] uppercase block">Tempat, Tanggal Lahir</span>
                            <span class="font-semibold" x-text="(selectedCandidate?.birth_place || '-') + ', ' + (selectedCandidate?.formatted_birth_date || '-')"></span>
                        </div>
                        <div>
                            <span class="text-slate-400 text-[10px] uppercase block">Nama Ayah / No HP</span>
                            <span class="font-semibold" x-text="(selectedCandidate?.father_name || '-') + ' (' + (selectedCandidate?.father_phone || '-') + ')'"></span>
                        </div>
                        <div>
                            <span class="text-slate-400 text-[10px] uppercase block">Nama Ibu / No HP</span>
                            <span class="font-semibold" x-text="(selectedCandidate?.mother_name || '-') + ' (' + (selectedCandidate?.mother_phone || '-') + ')'"></span>
                        </div>
                        <div>
                            <span class="text-slate-400 text-[10px] uppercase block">Asal Sekolah</span>
                            <span class="font-semibold" x-text="selectedCandidate?.previous_school || '-'"></span>
                        </div>
                        <div>
                            <span class="text-slate-400 text-[10px] uppercase block">Status Verifikasi & Bayar</span>
                            <span class="font-semibold capitalize" x-text="(selectedCandidate?.registration_status || '-') + ' | ' + (selectedCandidate?.payment_status || '-')"></span>
                        </div>
                        <div class="col-span-2">
                            <span class="text-slate-400 text-[10px] uppercase block">Alamat Lengkap</span>
                            <span class="font-semibold" x-text="selectedCandidate?.address || '-'"></span>
                        </div>
                    </div>
                </div>
                <div class="p-4 border-t border-slate-200 bg-slate-50/50 flex justify-end">
                    <button type="button" @click="detailModalOpen = false" class="px-4 py-2 bg-white border border-slate-200 rounded-lg text-xs font-semibold">
                        Tutup
                    </button>
                </div>
            </div>
        </div>

    </div>

    <!-- Alpine.js Application Logic -->
    <script>
        function spmbCandidateApp() {
            return {
                syncing: false,
                enrolling: false,
                enrollModalOpen: false,
                detailModalOpen: false,
                selectedCandidate: null,
                enrollData: {
                    candidate: null,
                    academic_years: [],
                    classrooms: [],
                },
                enrollForm: {
                    candidate_id: null,
                    nis: '',
                    academic_year_id: '',
                    classroom_id: '',
                    enrollment_date: '{{ date("Y-m-d") }}',
                    notes: '',
                },

                syncData() {
                    if (this.syncing) return;
                    this.syncing = true;

                    fetch('{{ route("spmb.candidates.sync") }}', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(res => res.json())
                    .then(res => {
                        this.syncing = false;
                        if (res.success) {
                            alert(res.message || 'Sinkronisasi berhasil!');
                            window.location.reload();
                        } else {
                            alert(res.message || 'Gagal menyinkronkan data.');
                        }
                    })
                    .catch(err => {
                        this.syncing = false;
                        alert('Error: ' + err.message);
                    });
                },

                openEnrollModal(id) {
                    fetch(`/spmb-candidates/${id}/enroll-data`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(res => res.json())
                    .then(res => {
                        if (res.success) {
                            this.enrollData = res;
                            this.enrollForm.candidate_id = id;
                            this.enrollForm.nis = res.suggested_nis;
                            this.enrollForm.academic_year_id = res.matched_year_id || (res.academic_years[0]?.id || '');
                            this.enrollForm.classroom_id = res.classrooms[0]?.id || '';
                            this.enrollForm.enrollment_date = '{{ date("Y-m-d") }}';
                            this.enrollModalOpen = true;
                        }
                    })
                    .catch(err => alert("Gagal memuat data pendaftaran: " + err.message));
                },

                submitEnroll() {
                    if (this.enrolling) return;
                    this.enrolling = true;

                    fetch(`/spmb-candidates/${this.enrollForm.candidate_id}/enroll`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(this.enrollForm)
                    })
                    .then(res => res.json())
                    .then(res => {
                        this.enrolling = false;
                        if (res.success) {
                            this.enrollModalOpen = false;
                            alert(res.message || 'Siswa berhasil didaftarkan sebagai Siswa Aktif!');
                            window.location.reload();
                        } else {
                            alert(res.message || 'Gagal mendaftarkan siswa.');
                        }
                    })
                    .catch(err => {
                        this.enrolling = false;
                        alert('Error: ' + err.message);
                    });
                },

                unenrollCandidate(id, name) {
                    if (!confirm(`Batalkan status Siswa Aktif untuk "${name}"?`)) return;

                    fetch(`/spmb-candidates/${id}/unenroll`, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(res => res.json())
                    .then(res => {
                        if (res.success) {
                            alert(res.message);
                            window.location.reload();
                        } else {
                            alert(res.message || 'Gagal membatalkan status siswa.');
                        }
                    })
                    .catch(err => alert('Error: ' + err.message));
                },

                openCandidateDetail(id) {
                    fetch(`/spmb-candidates/${id}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(res => res.json())
                    .then(res => {
                        if (res.success) {
                            this.selectedCandidate = res.candidate;
                            this.detailModalOpen = true;
                        }
                    })
                    .catch(err => alert("Gagal memuat detail: " + err.message));
                }
            }
        }
    </script>
</x-admin-layout>
