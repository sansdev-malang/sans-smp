<x-admin-layout>
    <div class="p-6 space-y-6" x-data="studentApp()">

        <!-- GREETING / PAGE TITLE -->
        <section class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 w-full text-left">
            <div class="flex flex-col gap-0.5">
                <div class="flex items-center gap-2.5">
                    <div class="p-2 bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 rounded-xl border border-indigo-500/20">
                        <i data-lucide="users" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-50 flex items-center gap-2">
                            Daftar Siswa
                            <span class="text-xs px-2.5 py-0.5 rounded-full bg-indigo-100 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-400 font-semibold border border-indigo-200 dark:border-indigo-800">
                                {{ setting('app_name', 'SMP Anak Saleh') }}
                            </span>
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Kelola dan pantau data akademis siswa aktif {{ setting('app_name', 'SMP Anak Saleh') }}.</p>
                    </div>
                </div>
            </div>
            <!-- ACTION CONTROLS: INFO TAHUN AJARAN AKTIF & ACTION BUTTONS -->
            <div class="flex flex-wrap items-center gap-3 shrink-0">
                <!-- Info Badge Tahun Ajaran Aktif -->
                <div class="inline-flex items-center gap-2 px-3.5 py-2 bg-indigo-50/80 dark:bg-indigo-950/40 border border-indigo-200/80 dark:border-indigo-800/60 rounded-xl text-xs shadow-xs">
                    <span class="flex h-2 w-2 relative">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    <span class="text-slate-500 dark:text-slate-400 font-medium">T.A. Aktif:</span>
                    <span class="font-bold text-indigo-700 dark:text-indigo-300">
                        {{ $activeYear ? $activeYear->name : '2026/2027' }} ({{ ucfirst($activeYear?->semester ?? 'Ganjil') }})
                    </span>
                </div>

                <button type="button" @click="importModalOpen = true"
                    class="inline-flex items-center justify-center gap-2 px-3.5 py-2 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-lg border border-slate-200 dark:border-slate-800 shadow-xs transition-all duration-100 cursor-pointer">
                    <i data-lucide="file-spreadsheet" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400"></i>
                    Impor Excel
                </button>

                <a href="{{ route('spmb.candidates.index') }}"
                    class="inline-flex items-center justify-center gap-2 px-3.5 py-2 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-lg border border-slate-200 dark:border-slate-800 shadow-xs transition-all duration-100 cursor-pointer">
                    <i data-lucide="user-plus" class="w-3.5 h-3.5 text-indigo-600 dark:text-indigo-400"></i>
                    Tarik Siswa SPMB
                </a>
                <button type="button" @click="openCreateModal()"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg shadow-sm transition-all duration-100 cursor-pointer">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    Tambah Siswa
                </button>
            </div>
        </section>

        <!-- STATS CARDS GRID -->
        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Stat Card 1: Total Siswa Aktif -->
            <div class="animate-card bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-xs flex flex-col justify-between">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Siswa Aktif</p>
                        <h3 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-50 mt-1">
                            {{ number_format($stats['active']) }}
                        </h3>
                    </div>
                    <div class="p-2.5 bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 rounded-xl border border-indigo-100 dark:border-indigo-900/50">
                        <i data-lucide="users" class="w-5 h-5"></i>
                    </div>
                </div>
                <div class="mt-3 text-[11px] text-slate-500 dark:text-slate-400">
                    Total terdaftar: <span class="font-semibold text-slate-700 dark:text-slate-300">{{ number_format($stats['total']) }}</span> siswa
                </div>
            </div>

            <!-- Stat Card 2: Laki-laki -->
            <div class="animate-card bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-xs flex flex-col justify-between">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Laki-laki</p>
                        <h3 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-50 mt-1">
                            {{ number_format($stats['male']) }}
                        </h3>
                    </div>
                    <div class="p-2.5 bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 rounded-xl border border-blue-100 dark:border-blue-900/50">
                        <i data-lucide="user" class="w-5 h-5"></i>
                    </div>
                </div>
                <div class="mt-3 text-[11px] text-slate-500 dark:text-slate-400">
                    Siswa aktif putra
                </div>
            </div>

            <!-- Stat Card 3: Perempuan -->
            <div class="animate-card bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-xs flex flex-col justify-between">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Perempuan</p>
                        <h3 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-50 mt-1">
                            {{ number_format($stats['female']) }}
                        </h3>
                    </div>
                    <div class="p-2.5 bg-pink-50 dark:bg-pink-950/40 text-pink-600 dark:text-pink-400 rounded-xl border border-pink-100 dark:border-pink-900/50">
                        <i data-lucide="user" class="w-5 h-5"></i>
                    </div>
                </div>
                <div class="mt-3 text-[11px] text-slate-500 dark:text-slate-400">
                    Siswa aktif putri
                </div>
            </div>

            <!-- Stat Card 4: Jalur SPMB -->
            <div class="animate-card bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-xs flex flex-col justify-between">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Dari Jalur SPMB</p>
                        <h3 class="text-2xl font-bold tracking-tight text-emerald-600 dark:text-emerald-400 mt-1">
                            {{ number_format($stats['spmb_enrolled']) }}
                        </h3>
                    </div>
                    <div class="p-2.5 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 rounded-xl border border-emerald-100 dark:border-emerald-900/50">
                        <i data-lucide="user-check" class="w-5 h-5"></i>
                    </div>
                </div>
                <div class="mt-3 text-[11px] text-slate-500 dark:text-slate-400">
                    Tersinkronisasi otomatis
                </div>
            </div>
        </section>

        <!-- SEARCH & FILTERS SECTION -->
        <section class="animate-card bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-4 shadow-xs w-full">
            <form method="GET" action="{{ route('students.index') }}" class="flex flex-col md:flex-row gap-3 items-stretch md:items-center justify-between">
                
                <!-- Search Box -->
                <div class="relative w-full md:max-w-md">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <i data-lucide="search" class="w-4 h-4 text-slate-400 dark:text-slate-500"></i>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, NIS, NIK, NISN, atau no HP..."
                        style="padding-left: 2.25rem;"
                        class="w-full h-9 pr-4 text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-slate-900 dark:text-slate-50 placeholder-slate-400 dark:placeholder-slate-500 transition-all shadow-inner">
                </div>

                <!-- Filter Toolbar -->
                <div class="flex flex-wrap items-center gap-2 w-full md:w-auto">
                    <!-- Filter Tahun Ajaran (Strictly defaults to active TA) -->
                    <select name="academic_year_id" onchange="this.form.submit()"
                        class="h-9 px-3 text-xs font-semibold bg-indigo-50/50 dark:bg-indigo-950/30 border border-indigo-200 dark:border-indigo-800/60 rounded-lg text-indigo-900 dark:text-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 cursor-pointer shadow-xs">
                        @foreach($academicYears as $ay)
                            <option value="{{ $ay->id }}" {{ $selectedYearId == $ay->id ? 'selected' : '' }}>
                                TA {{ $ay->name }} {{ $ay->is_active ? '(Aktif)' : '' }}
                            </option>
                        @endforeach
                    </select>

                    <!-- Filter Tingkat Kelas -->
                    <select name="class_level_id" onchange="this.form.submit()"
                        class="h-9 px-3 text-xs font-medium bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 cursor-pointer shadow-xs">
                        <option value="">Semua Tingkat</option>
                        @foreach($classLevels as $lvl)
                            <option value="{{ $lvl->id }}" {{ request('class_level_id') == $lvl->id ? 'selected' : '' }}>
                                {{ $lvl->name }}
                            </option>
                        @endforeach
                    </select>

                    <!-- Filter Rombel -->
                    <select name="classroom_id" onchange="this.form.submit()"
                        class="h-9 px-3 text-xs font-medium bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 cursor-pointer shadow-xs">
                        <option value="">Semua Rombel</option>
                        @foreach($classrooms as $c)
                            <option value="{{ $c->id }}" {{ request('classroom_id') == $c->id ? 'selected' : '' }}>
                                {{ $c->name }}
                            </option>
                        @endforeach
                    </select>

                    <!-- Filter Status Siswa -->
                    <select name="status" onchange="this.form.submit()"
                        class="h-9 px-3 text-xs font-medium bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 cursor-pointer shadow-xs">
                        <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>Semua Status</option>
                        <option value="aktif" {{ request('status', 'aktif') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="lulus" {{ request('status') == 'lulus' ? 'selected' : '' }}>Lulus</option>
                        <option value="mutasi_keluar" {{ request('status') == 'mutasi_keluar' ? 'selected' : '' }}>Mutasi Keluar</option>
                        <option value="non_aktif" {{ request('status') == 'non_aktif' ? 'selected' : '' }}>Non-Aktif</option>
                    </select>

                    @if(request()->hasAny(['search', 'class_level_id', 'classroom_id']) || (request('status') && request('status') !== 'aktif'))
                        <a href="{{ route('students.index') }}" 
                            class="h-9 px-3 inline-flex items-center justify-center text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 rounded-lg border border-slate-200 dark:border-slate-700 transition-colors"
                            title="Reset Filter">
                            <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                        </a>
                    @endif
                </div>
            </form>
        </section>

        <!-- TABLE SECTION -->
        <section class="animate-card bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xs overflow-hidden transition-all w-full">
            <div class="overflow-x-auto">
                <table class="w-full text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/75 dark:bg-slate-900/50">
                            <th class="px-4 py-3.5 text-left text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-12">No</th>
                            <th class="px-4 py-3.5 text-left text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Identitas Siswa</th>
                            <th class="px-4 py-3.5 text-left text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-36">NIS / NISN</th>
                            <th class="px-4 py-3.5 text-left text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-36">Rombel & Tingkat</th>
                            <th class="px-4 py-3.5 text-left text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-40">Kontak Orang Tua</th>
                            <th class="px-4 py-3.5 text-center text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-24">Jalur</th>
                            <th class="px-4 py-3.5 text-center text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-24">Status</th>
                            <th class="px-4 py-3.5 text-right text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-28">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                        @forelse($students as $index => $student)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors group">
                                <td class="px-4 py-3.5 text-slate-400 font-mono text-[11px]">
                                    {{ $students->firstItem() + $index }}
                                </td>
                                <td class="px-4 py-3.5">
                                    <div class="flex items-center gap-3">
                                        @if($student->photo_url)
                                            <img src="{{ $student->photo_url }}" alt="{{ $student->full_name }}" class="w-9 h-9 rounded-xl object-cover shrink-0 border border-slate-200 dark:border-slate-700">
                                        @else
                                            <div class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 border border-indigo-100 dark:border-indigo-900/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-xs shrink-0">
                                                {{ $student->initials }}
                                            </div>
                                        @endif
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-2">
                                                <button type="button" @click="openDetailModal({{ $student->id }})" class="font-bold text-slate-900 dark:text-slate-100 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors text-left truncate text-xs cursor-pointer">
                                                    {{ $student->full_name }}
                                                </button>
                                                <span class="px-1.5 py-0.2 rounded text-[10px] font-bold {{ $student->gender === 'L' ? 'bg-blue-50 text-blue-700 dark:bg-blue-950/50 dark:text-blue-400 border border-blue-200' : 'bg-pink-50 text-pink-700 dark:bg-pink-950/50 dark:text-pink-400 border border-pink-200' }}">
                                                    {{ $student->gender }}
                                                </span>
                                            </div>
                                            <div class="flex items-center gap-2 mt-0.5 text-[11px] text-slate-500 dark:text-slate-400">
                                                @if($student->nickname)
                                                    <span>"{{ $student->nickname }}"</span>
                                                    <span>&bull;</span>
                                                @endif
                                                <span>{{ $student->birth_place ?? '-' }}, {{ $student->formatted_birth_date ?? '-' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3.5 font-mono text-[11px]">
                                    <div class="font-bold text-indigo-600 dark:text-indigo-400">{{ $student->nis }}</div>
                                    <div class="text-[10px] text-slate-400">NISN: {{ $student->nisn ?? '-' }}</div>
                                </td>
                                <td class="px-4 py-3.5">
                                    <div class="flex items-center gap-1.5">
                                        <span class="px-2 py-0.5 rounded-md font-bold text-xs bg-indigo-50 text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800/60">
                                            {{ $student->classroom->name ?? '-' }}
                                        </span>
                                        <span class="text-[11px] text-slate-400">
                                            ({{ $student->classLevel->name ?? '-' }})
                                        </span>
                                    </div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">
                                        TA {{ $student->academicYear->name ?? '-' }}
                                    </div>
                                </td>
                                <td class="px-4 py-3.5">
                                    <div class="flex items-center gap-1.5 font-medium text-slate-700 dark:text-slate-300">
                                        <i data-lucide="phone" class="w-3 h-3 text-slate-400"></i>
                                        <span>{{ $student->parent_phone ?? ($student->father_phone ?? ($student->mother_phone ?? '-')) }}</span>
                                    </div>
                                    <div class="text-[10px] text-slate-400 truncate max-w-xs mt-0.5">
                                        Ayah: {{ $student->father_name ?? '-' }}
                                    </div>
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase {{ $student->enrollment_type === 'spmb' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/50 dark:text-emerald-400' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400' }}">
                                        {{ $student->enrollment_type }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    @php
                                        $statusClass = match($student->status) {
                                            'aktif' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800',
                                            'lulus' => 'bg-blue-50 text-blue-700 dark:bg-blue-950/50 dark:text-blue-400 border-blue-200 dark:border-blue-800',
                                            'mutasi_keluar' => 'bg-amber-50 text-amber-700 dark:bg-amber-950/50 dark:text-amber-400 border-amber-200 dark:border-amber-800',
                                            default => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border-slate-200 dark:border-slate-700',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $statusClass }}">
                                        {{ ucfirst(str_replace('_', ' ', $student->status)) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <button type="button" @click="openDetailModal({{ $student->id }})"
                                            class="p-1.5 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 rounded-lg transition-colors cursor-pointer"
                                            title="Detail Siswa">
                                            <i data-lucide="eye" class="w-4 h-4"></i>
                                        </button>
                                        <button type="button" @click="openEditModal({{ $student->id }})"
                                            class="p-1.5 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 rounded-lg transition-colors cursor-pointer"
                                            title="Edit Data">
                                            <i data-lucide="edit-2" class="w-4 h-4"></i>
                                        </button>
                                        <button type="button" @click="deleteStudent({{ $student->id }}, '{{ addslashes($student->full_name) }}')"
                                            class="p-1.5 hover:bg-rose-50 dark:hover:bg-rose-950/40 text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 rounded-lg transition-colors cursor-pointer"
                                            title="Hapus Siswa">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center text-slate-400">
                                    <div class="flex flex-col items-center justify-center max-w-sm mx-auto">
                                        <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-950/50 flex items-center justify-center text-indigo-500 mb-3 border border-indigo-100 dark:border-indigo-900/50">
                                            <i data-lucide="users" class="w-6 h-6"></i>
                                        </div>
                                        <h4 class="text-sm font-bold text-slate-800 dark:text-slate-200">Tidak Ada Data Siswa</h4>
                                        <p class="text-xs text-slate-400 mt-1 mb-4 text-center">
                                            Belum ada data siswa untuk filter tahun ajaran / rombel yang dipilih.
                                        </p>
                                        <button type="button" @click="importModalOpen = true" class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold shadow-xs cursor-pointer">
                                            <i data-lucide="file-spreadsheet" class="w-3.5 h-3.5"></i>
                                            Impor Data dari Excel
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- PAGINATION -->
            @if($students->hasPages())
                <div class="p-4 border-t border-slate-200 dark:border-slate-800">
                    {{ $students->links() }}
                </div>
            @endif
        </section>

        <!-- MODAL IMPOR EXCEL SISWA -->
        <div x-show="importModalOpen" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center p-4" style="display: none; margin-top: 0px !important; z-index: 9999; background-color: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px);">
            <div @click.outside="importModalOpen = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-lg shadow-2xl flex flex-col">
                
                <form @submit.prevent="submitImport">
                    <div class="p-5 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <div class="p-2 bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 rounded-xl border border-emerald-200 dark:border-emerald-800">
                                <i data-lucide="file-spreadsheet" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-900 dark:text-slate-50">Impor Data Siswa Excel</h3>
                                <p class="text-xs text-slate-400 mt-0.5">Unggah data siswa SMP secara massal.</p>
                            </div>
                        </div>
                        <button type="button" @click="importModalOpen = false" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

                    <div class="p-6 space-y-4 text-xs">
                        <div class="p-4 rounded-xl bg-indigo-50/70 dark:bg-indigo-950/40 border border-indigo-200 dark:border-indigo-800/60 space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-indigo-900 dark:text-indigo-300">Format Template Excel</span>
                                <a href="{{ route('students.template') }}" class="inline-flex items-center gap-1.5 px-3 py-1 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg text-xs shadow-xs transition-colors">
                                    <i data-lucide="download" class="w-3.5 h-3.5"></i>
                                    Unduh Template
                                </a>
                            </div>
                            <p class="text-[11px] text-indigo-700/80 dark:text-indigo-300/80">
                                Gunakan template resmi kami agar format kolom NIS, Nama, Tingkat (7/8/9), dan Rombel (7-A/8-B/9-A) sesuai dengan sistem.
                            </p>
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Pilih Tahun Ajaran Sasaran <span class="text-rose-500">*</span></label>
                            <select x-model="importData.academic_year_id" required
                                class="w-full h-9 px-3 text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 text-slate-900 dark:text-slate-50 cursor-pointer">
                                @foreach($academicYears as $ay)
                                    <option value="{{ $ay->id }}" {{ $selectedYearId == $ay->id ? 'selected' : '' }}>
                                        TA {{ $ay->name }} {{ $ay->is_active ? '(Aktif)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">File Excel (.xlsx / .xls / .csv) <span class="text-rose-500">*</span></label>
                            <input type="file" id="excelFileInput" accept=".xlsx,.xls,.csv" required
                                class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 cursor-pointer">
                        </div>
                    </div>

                    <div class="p-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 flex justify-end gap-2">
                        <button type="button" @click="importModalOpen = false" class="px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 text-slate-700 dark:text-slate-300 rounded-lg text-xs font-semibold transition-colors">
                            Batal
                        </button>
                        <button type="submit" :disabled="importing" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white rounded-lg text-xs font-bold transition-all shadow-xs flex items-center gap-1.5">
                            <span x-text="importing ? 'Memproses Impor...' : 'Mulai Impor Siswa'"></span>
                        </button>
                    </div>
                </form>

            </div>
        </div>

        <!-- MODAL TAMBAH / EDIT SISWA -->
        <div x-show="formModalOpen" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center p-4" style="display: none; margin-top: 0px !important; z-index: 9999; background-color: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px);">
            <div @click.outside="formModalOpen = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto shadow-2xl flex flex-col">
                
                <form @submit.prevent="submitStudentForm">
                    <div class="p-5 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between sticky top-0 bg-white/95 dark:bg-slate-900/95 backdrop-blur-sm z-10">
                        <div>
                            <h3 class="text-base font-bold text-slate-900 dark:text-slate-50" x-text="isEdit ? 'Edit Data Siswa' : 'Tambah Siswa Baru'"></h3>
                            <p class="text-xs text-slate-400 mt-0.5">Lengkapi identitas siswa dan penempatan rombel.</p>
                        </div>
                        <button type="button" @click="formModalOpen = false" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

                    <div class="p-6 space-y-4 text-xs">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">NIS <span class="text-rose-500">*</span></label>
                                <input type="text" x-model="formData.nis" required placeholder="Contoh: 26.SMP.001"
                                    class="w-full h-9 px-3 text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 font-mono">
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama Lengkap <span class="text-rose-500">*</span></label>
                                <input type="text" x-model="formData.full_name" required placeholder="Nama lengkap siswa"
                                    class="w-full h-9 px-3 text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama Panggilan</label>
                                <input type="text" x-model="formData.nickname" placeholder="Nama panggilan"
                                    class="w-full h-9 px-3 text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Jenis Kelamin <span class="text-rose-500">*</span></label>
                                <select x-model="formData.gender" required
                                    class="w-full h-9 px-3 text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                                    <option value="L">Laki-laki (L)</option>
                                    <option value="P">Perempuan (P)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Agama</label>
                                <input type="text" x-model="formData.religion" placeholder="Islam"
                                    class="w-full h-9 px-3 text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Tempat Lahir</label>
                                <input type="text" x-model="formData.birth_place" placeholder="Kota lahir"
                                    class="w-full h-9 px-3 text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Tanggal Lahir</label>
                                <input type="date" x-model="formData.birth_date"
                                    class="w-full h-9 px-3 text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Tahun Ajaran <span class="text-rose-500">*</span></label>
                                <select x-model="formData.academic_year_id" required
                                    class="w-full h-9 px-3 text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 cursor-pointer">
                                    @foreach($academicYears as $ay)
                                        <option value="{{ $ay->id }}">TA {{ $ay->name }} {{ $ay->is_active ? '(Aktif)' : '' }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Tingkat Kelas <span class="text-rose-500">*</span></label>
                                <select x-model="formData.class_level_id" required
                                    class="w-full h-9 px-3 text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 cursor-pointer">
                                    @foreach($classLevels as $lvl)
                                        <option value="{{ $lvl->id }}">{{ $lvl->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Rombongan Belajar <span class="text-rose-500">*</span></label>
                                <select x-model="formData.classroom_id" required
                                    class="w-full h-9 px-3 text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 cursor-pointer">
                                    <option value="">Pilih Rombel...</option>
                                    @foreach($classrooms as $c)
                                        <option value="{{ $c->id }}">{{ $c->name }} (TA {{ $c->academicYear->name ?? '' }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Jalur Pendaftaran <span class="text-rose-500">*</span></label>
                                <select x-model="formData.enrollment_type" required
                                    class="w-full h-9 px-3 text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                                    <option value="spmb">SPMB</option>
                                    <option value="mutasi">Mutasi Masuk</option>
                                    <option value="manual">Manual</option>
                                    <option value="import">Impor Excel</option>
                                </select>
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Status Siswa <span class="text-rose-500">*</span></label>
                                <select x-model="formData.status" required
                                    class="w-full h-9 px-3 text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                                    <option value="aktif">Aktif</option>
                                    <option value="lulus">Lulus</option>
                                    <option value="mutasi_keluar">Mutasi Keluar</option>
                                    <option value="drop_out">Drop Out</option>
                                    <option value="non_aktif">Non Aktif</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">No. HP / WA Wali Murid</label>
                                <input type="text" x-model="formData.parent_phone" placeholder="081234567890"
                                    class="w-full h-9 px-3 text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 font-mono">
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama Ayah</label>
                                <input type="text" x-model="formData.father_name" placeholder="Nama ayah"
                                    class="w-full h-9 px-3 text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                            </div>
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Alamat Lengkap</label>
                            <textarea x-model="formData.address" rows="2" placeholder="Alamat tempat tinggal siswa..."
                                class="w-full p-3 text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"></textarea>
                        </div>
                    </div>

                    <div class="p-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 flex justify-end gap-2">
                        <button type="button" @click="formModalOpen = false" class="px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 text-slate-700 dark:text-slate-300 rounded-lg text-xs font-semibold">
                            Batal
                        </button>
                        <button type="submit" :disabled="saving" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white rounded-lg text-xs font-bold shadow-xs">
                            <span x-text="saving ? 'Menyimpan...' : (isEdit ? 'Simpan Perubahan' : 'Tambah Siswa')"></span>
                        </button>
                    </div>
                </form>

            </div>
        </div>

        <!-- MODAL DETAIL SISWA -->
        <div x-show="detailModalOpen" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center p-4" style="display: none; margin-top: 0px !important; z-index: 9999; background-color: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px);">
            <div @click.outside="detailModalOpen = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-lg max-h-[85vh] overflow-y-auto shadow-2xl flex flex-col">
                <div class="p-5 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between sticky top-0 bg-white/95 dark:bg-slate-900/95 backdrop-blur-sm z-10">
                    <h3 class="text-base font-bold text-slate-900 dark:text-slate-50">Biodata Siswa</h3>
                    <button type="button" @click="detailModalOpen = false" class="p-2 text-slate-400 hover:text-slate-600 rounded-lg">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
                <div class="p-6 space-y-4 text-xs" x-show="selectedStudent">
                    <div class="flex items-center gap-4 pb-4 border-b border-slate-100 dark:border-slate-800">
                        <template x-if="selectedStudent?.photo_url">
                            <img :src="selectedStudent?.photo_url" class="w-14 h-14 rounded-2xl object-cover border border-slate-200">
                        </template>
                        <template x-if="!selectedStudent?.photo_url">
                            <div class="w-14 h-14 rounded-2xl bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 font-bold text-lg flex items-center justify-center" x-text="selectedStudent?.initials || 'S'"></div>
                        </template>
                        <div>
                            <h4 class="text-sm font-bold text-slate-900 dark:text-slate-100" x-text="selectedStudent?.full_name"></h4>
                            <p class="font-mono text-indigo-600 dark:text-indigo-400 font-bold" x-text="'NIS: ' + (selectedStudent?.nis || '-')"></p>
                            <p class="text-[11px] text-slate-400" x-text="'Rombel: ' + (selectedStudent?.classroom?.name || '-') + ' | TA ' + (selectedStudent?.academic_year?.name || '-')"></p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 text-slate-600 dark:text-slate-300">
                        <div>
                            <span class="text-slate-400 text-[10px] uppercase block">Jenis Kelamin</span>
                            <span class="font-semibold" x-text="selectedStudent?.gender === 'L' ? 'Laki-laki' : 'Perempuan'"></span>
                        </div>
                        <div>
                            <span class="text-slate-400 text-[10px] uppercase block">Tempat, Tanggal Lahir</span>
                            <span class="font-semibold" x-text="(selectedStudent?.birth_place || '-') + ', ' + (selectedStudent?.formatted_birth_date || '-')"></span>
                        </div>
                        <div>
                            <span class="text-slate-400 text-[10px] uppercase block">No. HP / WA Ortu</span>
                            <span class="font-semibold font-mono" x-text="selectedStudent?.parent_phone || '-'"></span>
                        </div>
                        <div>
                            <span class="text-slate-400 text-[10px] uppercase block">Nama Ayah</span>
                            <span class="font-semibold" x-text="selectedStudent?.father_name || '-'"></span>
                        </div>
                        <div class="col-span-2">
                            <span class="text-slate-400 text-[10px] uppercase block">Alamat</span>
                            <span class="font-semibold" x-text="selectedStudent?.address || '-'"></span>
                        </div>
                    </div>
                </div>
                <div class="p-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 flex justify-end">
                    <button type="button" @click="detailModalOpen = false" class="px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 rounded-lg text-xs font-semibold">
                        Tutup
                    </button>
                </div>
            </div>
        </div>

    </div>

    <!-- Alpine.js Script -->
    <script>
        function studentApp() {
            return {
                importModalOpen: false,
                formModalOpen: false,
                detailModalOpen: false,
                isEdit: false,
                saving: false,
                importing: false,
                selectedStudent: null,

                importData: {
                    academic_year_id: '{{ $selectedYearId ?? ($activeYear?->id ?? '') }}',
                },

                formData: {
                    id: null,
                    nis: '',
                    nisn: '',
                    nik: '',
                    full_name: '',
                    nickname: '',
                    gender: 'L',
                    religion: 'Islam',
                    birth_place: '',
                    birth_date: '',
                    academic_year_id: '{{ $selectedYearId ?? ($activeYear?->id ?? '') }}',
                    class_level_id: '{{ $classLevels->first()?->id ?? '' }}',
                    classroom_id: '',
                    enrollment_type: 'spmb',
                    status: 'aktif',
                    parent_phone: '',
                    father_name: '',
                    address: '',
                },

                openCreateModal() {
                    this.isEdit = false;
                    this.formData = {
                        id: null,
                        nis: '',
                        nisn: '',
                        nik: '',
                        full_name: '',
                        nickname: '',
                        gender: 'L',
                        religion: 'Islam',
                        birth_place: '',
                        birth_date: '',
                        academic_year_id: '{{ $selectedYearId ?? ($activeYear?->id ?? '') }}',
                        class_level_id: '{{ $classLevels->first()?->id ?? '' }}',
                        classroom_id: '',
                        enrollment_type: 'manual',
                        status: 'aktif',
                        parent_phone: '',
                        father_name: '',
                        address: '',
                    };
                    this.formModalOpen = true;
                },

                openEditModal(id) {
                    fetch(`/students/${id}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(res => res.json())
                    .then(res => {
                        if (res.success) {
                            const s = res.student;
                            this.isEdit = true;
                            this.formData = {
                                id: s.id,
                                nis: s.nis,
                                nisn: s.nisn || '',
                                nik: s.nik || '',
                                full_name: s.full_name,
                                nickname: s.nickname || '',
                                gender: s.gender || 'L',
                                religion: s.religion || 'Islam',
                                birth_place: s.birth_place || '',
                                birth_date: s.birth_date ? s.birth_date.substring(0, 10) : '',
                                academic_year_id: s.academic_year_id,
                                class_level_id: s.class_level_id,
                                classroom_id: s.classroom_id,
                                enrollment_type: s.enrollment_type || 'manual',
                                status: s.status || 'aktif',
                                parent_phone: s.parent_phone || '',
                                father_name: s.father_name || '',
                                address: s.address || '',
                            };
                            this.formModalOpen = true;
                        }
                    })
                    .catch(err => alert("Gagal mengambil data: " + err.message));
                },

                openDetailModal(id) {
                    fetch(`/students/${id}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(res => res.json())
                    .then(res => {
                        if (res.success) {
                            this.selectedStudent = res.student;
                            this.detailModalOpen = true;
                        }
                    })
                    .catch(err => alert("Gagal memuat detail: " + err.message));
                },

                submitStudentForm() {
                    if (this.saving) return;
                    this.saving = true;

                    const url = this.isEdit ? `/students/${this.formData.id}` : '/students';
                    const method = this.isEdit ? 'PUT' : 'POST';

                    fetch(url, {
                        method: method,
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(this.formData)
                    })
                    .then(res => res.json())
                    .then(res => {
                        this.saving = false;
                        if (res.success) {
                            this.formModalOpen = false;
                            alert(res.message || 'Data siswa berhasil disimpan!');
                            window.location.reload();
                        } else {
                            alert(res.message || 'Terjadi kesalahan.');
                        }
                    })
                    .catch(err => {
                        this.saving = false;
                        alert('Error: ' + err.message);
                    });
                },

                submitImport() {
                    const fileInput = document.getElementById('excelFileInput');
                    if (!fileInput.files.length) {
                        alert('Silakan pilih file Excel terlebih dahulu.');
                        return;
                    }

                    if (this.importing) return;
                    this.importing = true;

                    const form = new FormData();
                    form.append('file', fileInput.files[0]);
                    form.append('academic_year_id', this.importData.academic_year_id);

                    fetch('{{ route("students.import") }}', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: form
                    })
                    .then(res => res.json())
                    .then(res => {
                        this.importing = false;
                        if (res.success) {
                            this.importModalOpen = false;
                            alert(res.message || 'Impor data selesai!');
                            window.location.reload();
                        } else {
                            alert(res.message || 'Gagal memproses impor.');
                        }
                    })
                    .catch(err => {
                        this.importing = false;
                        alert('Error: ' + err.message);
                    });
                },

                deleteStudent(id, name) {
                    if (!confirm(`Hapus data siswa "${name}"?`)) return;

                    fetch(`/students/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(res => res.json())
                    .then(res => {
                        if (res.success) {
                            alert(res.message || 'Siswa berhasil dihapus!');
                            window.location.reload();
                        } else {
                            alert(res.message || 'Gagal menghapus siswa.');
                        }
                    })
                    .catch(err => alert('Error: ' + err.message));
                }
            }
        }
    </script>
</x-admin-layout>
