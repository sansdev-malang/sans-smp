<x-admin-layout>
    <div class="p-3.5 sm:p-5 lg:p-6 space-y-3.5 sm:space-y-4" x-data="homeroomApp()">

        <!-- PAGE HEADER -->
        <section class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 w-full text-left">
            <div class="flex flex-col gap-0.5">
                <h2 class="text-base sm:text-lg font-bold tracking-tight text-slate-900 dark:text-slate-50">Penugasan Pendidik &amp; Wali Kelas</h2>
                <p class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400">
                    Alokasi Wali Kelas, Guru Mapel, Guru Pendamping Khusus (GPK), dan GPQ per rombel tersimpan per tahun ajaran.
                </p>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <a href="{{ route('teachers.index') }}"
                    class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 sm:px-3.5 sm:py-2 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-lg border border-slate-200 dark:border-slate-800 shadow-xs transition-colors cursor-pointer">
                    <i data-lucide="graduation-cap" class="w-3.5 h-3.5 text-indigo-600 dark:text-indigo-400"></i>
                    Data Guru
                </a>
                <button type="button" @click="openCreateModal()"
                    class="inline-flex items-center justify-center gap-1.5 px-3.5 py-1.5 sm:px-4 sm:py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg shadow-sm transition-colors cursor-pointer">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    Tugaskan Pendidik
                </button>
            </div>
        </section>

        <!-- KPI STATS CARDS -->
        <section class="grid grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-3">
            <!-- Stat 1: Total Penugasan -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-3 sm:p-3.5 shadow-xs flex items-center gap-3 transition-colors">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0 border border-indigo-100 dark:border-indigo-900/40">
                    <i data-lucide="user-check" class="w-5 h-5"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-[10px] sm:text-[10.5px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider truncate">Total Penugasan</p>
                    <div class="flex items-baseline gap-1.5 mt-0.5">
                        <h3 class="text-lg sm:text-xl font-bold tracking-tight text-slate-900 dark:text-slate-50 font-mono">
                            {{ number_format($stats['total_assignments'] ?? $assignments->total()) }}
                        </h3>
                        <span class="text-[10px] text-slate-400 font-medium truncate">Pendidik</span>
                    </div>
                </div>
            </div>

            <!-- Stat 2: Wali Kelas -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-3 sm:p-3.5 shadow-xs flex items-center gap-3 transition-colors">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 border border-emerald-100 dark:border-emerald-900/40">
                    <i data-lucide="shield-check" class="w-5 h-5"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-[10px] sm:text-[10.5px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider truncate">Wali Kelas</p>
                    <div class="flex items-baseline gap-1.5 mt-0.5">
                        <h3 class="text-lg sm:text-xl font-bold tracking-tight text-emerald-600 dark:text-emerald-400 font-mono">
                            {{ number_format($stats['total_wali_kelas'] ?? 0) }}
                        </h3>
                        <span class="text-[10px] text-slate-400 font-medium truncate">/ {{ number_format($stats['total_classrooms'] ?? $classrooms->count()) }} Rombel</span>
                    </div>
                </div>
            </div>

            <!-- Stat 3: Guru Mapel -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-3 sm:p-3.5 shadow-xs flex items-center gap-3 transition-colors">
                <div class="w-10 h-10 rounded-xl bg-teal-50 dark:bg-teal-950/50 text-teal-600 dark:text-teal-400 flex items-center justify-center shrink-0 border border-teal-100 dark:border-teal-900/40">
                    <i data-lucide="book-open" class="w-5 h-5"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-[10px] sm:text-[10.5px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider truncate">Guru Mapel</p>
                    <div class="flex items-baseline gap-1.5 mt-0.5">
                        <h3 class="text-lg sm:text-xl font-bold tracking-tight text-teal-600 dark:text-teal-400 font-mono">
                            {{ number_format($stats['total_guru_mapel'] ?? 0) }}
                        </h3>
                        <span class="text-[10px] text-slate-400 font-medium truncate">Penugasan</span>
                    </div>
                </div>
            </div>

            <!-- Stat 4: GPK & GPQ -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-3 sm:p-3.5 shadow-xs flex items-center gap-3 transition-colors">
                <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0 border border-amber-100 dark:border-amber-900/40">
                    <i data-lucide="heart-handshake" class="w-5 h-5"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-[10px] sm:text-[10.5px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider truncate">GPK / GPQ</p>
                    <div class="flex items-baseline gap-1.5 mt-0.5">
                        <h3 class="text-lg sm:text-xl font-bold tracking-tight text-amber-600 dark:text-amber-400 font-mono">
                            {{ number_format(($stats['total_gpk'] ?? 0) + ($stats['total_gpq'] ?? 0)) }}
                        </h3>
                        <span class="text-[10px] text-slate-400 font-medium truncate">Inklusi &amp; Qur'an</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- FILTERS TOOLBAR -->
        <section class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-3 shadow-xs">
            <form method="GET" action="{{ route('homeroom-assignments.index') }}" class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-2.5">
                
                <!-- Left: Dropdowns -->
                <div class="flex flex-wrap items-center gap-2">
                    <!-- 1. Tahun Ajaran -->
                    <select name="academic_year_id" onchange="this.form.submit()"
                        class="h-9 px-3 text-xs font-semibold bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/80 rounded-lg text-slate-700 dark:text-slate-300 focus:outline-none cursor-pointer">
                        @foreach($academicYears as $ay)
                            <option value="{{ $ay->id }}" {{ $selectedYearId == $ay->id ? 'selected' : '' }}>
                                {{ $ay->name }} {{ $ay->is_active ? ' (Aktif)' : '' }}
                            </option>
                        @endforeach
                    </select>

                    <!-- 2. Tingkat / Kelas -->
                    <select name="class_level_id" onchange="this.form.submit()"
                        class="h-9 px-3 text-xs font-semibold bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/80 rounded-lg text-slate-700 dark:text-slate-300 focus:outline-none cursor-pointer">
                        <option value="all" {{ ($selectedClassLevelId ?? 'all') === 'all' ? 'selected' : '' }}>Semua Tingkat</option>
                        @foreach($classLevels as $lvl)
                            <option value="{{ $lvl->id }}" {{ ($selectedClassLevelId ?? '') == $lvl->id ? 'selected' : '' }}>
                                {{ $lvl->name }}
                            </option>
                        @endforeach
                    </select>

                    <!-- 3. Peran Pendidik -->
                    <select name="role" onchange="this.form.submit()"
                        class="h-9 px-3 text-xs font-semibold bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/80 rounded-lg text-slate-700 dark:text-slate-300 focus:outline-none cursor-pointer">
                        <option value="all" {{ ($selectedRole ?? 'all') === 'all' ? 'selected' : '' }}>Semua Peran</option>
                        <option value="wali_kelas" {{ ($selectedRole ?? '') === 'wali_kelas' ? 'selected' : '' }}>Wali Kelas</option>
                        <option value="guru_mapel" {{ ($selectedRole ?? '') === 'guru_mapel' ? 'selected' : '' }}>Guru Mapel</option>
                        <option value="gpk" {{ ($selectedRole ?? '') === 'gpk' ? 'selected' : '' }}>GPK (Inklusi)</option>
                        <option value="gpq" {{ ($selectedRole ?? '') === 'gpq' ? 'selected' : '' }}>GPQ (Al-Qur'an)</option>
                    </select>

                    <!-- 4. Status Penugasan -->
                    <select name="status" onchange="this.form.submit()"
                        class="h-9 px-3 text-xs font-semibold bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/80 rounded-lg text-slate-700 dark:text-slate-300 focus:outline-none cursor-pointer">
                        <option value="all" {{ ($selectedStatus ?? 'all') === 'all' ? 'selected' : '' }}>Semua Status</option>
                        <option value="active" {{ ($selectedStatus ?? '') === 'active' || ($selectedStatus ?? '') === '1' ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ ($selectedStatus ?? '') === 'inactive' || ($selectedStatus ?? '') === '0' ? 'selected' : '' }}>Non-aktif</option>
                    </select>

                    <!-- 5. Jumlah Baris -->
                    <select name="per_page" onchange="this.form.submit()"
                        class="h-9 px-3 text-xs font-semibold bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/80 rounded-lg text-slate-700 dark:text-slate-300 focus:outline-none cursor-pointer">
                        <option value="15" {{ request('per_page', 15) == 15 ? 'selected' : '' }}>15 Data</option>
                        <option value="25" {{ request('per_page', 15) == 25 ? 'selected' : '' }}>25 Data</option>
                        <option value="50" {{ request('per_page', 15) == 50 ? 'selected' : '' }}>50 Data</option>
                        <option value="100" {{ request('per_page', 15) == 100 ? 'selected' : '' }}>100 Data</option>
                        <option value="all" {{ request('per_page') == 'all' || request('per_page') == '99999' ? 'selected' : '' }}>Semua</option>
                    </select>

                    @if(request()->filled('search') || (request('class_level_id') && request('class_level_id') !== 'all') || (request('role') && request('role') !== 'all') || (request('status') && request('status') !== 'all') || (request()->filled('per_page') && request('per_page') != 15))
                        <a href="{{ route('homeroom-assignments.index', ['academic_year_id' => $selectedYearId]) }}" class="h-9 px-2.5 flex items-center justify-center bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-xs font-semibold text-slate-700 dark:text-slate-300 rounded-lg transition-colors" title="Reset Filter">
                            <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                        </a>
                    @endif
                </div>

                <!-- Right: Search Bar -->
                <div x-data="{ searchVal: '{{ request('search') }}' }" class="w-full sm:w-72 md:w-80 flex items-center search-container bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/80 rounded-lg overflow-hidden focus-within:ring-2 focus-within:ring-indigo-500/20 focus-within:border-indigo-500">
                    <span class="pl-3 text-slate-400">
                        <i data-lucide="search" class="w-3.5 h-3.5"></i>
                    </span>
                    <input type="text" name="search" x-model="searchVal" placeholder="Cari nama guru, rombel, mapel, NUPTK..."
                        style="border: none !important; outline: none !important; box-shadow: none !important;"
                        class="w-full h-9 px-2.5 text-xs bg-transparent text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:ring-0">
                    
                    <button type="button" x-show="searchVal.trim() !== ''" @click="searchVal = ''; $el.closest('.search-container').querySelector('input').focus();" class="h-9 px-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors cursor-pointer bg-transparent border-0 flex items-center justify-center" title="Bersihkan pencarian">
                        <i data-lucide="x" class="w-3.5 h-3.5"></i>
                    </button>

                    <button type="submit" 
                        class="h-9 px-3.5 font-semibold text-xs bg-slate-100 dark:bg-slate-700/60 hover:bg-indigo-600 hover:text-white dark:hover:bg-indigo-600 text-slate-700 dark:text-slate-200 transition-colors cursor-pointer whitespace-nowrap flex items-center justify-center border-l border-slate-200 dark:border-slate-700/80">
                        Cari
                    </button>
                </div>

            </form>
        </section>

        <!-- TABLE PENUGASAN -->
        <section class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xs overflow-hidden w-full">
            <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-900/30">
                <h3 class="text-xs font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                    <i data-lucide="user-check" class="w-3.5 h-3.5 text-indigo-600 dark:text-indigo-400"></i>
                    Daftar Penugasan Pendidik &amp; Tenaga Kependidikan
                </h3>
                <span class="text-[11px] text-slate-500 dark:text-slate-400">
                    Menampilkan <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $assignments->count() }}</span> dari <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $assignments->total() }}</span> data
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/75 dark:bg-slate-900/50">
                            <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider whitespace-nowrap">Tahun Ajaran</th>
                            <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider whitespace-nowrap">Tingkat</th>
                            <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider whitespace-nowrap">Rombel / Kelas</th>
                            <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider whitespace-nowrap">Peran</th>
                            <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider min-w-[200px]">Guru / Pendidik</th>
                            <th class="px-4 py-3 text-center text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider whitespace-nowrap w-28">Status</th>
                            <th class="px-4 py-3 text-right text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider whitespace-nowrap w-24">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                        @forelse($assignments as $a)
                            <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition-colors group">
                                
                                <!-- 1. Tahun Ajaran -->
                                <td class="px-4 py-3 font-semibold text-slate-900 dark:text-slate-100 whitespace-nowrap">
                                    <div class="flex items-center gap-1.5 font-bold">
                                        <i data-lucide="calendar" class="w-3.5 h-3.5 text-indigo-500"></i>
                                        <span>{{ $a->academicYear?->name ?? '-' }}</span>
                                    </div>
                                </td>

                                <!-- 2. Tingkat -->
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="inline-flex px-2 py-0.5 rounded-md text-[11px] font-bold bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                        {{ $a->classLevel?->name ?? $a->classroom?->classLevel?->name ?? '-' }}
                                    </span>
                                </td>

                                <!-- 3. Rombel / Kelas -->
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-lg bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0 border border-indigo-100 dark:border-indigo-900/40 font-bold text-xs">
                                            <i data-lucide="shapes" class="w-3.5 h-3.5"></i>
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-900 dark:text-slate-100 text-xs">
                                                {{ $a->classroom?->name ?? '-' }}
                                            </p>
                                            @if($a->classroom?->code)
                                                <p class="text-[10px] text-slate-400 font-mono">
                                                    Kode: {{ $a->classroom->code }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <!-- 4. Peran & Mapel -->
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if($a->role === 'wali_kelas')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[11px] font-bold bg-indigo-50 text-indigo-700 dark:bg-indigo-950/50 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                                            <i data-lucide="shield-check" class="w-3 h-3 text-indigo-600"></i>
                                            Wali Kelas
                                        </span>
                                    @elseif($a->role === 'guru_mapel')
                                        <div class="flex flex-col">
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[11px] font-bold bg-teal-50 text-teal-700 dark:bg-teal-950/50 dark:text-teal-300 border border-teal-200 dark:border-teal-800 w-fit">
                                                <i data-lucide="book-open" class="w-3 h-3 text-teal-600"></i>
                                                Guru Mapel
                                            </span>
                                            @if($a->subject)
                                                <span class="text-[10.5px] font-semibold text-slate-600 dark:text-slate-400 mt-0.5">
                                                    {{ $a->subject }}
                                                </span>
                                            @endif
                                        </div>
                                    @elseif($a->role === 'gpk')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[11px] font-bold bg-amber-50 text-amber-700 dark:bg-amber-950/50 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                            <i data-lucide="heart-handshake" class="w-3 h-3 text-amber-600"></i>
                                            GPK (Inklusi)
                                        </span>
                                    @elseif($a->role === 'gpq')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[11px] font-bold bg-rose-50 text-rose-700 dark:bg-rose-950/50 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                                            <i data-lucide="sparkles" class="w-3 h-3 text-rose-600"></i>
                                            GPQ (Al-Qur'an)
                                        </span>
                                    @else
                                        <span class="text-slate-600 font-semibold">{{ $a->role_label }}</span>
                                    @endif
                                </td>

                                <!-- 5. Guru / Pendidik -->
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2.5 min-w-[200px]">
                                        <div class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center font-bold text-xs shrink-0 border border-slate-200 dark:border-slate-700">
                                            {{ strtoupper(substr($a->teacher?->name ?? 'G', 0, 2)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-bold text-slate-900 dark:text-slate-100 text-xs truncate">
                                                {{ $a->teacher?->name ?? '-' }}
                                            </p>
                                            <div class="flex items-center gap-2 text-[10.5px] text-slate-400 font-mono mt-0.5">
                                                @if($a->teacher?->nuptk)
                                                    <span>NUPTK: {{ $a->teacher->nuptk }}</span>
                                                @elseif($a->teacher?->niy)
                                                    <span>NIY: {{ $a->teacher->niy }}</span>
                                                @elseif($a->teacher?->phone)
                                                    <span>{{ $a->teacher->phone }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- 6. Status -->
                                <td class="px-4 py-3 text-center whitespace-nowrap">
                                    <button type="button" @click="toggleStatus({{ $a->id }}, {{ $a->is_active ? 'false' : 'true' }})"
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10.5px] font-bold transition-all cursor-pointer {{ $a->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-400 dark:border-emerald-800' : 'bg-slate-100 text-slate-500 border border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700' }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $a->is_active ? 'bg-emerald-500 animate-pulse' : 'bg-slate-400' }}"></span>
                                        <span>{{ $a->is_active ? 'Aktif' : 'Non-aktif' }}</span>
                                    </button>
                                </td>

                                <!-- 7. Aksi -->
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1">
                                        <button type="button" @click="openEditModal({{ json_encode([
                                            'id' => $a->id,
                                            'academic_year_id' => $a->academic_year_id,
                                            'class_level_id' => $a->class_level_id,
                                            'classroom_id' => $a->classroom_id,
                                            'employee_id' => $a->employee_id,
                                            'role' => $a->role,
                                            'subject' => $a->subject,
                                            'is_active' => (bool)$a->is_active,
                                            'notes' => $a->notes,
                                        ]) }})"
                                            class="w-7 h-7 flex items-center justify-center rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 transition-colors"
                                            title="Edit Penugasan">
                                            <i data-lucide="edit-2" class="w-3.5 h-3.5"></i>
                                        </button>
                                        <button type="button" @click="confirmDelete({{ $a->id }}, '{{ addslashes($a->teacher?->name ?? 'Pendidik') }}', '{{ addslashes($a->classroom?->name ?? 'Rombel') }}')"
                                            class="w-7 h-7 flex items-center justify-center rounded-lg bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/50 dark:hover:bg-rose-900/60 text-rose-600 dark:text-rose-400 transition-colors"
                                            title="Hapus Penugasan">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </div>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-12 text-center text-slate-400 dark:text-slate-500">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <div class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-slate-800/60 flex items-center justify-center text-slate-400">
                                            <i data-lucide="users" class="w-6 h-6"></i>
                                        </div>
                                        <p class="font-semibold text-xs text-slate-600 dark:text-slate-400">Belum ada penugasan pendidik pada filter ini</p>
                                        <p class="text-[11px] text-slate-400">Klik tombol "Tugaskan Pendidik" untuk menambahkan alokasi baru.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- PAGINATION -->
            @if($assignments->hasPages())
                <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30">
                    {{ $assignments->links() }}
                </div>
            @endif
        </section>

        <!-- MODAL FORM CREATE / EDIT ASSIGNMENT -->
        <div x-show="modalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="modalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs transition-opacity" @click="modalOpen = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="modalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="inline-block align-bottom bg-white dark:bg-slate-900 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-200 dark:border-slate-800">
                    
                    <form @submit.prevent="submitForm()">
                        <div class="p-5 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-900/50">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold">
                                    <i data-lucide="user-plus" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100" x-text="isEdit ? 'Edit Penugasan Pendidik' : 'Tugaskan Pendidik Baru'"></h3>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400">Pilih rombel, peran, dan nama pendidik.</p>
                                </div>
                            </div>
                            <button type="button" @click="modalOpen = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
                                <i data-lucide="x" class="w-4 h-4"></i>
                            </button>
                        </div>

                        <div class="p-5 space-y-4 text-xs">
                            <!-- 1. Tahun Ajaran -->
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Tahun Ajaran <span class="text-rose-500">*</span></label>
                                <select x-model="formData.academic_year_id" required
                                    class="w-full h-9 px-3 text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-slate-900 dark:text-slate-50 font-bold">
                                    @foreach($academicYears as $ay)
                                        <option value="{{ $ay->id }}">{{ $ay->name }} {{ $ay->is_active ? ' (Aktif)' : '' }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- 2. Rombel / Kelas -->
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Rombel / Kelas <span class="text-rose-500">*</span></label>
                                <select x-model="formData.classroom_id" required
                                    class="w-full h-9 px-3 text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-slate-900 dark:text-slate-50 font-bold">
                                    <option value="">-- Pilih Rombel / Kelas --</option>
                                    @foreach($classrooms as $c)
                                        <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->classLevel?->name ?? 'Tingkat' }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- 3. Peran -->
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Peran Pendidik <span class="text-rose-500">*</span></label>
                                <select x-model="formData.role" required
                                    class="w-full h-9 px-3 text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-slate-900 dark:text-slate-50 font-bold">
                                    <option value="wali_kelas">Wali Kelas</option>
                                    <option value="guru_mapel">Guru Mapel</option>
                                    <option value="gpk">Guru Pendamping Khusus (GPK)</option>
                                    <option value="gpq">Guru Pendamping Al-Qur'an (GPQ)</option>
                                </select>
                            </div>

                            <!-- 4. Mata Pelajaran (Khusus Guru Mapel) -->
                            <div x-show="formData.role === 'guru_mapel'" x-cloak>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Mata Pelajaran <span class="text-rose-500">*</span></label>
                                <input type="text" x-model="formData.subject" placeholder="Contoh: Matematika, IPA, Bahasa Indonesia, dll."
                                    class="w-full h-9 px-3 text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-slate-900 dark:text-slate-50">
                            </div>

                            <!-- 5. Guru / Pendidik -->
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Guru / Pendidik <span class="text-rose-500">*</span></label>
                                <select x-model="formData.employee_id" required
                                    class="w-full h-9 px-3 text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-slate-900 dark:text-slate-50">
                                    <option value="">-- Pilih Guru / Pendidik --</option>
                                    @foreach($teachers as $t)
                                        <option value="{{ $t->id }}">{{ $t->name }} {{ $t->position ? '(' . $t->position . ')' : '' }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- 6. Catatan -->
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Catatan / Keterangan</label>
                                <textarea x-model="formData.notes" rows="2" placeholder="Catatan opsional mengenai penugasan ini..."
                                    class="w-full p-3 text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-slate-900 dark:text-slate-50"></textarea>
                            </div>

                            <!-- 7. Status -->
                            <div class="p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-700/50 flex items-center justify-between">
                                <div>
                                    <p class="font-semibold text-slate-800 dark:text-slate-200">Status Penugasan</p>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400">Jika aktif dan peran Wali Kelas, otomatis tersinkron ke rombel.</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" x-model="formData.is_active" class="sr-only peer">
                                    <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-slate-600 peer-checked:bg-indigo-600"></div>
                                </label>
                            </div>
                        </div>

                        <div class="p-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 flex justify-end gap-2">
                            <button type="button" @click="modalOpen = false" class="px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 text-slate-700 dark:text-slate-300 rounded-lg text-xs font-semibold transition-colors">
                                Batal
                            </button>
                            <button type="submit" :disabled="saving" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white rounded-lg text-xs font-bold transition-all shadow-xs flex items-center gap-1.5">
                                <span x-text="saving ? 'Menyimpan...' : (isEdit ? 'Simpan Perubahan' : 'Tugaskan Pendidik')"></span>
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>

        <!-- MODAL DELETE CONFIRMATION -->
        <div x-show="deleteModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="deleteModalOpen" class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs transition-opacity" @click="deleteModalOpen = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div x-show="deleteModalOpen" class="inline-block align-bottom bg-white dark:bg-slate-900 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-slate-200 dark:border-slate-800 p-5">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-950 text-rose-600 dark:text-rose-400 flex items-center justify-center font-bold shrink-0">
                            <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-slate-900 dark:text-slate-100">Hapus Penugasan Pendidik</h4>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                Yakin ingin menghapus penugasan <strong class="text-slate-800 dark:text-slate-200" x-text="deleteTeacherName"></strong> di rombel <strong class="text-slate-800 dark:text-slate-200" x-text="deleteClassroomName"></strong>?
                            </p>
                        </div>
                    </div>
                    <div class="mt-5 flex justify-end gap-2">
                        <button type="button" @click="deleteModalOpen = false" class="px-3.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-lg">
                            Batal
                        </button>
                        <form :action="deleteUrl" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-4 py-1.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-lg shadow-xs">
                                Ya, Hapus
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>

    @push('scripts')
    <script>
        function homeroomApp() {
            return {
                modalOpen: false,
                deleteModalOpen: false,
                isEdit: false,
                saving: false,
                deleteUrl: '',
                deleteTeacherName: '',
                deleteClassroomName: '',
                formData: {
                    id: null,
                    academic_year_id: '{{ $selectedYearId }}',
                    classroom_id: '',
                    employee_id: '',
                    role: 'wali_kelas',
                    subject: '',
                    is_active: true,
                    notes: '',
                },

                openCreateModal() {
                    this.isEdit = false;
                    this.formData = {
                        id: null,
                        academic_year_id: '{{ $selectedYearId }}',
                        classroom_id: '',
                        employee_id: '',
                        role: 'wali_kelas',
                        subject: '',
                        is_active: true,
                        notes: '',
                    };
                    this.modalOpen = true;
                    this.$nextTick(() => lucide.createIcons());
                },

                openEditModal(data) {
                    this.isEdit = true;
                    this.formData = {
                        id: data.id,
                        academic_year_id: data.academic_year_id,
                        classroom_id: data.classroom_id,
                        employee_id: data.employee_id,
                        role: data.role || 'wali_kelas',
                        subject: data.subject || '',
                        is_active: !!data.is_active,
                        notes: data.notes || '',
                    };
                    this.modalOpen = true;
                    this.$nextTick(() => lucide.createIcons());
                },

                confirmDelete(id, teacherName, classroomName) {
                    this.deleteUrl = `/homeroom-assignments/${id}`;
                    this.deleteTeacherName = teacherName;
                    this.deleteClassroomName = classroomName;
                    this.deleteModalOpen = true;
                    this.$nextTick(() => lucide.createIcons());
                },

                async toggleStatus(id, newStatus) {
                    try {
                        const res = await fetch(`/homeroom-assignments/${id}/toggle-status`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({ is_active: newStatus }),
                        });
                        const json = await res.json();
                        if (json.success) {
                            window.location.reload();
                        } else {
                            alert(json.message || 'Gagal mengubah status penugasan.');
                        }
                    } catch (e) {
                        alert('Terjadi kesalahan koneksi.');
                    }
                },

                async submitForm() {
                    this.saving = true;
                    const url = this.isEdit 
                        ? `/homeroom-assignments/${this.formData.id}` 
                        : '{{ route("homeroom-assignments.store") }}';
                    const method = this.isEdit ? 'PUT' : 'POST';

                    try {
                        const res = await fetch(url, {
                            method: method,
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify(this.formData),
                        });
                        const json = await res.json();
                        if (json.success) {
                            window.location.reload();
                        } else {
                            alert(json.message || 'Gagal menyimpan penugasan.');
                            this.saving = false;
                        }
                    } catch (e) {
                        alert('Terjadi kesalahan koneksi saat menyimpan.');
                        this.saving = false;
                    }
                }
            };
        }
    </script>
    @endpush
</x-admin-layout>
