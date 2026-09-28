<x-admin-layout>
    <!-- Alpine.js Application Logic (Loaded before DOM element for seamless initialization) -->
    <script>
        const MASTER_GPK_TEACHERS = Object.freeze(@json($teachers) || []);

        function studentApp() {
            return {
                init() {
                    const urlParams = new URLSearchParams(window.location.search);
                    const editStudentId = urlParams.get('edit_student_id');
                    const openCreate = urlParams.get('open_create');

                    if (editStudentId) {
                        this.openEditModal(editStudentId);
                        // Bersihkan parameter dari URL browser agar refresh tidak memicu buka modal berulang
                        urlParams.delete('edit_student_id');
                        const newUrl = window.location.pathname + (urlParams.toString() ? '?' + urlParams.toString() : '');
                        window.history.replaceState({}, '', newUrl);
                    } else if (openCreate) {
                        this.openCreateModal();
                        // Bersihkan parameter dari URL browser agar refresh tidak memicu buka modal berulang
                        urlParams.delete('open_create');
                        const newUrl = window.location.pathname + (urlParams.toString() ? '?' + urlParams.toString() : '');
                        window.history.replaceState({}, '', newUrl);
                    }
                },
                detailModalOpen: false,
                formModalOpen: false,
                importModalOpen: false,
                exportDropdownOpen: false,
                downloadingTemplate: false,
                activeDetailTab: 1,
                activeFormTab: 1,
                isEdit: false,
                saving: false,
                selectedStudent: null,
                classroomHistories: [],
                gpkDropdownOpen: false,
                gpkSearch: '',
                selectedGpkName: '',
                gpkTeacherList: MASTER_GPK_TEACHERS,

                filterGpkTeachers() {
                    const q = (this.gpkSearch || '').toLowerCase().trim();
                    if (!q) {
                        this.gpkTeacherList = MASTER_GPK_TEACHERS;
                        return;
                    }
                    this.gpkTeacherList = MASTER_GPK_TEACHERS.filter(t => {
                        const name = (t.full_name || t.name || '').toLowerCase();
                        const nip = (t.nip || '').toLowerCase();
                        const pos = (t.position || '').toLowerCase();
                        return name.includes(q) || nip.includes(q) || pos.includes(q);
                    });
                },

                selectGpk(id) {
                    this.formData.gpk_employee_id = id;
                    const found = MASTER_GPK_TEACHERS.find(t => t.id == id);
                    this.selectedGpkName = found ? (found.full_name || found.name) : '';
                    this.gpkDropdownOpen = false;
                    this.gpkSearch = '';
                    this.gpkTeacherList = MASTER_GPK_TEACHERS;
                    this.$nextTick(() => {
                        if (window.lucide) lucide.createIcons();
                    });
                },

                clearGpk() {
                    this.formData.gpk_employee_id = '';
                    this.selectedGpkName = '';
                    this.gpkDropdownOpen = false;
                    this.gpkSearch = '';
                    this.gpkTeacherList = MASTER_GPK_TEACHERS;
                    this.$nextTick(() => {
                        if (window.lucide) lucide.createIcons();
                    });
                },

                formData: {
                    id: null,
                    academic_year_id: '{{ $selectedYearId && $selectedYearId !== "all" ? $selectedYearId : ($academicYears->firstWhere("is_active", true)?->id ?? "") }}',
                    classroom_id: '',
                    nis: '',
                    nisn: '',
                    nik: '',
                    no_kk: '',
                    birth_certificate_no: '',
                    citizenship: 'WNI',
                    full_name: '',
                    nickname: '',
                    gender: 'L',
                    birth_place: '',
                    birth_date: '',
                    religion: 'Islam',
                    enrolled_date: '',

                    student_type: 'REGULER',
                    special_needs_type: '',
                    special_needs_notes: '',
                    gpk_employee_id: '',

                    address: '',
                    rt: '',
                    rw: '',
                    village: '',
                    district: '',
                    district_category: '',
                    city: 'Malang',
                    province: 'Jawa Timur',
                    postal_code: '',
                    residence_status: '',
                    distance_to_school: '',
                    home_phone: '',

                    father_name: '',
                    father_nik: '',
                    father_birth_place: '',
                    father_birth_date: '',
                    father_religion: 'Islam',
                    father_phone: '',
                    father_email: '',
                    father_education: '',
                    father_job: '',
                    father_company: '',
                    father_company_address: '',
                    father_company_phone: '',
                    father_income: '',
                    father_address: '',
                    father_social_media: '',

                    mother_name: '',
                    mother_nik: '',
                    mother_birth_place: '',
                    mother_birth_date: '',
                    mother_religion: 'Islam',
                    mother_phone: '',
                    mother_email: '',
                    mother_education: '',
                    mother_job: '',
                    mother_company: '',
                    mother_company_address: '',
                    mother_company_phone: '',
                    mother_income: '',
                    mother_address: '',
                    mother_social_media: '',

                    guardian_name: '',
                    guardian_nik: '',
                    guardian_relation: '',
                    guardian_birth_place: '',
                    guardian_birth_date: '',
                    guardian_religion: 'Islam',
                    guardian_phone: '',
                    guardian_job: '',
                    guardian_address: '',
                    guardian_company: '',
                    guardian_company_address: '',
                    guardian_company_phone: '',
                    guardian_income: '',
                    guardian_email: '',
                    guardian_social_media: '',

                    parent_phone: '',
                    parent_email: '',

                    child_number: '',
                    siblings_count: '',
                    step_siblings_count: '',
                    adoptive_siblings_count: '',
                    home_language: 'Bahasa Indonesia',
                    ethnic_group: '',

                    blood_type: '',
                    height: '',
                    weight: '',
                    skin_color: '',
                    hair_type: '',
                    hair_color: '',
                    severe_disease_history: '',
                    frequent_disease: '',

                    previous_school: '',
                    origin_category: 'SMP',
                    previous_school_address: '',
                    sttb_number_date: '',
                    graduation_year: '',
                    diploma_number: '',
                    continued_school: '',

                    status: 'aktif',
                    notes: '',
                },

                async downloadTemplate() {
                    if (this.downloadingTemplate) return;
                    this.downloadingTemplate = true;
                    try {
                        if (typeof NProgress !== 'undefined') {
                            NProgress.start();
                        }
                        const response = await fetch('{{ route('students.download-template') }}');
                        if (!response.ok) throw new Error('Gagal mengunduh file template Excel.');
                        
                        const blob = await response.blob();
                        const blobUrl = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.style.display = 'none';
                        a.setAttribute('data-no-loader', 'true');
                        a.href = blobUrl;
                        a.download = 'Template_Import_Siswa_SMP_Lengkap.xlsx';
                        document.body.appendChild(a);
                        a.click();
                        
                        setTimeout(() => {
                            window.URL.revokeObjectURL(blobUrl);
                            a.remove();
                        }, 2000);

                        if (window.showToastNotification) {
                            window.showToastNotification('Template Excel berhasil diunduh!', 'success');
                        }
                    } catch (err) {
                        if (window.showToastNotification) {
                            window.showToastNotification('Gagal mengunduh template: ' + err.message, 'error');
                        } else if (window.showToast) {
                            window.showToast('Gagal', err.message, 'error');
                        }
                    } finally {
                        this.downloadingTemplate = false;
                        if (typeof NProgress !== 'undefined') {
                            NProgress.done();
                        }
                    }
                },

                openDetailModal(id) {
                    this.activeDetailTab = 1;
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
                            this.classroomHistories = res.classroom_histories || [];
                            this.detailModalOpen = true;
                            this.$nextTick(() => {
                                if (window.lucide) lucide.createIcons();
                            });
                        }
                    })
                    .catch(err => {
                        if (window.showToastNotification) {
                            window.showToastNotification("Gagal memuat detail siswa: " + err.message, "error");
                        } else if (window.showToast) {
                            window.showToast("Gagal", err.message, "error");
                        }
                    });
                },

                openCreateModal() {
                    this.isEdit = false;
                    this.activeFormTab = 1;
                    this.gpkDropdownOpen = false;
                    this.gpkSearch = '';
                    this.selectedGpkName = '';
                    this.gpkTeacherList = MASTER_GPK_TEACHERS;
                    this.formData = {
                        id: null,
                        academic_year_id: '{{ $selectedYearId && $selectedYearId !== "all" ? $selectedYearId : ($academicYears->firstWhere("is_active", true)?->id ?? "") }}',
                        classroom_id: '',
                        nis: '',
                        nisn: '',
                        nik: '',
                        no_kk: '',
                        birth_certificate_no: '',
                        citizenship: 'WNI',
                        full_name: '',
                        nickname: '',
                        gender: 'L',
                        birth_place: '',
                        birth_date: '',
                        religion: 'Islam',
                        enrolled_date: '',

                        student_type: 'REGULER',
                        special_needs_type: '',
                        special_needs_notes: '',
                        gpk_employee_id: '',

                        address: '',
                        rt: '',
                        rw: '',
                        village: '',
                        district: '',
                        district_category: '',
                        city: 'Malang',
                        province: 'Jawa Timur',
                        postal_code: '',
                        residence_status: '',
                        distance_to_school: '',
                        home_phone: '',

                        father_name: '',
                        father_nik: '',
                        father_birth_place: '',
                        father_birth_date: '',
                        father_religion: 'Islam',
                        father_phone: '',
                        father_email: '',
                        father_education: '',
                        father_job: '',
                        father_company: '',
                        father_company_address: '',
                        father_company_phone: '',
                        father_income: '',
                        father_address: '',
                        father_social_media: '',

                        mother_name: '',
                        mother_nik: '',
                        mother_birth_place: '',
                        mother_birth_date: '',
                        mother_religion: 'Islam',
                        mother_phone: '',
                        mother_email: '',
                        mother_education: '',
                        mother_job: '',
                        mother_company: '',
                        mother_company_address: '',
                        mother_company_phone: '',
                        mother_income: '',
                        mother_address: '',
                        mother_social_media: '',

                        guardian_name: '',
                        guardian_nik: '',
                        guardian_relation: '',
                        guardian_birth_place: '',
                        guardian_birth_date: '',
                        guardian_religion: 'Islam',
                        guardian_phone: '',
                        guardian_job: '',
                        guardian_address: '',
                        guardian_company: '',
                        guardian_company_address: '',
                        guardian_company_phone: '',
                        guardian_income: '',
                        guardian_email: '',
                        guardian_social_media: '',

                        parent_phone: '',
                        parent_email: '',

                        child_number: '',
                        siblings_count: '',
                        step_siblings_count: '',
                        adoptive_siblings_count: '',
                        home_language: 'Bahasa Indonesia',
                        ethnic_group: '',

                        blood_type: '',
                        height: '',
                        weight: '',
                        skin_color: '',
                        hair_type: '',
                        hair_color: '',
                        severe_disease_history: '',
                        frequent_disease: '',

                        previous_school: '',
                        origin_category: 'SMP',
                        previous_school_address: '',
                        sttb_number_date: '',
                        graduation_year: '',
                        diploma_number: '',
                        continued_school: '',

                        status: 'aktif',
                        notes: '',
                    };
                    this.formModalOpen = true;
                    this.$nextTick(() => {
                        if (window.lucide) lucide.createIcons();
                    });
                },

                openEditModal(id) {
                    this.activeFormTab = 1;
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
                            this.gpkDropdownOpen = false;
                            this.gpkSearch = '';
                            if (s.gpk_employee_id) {
                                const found = MASTER_GPK_TEACHERS.find(t => t.id == s.gpk_employee_id);
                                this.selectedGpkName = found ? (found.full_name || found.name) : '';
                            } else {
                                this.selectedGpkName = '';
                            }
                            this.gpkTeacherList = MASTER_GPK_TEACHERS;
                            this.formData = {
                                id: s.id,
                                academic_year_id: s.academic_year_id || '',
                                classroom_id: s.classroom_id || '',
                                nis: s.nis || '',
                                nisn: s.nisn || '',
                                nik: s.nik || '',
                                no_kk: s.no_kk || '',
                                birth_certificate_no: s.birth_certificate_no || '',
                                citizenship: s.citizenship || 'WNI',
                                full_name: s.full_name || '',
                                nickname: s.nickname || '',
                                gender: s.gender || 'L',
                                birth_place: s.birth_place || '',
                                birth_date: s.birth_date ? s.birth_date.substring(0, 10) : '',
                                religion: s.religion || 'Islam',
                                enrolled_date: s.enrolled_date ? s.enrolled_date.substring(0, 10) : '',

                                student_type: (s.student_type && s.student_type.includes('PDBK')) ? 'PDBK' : (s.student_type || 'REGULER'),
                                special_needs_type: s.special_needs_type || '',
                                special_needs_notes: s.special_needs_notes || '',
                                gpk_employee_id: s.gpk_employee_id || '',

                                address: s.address || '',
                                rt: s.rt || '',
                                rw: s.rw || '',
                                village: s.village || '',
                                district: s.district || '',
                                district_category: s.district_category || '',
                                city: s.city || 'Malang',
                                province: s.province || 'Jawa Timur',
                                postal_code: s.postal_code || '',
                                residence_status: s.residence_status || '',
                                distance_to_school: s.distance_to_school || '',
                                home_phone: s.home_phone || '',

                                father_name: s.father_name || '',
                                father_nik: s.father_nik || '',
                                father_birth_place: s.father_birth_place || '',
                                father_birth_date: s.father_birth_date ? s.father_birth_date.substring(0, 10) : '',
                                father_religion: s.father_religion || 'Islam',
                                father_phone: s.father_phone || '',
                                father_email: s.father_email || '',
                                father_education: s.father_education || '',
                                father_job: s.father_job || '',
                                father_company: s.father_company || '',
                                father_company_address: s.father_company_address || '',
                                father_company_phone: s.father_company_phone || '',
                                father_income: s.father_income || '',
                                father_address: s.father_address || '',
                                father_social_media: s.father_social_media || '',

                                mother_name: s.mother_name || '',
                                mother_nik: s.mother_nik || '',
                                mother_birth_place: s.mother_birth_place || '',
                                mother_birth_date: s.mother_birth_date ? s.mother_birth_date.substring(0, 10) : '',
                                mother_religion: s.mother_religion || 'Islam',
                                mother_phone: s.mother_phone || '',
                                mother_email: s.mother_email || '',
                                mother_education: s.mother_education || '',
                                mother_job: s.mother_job || '',
                                mother_company: s.mother_company || '',
                                mother_company_address: s.mother_company_address || '',
                                mother_company_phone: s.mother_company_phone || '',
                                mother_income: s.mother_income || '',
                                mother_address: s.mother_address || '',
                                mother_social_media: s.mother_social_media || '',

                                guardian_name: s.guardian_name || '',
                                guardian_nik: s.guardian_nik || '',
                                guardian_relation: s.guardian_relation || '',
                                guardian_birth_place: s.guardian_birth_place || '',
                                guardian_birth_date: s.guardian_birth_date ? s.guardian_birth_date.substring(0, 10) : '',
                                guardian_religion: s.guardian_religion || 'Islam',
                                guardian_phone: s.guardian_phone || '',
                                guardian_job: s.guardian_job || '',
                                guardian_address: s.guardian_address || '',
                                guardian_company: s.guardian_company || '',
                                guardian_company_address: s.guardian_company_address || '',
                                guardian_company_phone: s.guardian_company_phone || '',
                                guardian_income: s.guardian_income || '',
                                guardian_email: s.guardian_email || '',
                                guardian_social_media: s.guardian_social_media || '',

                                parent_phone: s.parent_phone || '',
                                parent_email: s.parent_email || '',

                                child_number: s.child_number || '',
                                siblings_count: s.siblings_count !== null ? s.siblings_count : '',
                                step_siblings_count: s.step_siblings_count !== null ? s.step_siblings_count : '',
                                adoptive_siblings_count: s.adoptive_siblings_count !== null ? s.adoptive_siblings_count : '',
                                home_language: s.home_language || 'Bahasa Indonesia',
                                ethnic_group: s.ethnic_group || '',

                                blood_type: s.blood_type || '',
                                height: s.height || '',
                                weight: s.weight || '',
                                skin_color: s.skin_color || '',
                                hair_type: s.hair_type || '',
                                hair_color: s.hair_color || '',
                                severe_disease_history: s.severe_disease_history || '',
                                frequent_disease: s.frequent_disease || '',

                                previous_school: s.previous_school || '',
                                origin_category: s.origin_category || 'SMP',
                                previous_school_address: s.previous_school_address || '',
                                sttb_number_date: s.sttb_number_date || '',
                                graduation_year: s.graduation_year || '',
                                diploma_number: s.diploma_number || '',
                                continued_school: s.continued_school || '',

                                status: s.status || 'aktif',
                                notes: s.notes || '',
                            };
                            this.detailModalOpen = false;
                            this.formModalOpen = true;
                            this.$nextTick(() => {
                                if (window.lucide) lucide.createIcons();
                            });
                        }
                    })
                    .catch(err => {
                        if (window.showToastNotification) {
                            window.showToastNotification("Gagal mengambil data siswa: " + err.message, "error");
                        } else if (window.showToast) {
                            window.showToast("Gagal", err.message, "error");
                        }
                    });
                },

                submitForm() {
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
                            if (window.setPendingToast) {
                                window.setPendingToast(res.message || 'Data siswa berhasil disimpan!', 'success');
                            }
                            const cleanUrl = new URL(window.location.href);
                            cleanUrl.searchParams.delete('edit_student_id');
                            cleanUrl.searchParams.delete('open_create');
                            window.location.href = cleanUrl.toString();
                        } else {
                            if (window.showToastNotification) {
                                window.showToastNotification(res.message || 'Terjadi kesalahan saat menyimpan.', 'error');
                            } else if (window.showToast) {
                                window.showToast('Gagal', res.message || 'Terjadi kesalahan saat menyimpan.', 'error');
                            }
                        }
                    })
                    .catch(err => {
                        this.saving = false;
                        if (window.showToastNotification) {
                            window.showToastNotification('Error: ' + err.message, 'error');
                        } else if (window.showToast) {
                            window.showToast('Error', err.message, 'error');
                        }
                    });
                },

                confirmModal: {
                    open: false,
                    id: null,
                    title: '',
                    message: '',
                    loading: false
                },

                confirmDeleteStudent(id, name) {
                    this.confirmModal = {
                        open: true,
                        id: id,
                        title: 'Hapus Data Siswa?',
                        message: `Apakah Anda yakin ingin menghapus data siswa <strong>${name}</strong>? Data yang telah dihapus tidak dapat dipulihkan.`,
                        loading: false
                    };
                    this.$nextTick(() => {
                        if (window.lucide) lucide.createIcons();
                    });
                },

                executeConfirmDelete() {
                    if (this.confirmModal.loading) return;
                    this.confirmModal.loading = true;

                    fetch(`/students/${this.confirmModal.id}`, {
                        method: 'DELETE',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(async res => {
                        this.confirmModal.loading = false;
                        const data = await res.json();
                        if (res.ok && data.success) {
                            this.confirmModal.open = false;
                            if (window.setPendingToast) {
                                window.setPendingToast(data.message || 'Data siswa berhasil dihapus!', 'success');
                            }
                            window.location.reload();
                        } else {
                            const errMsg = data.message || 'Gagal menghapus data siswa.';
                            if (window.showToastNotification) {
                                window.showToastNotification(errMsg, 'error');
                            } else {
                                alert(errMsg);
                            }
                        }
                    })
                    .catch(err => {
                        this.confirmModal.loading = false;
                        if (window.showToastNotification) {
                            window.showToastNotification('Error: ' + err.message, 'error');
                        } else {
                            alert('Error: ' + err.message);
                        }
                    });
                }
            };
        }
        window.studentApp = studentApp;
        document.addEventListener('alpine:init', () => {
            if (typeof Alpine !== 'undefined' && Alpine.data) {
                Alpine.data('studentApp', studentApp);
            }
        });
    </script>

    <div class="p-4 sm:p-5 lg:p-6 space-y-4 lg:space-y-5" x-data="studentApp()">

        <!-- GREETING / PAGE TITLE -->
        <section class="flex flex-col md:flex-row justify-between items-start md:items-center gap-3.5 w-full text-left">
            <div class="flex flex-col gap-0.5">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 rounded-xl border border-indigo-500/20 flex items-center justify-center shrink-0">
                        <i data-lucide="users" class="w-5 h-5 shrink-0"></i>
                    </div>
                    <div>
                        <h2 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-50">
                            Daftar Siswa
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Database komprehensif peserta didik, riwayat kelas, inklusi, dan orang tua {{ setting('unit_name', 'SMP Anak Saleh') }}.</p>
                    </div>
                </div>
            </div>
            <!-- ACTION CONTROLS: INFO TAHUN AJARAN AKTIF & ACTION BUTTONS -->
            <div class="flex flex-wrap items-center gap-2 shrink-0">
                <!-- Info Badge Tahun Pelajaran Aktif -->
                <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-indigo-50/80 dark:bg-indigo-950/40 border border-indigo-200/80 dark:border-indigo-800/60 rounded-xl text-xs shadow-xs shrink-0">
                    <span class="flex h-2 w-2 relative shrink-0">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    <span class="text-slate-500 dark:text-slate-400 font-medium">Tapel Aktif:</span>
                    <span class="font-bold text-indigo-700 dark:text-indigo-300">
                        {{ $activeAcademicYear ? $activeAcademicYear->name : '2026/2027' }}
                    </span>
                </div>

                <button type="button" @click="importModalOpen = true"
                    class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-lg border border-slate-200 dark:border-slate-800 shadow-xs transition-colors cursor-pointer shrink-0">
                    <i data-lucide="upload" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400 shrink-0"></i>
                    <span>Impor Excel</span>
                </button>

                <!-- DROPDOWN EKSPOR & CETAK -->
                <div class="relative shrink-0" @click.outside="exportDropdownOpen = false">
                    <button type="button" @click="exportDropdownOpen = !exportDropdownOpen"
                        class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-lg border border-slate-200 dark:border-slate-800 shadow-xs transition-colors cursor-pointer">
                        <i data-lucide="download" class="w-3.5 h-3.5 text-indigo-600 dark:text-indigo-400 shrink-0"></i>
                        <span>Ekspor & Cetak</span>
                        <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200 shrink-0" :class="{ 'rotate-180': exportDropdownOpen }"></i>
                    </button>

                    <div x-show="exportDropdownOpen"
                        x-cloak
                        style="display: none;"
                        class="absolute right-0 mt-1.5 w-60 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xl py-1.5 z-50 divide-y divide-slate-100 dark:divide-slate-800/60">
                        
                        <div class="px-3 py-1.5 bg-slate-50/50 dark:bg-slate-800/30">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Pilihan Format Dokumen</p>
                        </div>

                        <div class="py-1">
                            <!-- Ekspor Excel -->
                            <a href="{{ route('students.export.excel', request()->all()) }}" @click="exportDropdownOpen = false"
                                class="flex items-center gap-2.5 px-3 py-2 text-xs text-slate-700 dark:text-slate-300 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 hover:text-emerald-700 dark:hover:text-emerald-400 transition-colors group">
                                <div class="w-7 h-7 flex items-center justify-center rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-800/60 shrink-0">
                                    <i data-lucide="file-spreadsheet" class="w-4 h-4 shrink-0"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <span class="font-semibold block text-slate-800 dark:text-slate-200 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 truncate">Ekspor File Excel</span>
                                    <span class="text-[10px] text-slate-400 block truncate">Format .xlsx lengkap 46 kolom</span>
                                </div>
                            </a>

                            <!-- Unduh PDF -->
                            <a href="{{ route('students.export.pdf', request()->all()) }}" @click="exportDropdownOpen = false"
                                class="flex items-center gap-2.5 px-3 py-2 text-xs text-slate-700 dark:text-slate-300 hover:bg-rose-50 dark:hover:bg-rose-950/40 hover:text-rose-700 dark:hover:text-rose-400 transition-colors group">
                                <div class="w-7 h-7 flex items-center justify-center rounded-lg bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 border border-rose-200/60 dark:border-rose-800/60 shrink-0">
                                    <i data-lucide="file-text" class="w-4 h-4 shrink-0"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <span class="font-semibold block text-slate-800 dark:text-slate-200 group-hover:text-rose-600 dark:group-hover:text-rose-400 truncate">Unduh Dokumen PDF</span>
                                    <span class="text-[10px] text-slate-400 block truncate">Format landscape siap cetak</span>
                                </div>
                            </a>
                        </div>

                        <div class="py-1">
                            <!-- Cetak / Print Browser -->
                            <a href="{{ route('students.print', request()->all()) }}" target="_blank" @click="exportDropdownOpen = false"
                                class="flex items-center gap-2.5 px-3 py-2 text-xs text-slate-700 dark:text-slate-300 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 hover:text-indigo-700 dark:hover:text-indigo-400 transition-colors group">
                                <div class="w-7 h-7 flex items-center justify-center rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-200/60 dark:border-indigo-800/60 shrink-0">
                                    <i data-lucide="printer" class="w-4 h-4 shrink-0"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <span class="font-semibold block text-slate-800 dark:text-slate-200 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 truncate">Cetak / Print Dialog</span>
                                    <span class="text-[10px] text-slate-400 block truncate">Preview kop surat & print browser</span>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>

                <button type="button" @click="openCreateModal()"
                    class="inline-flex items-center justify-center gap-1.5 px-3.5 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg shadow-sm transition-colors cursor-pointer shrink-0">
                    <i data-lucide="plus" class="w-3.5 h-3.5 shrink-0"></i>
                    <span>Tambah Siswa</span>
                </button>
            </div>
        </section>

        <!-- IMPORT ERRORS NOTIFICATION (IF ANY ROWS SKIPPED) -->
        @if(session('import_errors'))
            <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300 text-xs space-y-2 shadow-xs">
                <div class="flex items-center gap-2 font-bold">
                    <i data-lucide="alert-triangle" class="w-4 h-4 text-rose-600 dark:text-rose-400 shrink-0"></i>
                    <span>Catatan Impor (Beberapa baris dilewati):</span>
                </div>
                <ul class="list-disc list-inside space-y-1 pl-2 text-[11px] text-rose-700 dark:text-rose-300 max-h-40 overflow-y-auto">
                    @foreach(session('import_errors') as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- STATS CARDS GRID -->
        <section class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2.5 sm:gap-3 lg:gap-3.5">
            <!-- Stat Card 1: Total Siswa Aktif -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-3 sm:p-3.5 lg:p-4 shadow-xs flex flex-col justify-between">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Siswa Aktif</p>
                        <h3 class="text-lg sm:text-xl font-black tracking-tight text-slate-900 dark:text-slate-50 mt-1">
                            {{ number_format($stats['total_active']) }}
                        </h3>
                    </div>
                    <div class="w-8 h-8 flex items-center justify-center bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 rounded-lg border border-indigo-100 dark:border-indigo-900/50 shrink-0">
                        <i data-lucide="users" class="w-4 h-4 shrink-0"></i>
                    </div>
                </div>
                <div class="mt-2 text-[10px] text-slate-400">
                    Total terdata: <span class="font-semibold text-slate-600 dark:text-slate-300">{{ number_format($stats['total_all']) }}</span>
                </div>
            </div>

            <!-- Stat Card 2: Laki-laki -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-3 sm:p-3.5 lg:p-4 shadow-xs flex flex-col justify-between">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Putra (L)</p>
                        <h3 class="text-lg sm:text-xl font-black tracking-tight text-slate-900 dark:text-slate-50 mt-1">
                            {{ number_format($stats['male']) }}
                        </h3>
                    </div>
                    <div class="w-8 h-8 flex items-center justify-center bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 rounded-lg border border-blue-100 dark:border-blue-900/50 shrink-0">
                        <i data-lucide="user" class="w-4 h-4 shrink-0"></i>
                    </div>
                </div>
                <div class="mt-2 text-[10px] text-slate-400">
                    {{ $stats['total_active'] > 0 ? round(($stats['male'] / $stats['total_active']) * 100) : 0 }}% dari total aktif
                </div>
            </div>

            <!-- Stat Card 3: Perempuan -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-3 sm:p-3.5 lg:p-4 shadow-xs flex flex-col justify-between">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Putri (P)</p>
                        <h3 class="text-lg sm:text-xl font-black tracking-tight text-slate-900 dark:text-slate-50 mt-1">
                            {{ number_format($stats['female']) }}
                        </h3>
                    </div>
                    <div class="w-8 h-8 flex items-center justify-center bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 rounded-lg border border-rose-100 dark:border-rose-900/50 shrink-0">
                        <i data-lucide="user-check" class="w-4 h-4 shrink-0"></i>
                    </div>
                </div>
                <div class="mt-2 text-[10px] text-slate-400">
                    {{ $stats['total_active'] > 0 ? round(($stats['female'] / $stats['total_active']) * 100) : 0 }}% dari total aktif
                </div>
            </div>

            <!-- Stat Card 4: Inklusi (PDBK) -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-3 sm:p-3.5 lg:p-4 shadow-xs flex flex-col justify-between">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-[10px] font-bold text-purple-600 dark:text-purple-400 uppercase tracking-wider">Inklusi (PDBK)</p>
                        <h3 class="text-lg sm:text-xl font-black tracking-tight text-purple-700 dark:text-purple-300 mt-1">
                            {{ number_format($stats['pdbk']) }}
                        </h3>
                    </div>
                    <div class="w-8 h-8 flex items-center justify-center bg-purple-50 dark:bg-purple-950/40 text-purple-600 dark:text-purple-400 rounded-lg border border-purple-100 dark:border-purple-900/50 shrink-0">
                        <i data-lucide="heart-handshake" class="w-4 h-4 shrink-0"></i>
                    </div>
                </div>
                <div class="mt-2 text-[10px] text-purple-600/80 dark:text-purple-400/80">
                    Berkebutuhan khusus
                </div>
            </div>

            <!-- Stat Card 5: Rombongan Belajar -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-3 sm:p-3.5 lg:p-4 shadow-xs flex flex-col justify-between col-span-2 sm:col-span-1">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Rombel</p>
                        <h3 class="text-lg sm:text-xl font-black tracking-tight text-slate-900 dark:text-slate-50 mt-1">
                            {{ number_format($stats['classrooms']) }}
                        </h3>
                    </div>
                    <div class="w-8 h-8 flex items-center justify-center bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 rounded-lg border border-emerald-100 dark:border-emerald-900/50 shrink-0">
                        <i data-lucide="layout-grid" class="w-4 h-4 shrink-0"></i>
                    </div>
                </div>
                <div class="mt-2 text-[10px] text-slate-400">
                    {{ $stats['classrooms'] }} Rombel (Kelas 7 - 9)
                </div>
            </div>
        </section>

        <!-- SEARCH & FILTERS -->
        <section class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-3 sm:p-3.5 shadow-xs w-full">
            <form method="GET" action="{{ route('students.index') }}" class="flex flex-col lg:flex-row gap-2.5 items-stretch lg:items-center justify-between">
                <!-- Search Box -->
                <div class="relative w-full lg:max-w-xs">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 dark:text-slate-500"></i>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, NIS, NIK, No. Ortu..."
                        style="padding-left: 2.25rem;"
                        class="w-full h-8.5 pr-3 text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-slate-900 dark:text-slate-50 placeholder-slate-400 shadow-inner">
                </div>

                <!-- Filter Select Toolbar -->
                <div class="flex flex-wrap items-center gap-2 w-full lg:w-auto">
                    <!-- Filter Tahun Pelajaran (Tapel) -->
                    <select name="academic_year_id" onchange="this.form.submit()"
                        class="h-8.5 px-2.5 text-xs font-semibold bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 cursor-pointer shadow-xs">
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}" {{ ($selectedYearName ?? '') == $year->name || $selectedYearId == $year->id ? 'selected' : '' }}>
                                Tapel {{ $year->name }} {{ $year->has_active || $year->is_active ? '★' : '' }}
                            </option>
                        @endforeach
                    </select>

                    <!-- Filter Tingkat Kelas -->
                    <select name="class_level_id" onchange="this.form.submit()"
                        class="h-8.5 px-2.5 text-xs font-medium bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 cursor-pointer shadow-xs">
                        <option value="all">Semua Tingkat</option>
                        @foreach($classLevels as $lvl)
                            <option value="{{ $lvl->id }}" {{ request('class_level_id') == $lvl->id ? 'selected' : '' }}>
                                {{ $lvl->name }}
                            </option>
                        @endforeach
                    </select>

                    <!-- Filter Rombel -->
                    <select name="classroom_id" onchange="this.form.submit()"
                        class="h-8.5 px-2.5 text-xs font-medium bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 cursor-pointer shadow-xs max-w-[150px] truncate">
                        <option value="all">Semua Rombel</option>
                        @foreach($classrooms as $rombel)
                            <option value="{{ $rombel->id }}" {{ request('classroom_id') == $rombel->id ? 'selected' : '' }}>
                                {{ $rombel->full_name }}
                            </option>
                        @endforeach
                    </select>

                    <!-- Filter Tipe Siswa (Reguler / Inklusi) -->
                    <select name="student_type" onchange="this.form.submit()"
                        class="h-8.5 px-2.5 text-xs font-medium bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 cursor-pointer shadow-xs">
                        <option value="">Semua Tipe</option>
                        <option value="REGULER" {{ request('student_type') == 'REGULER' ? 'selected' : '' }}>Reguler</option>
                        <option value="PDBK" {{ request('student_type') == 'PDBK' ? 'selected' : '' }}>PDBK (Inklusi)</option>
                    </select>

                    <!-- Filter Status -->
                    <select name="status" onchange="this.form.submit()"
                        class="h-8.5 px-2.5 text-xs font-medium bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 cursor-pointer shadow-xs">
                        <option value="all">Semua Status</option>
                        <option value="aktif" {{ request('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="lulus" {{ request('status') == 'lulus' ? 'selected' : '' }}>Lulus</option>
                        <option value="mutasi" {{ request('status') == 'mutasi' ? 'selected' : '' }}>Mutasi</option>
                        <option value="keluar" {{ request('status') == 'keluar' ? 'selected' : '' }}>Keluar</option>
                        <option value="nonaktif" {{ request('status') == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                    </select>

                    <!-- Filter Jumlah Baris (Per Page) -->
                    <select name="per_page" onchange="this.form.submit()"
                        class="h-8.5 px-2.5 text-xs font-semibold bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 cursor-pointer shadow-xs"
                        title="Tampilkan jumlah baris per halaman">
                        <option value="15" {{ request('per_page', 15) == 15 ? 'selected' : '' }}>15 baris</option>
                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25 baris</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 baris</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100 baris</option>
                        <option value="all" {{ request('per_page') === 'all' ? 'selected' : '' }}>Semua Baris</option>
                    </select>

                    @if(request()->hasAny(['search', 'academic_year_id', 'class_level_id', 'classroom_id', 'student_type', 'status', 'gender']) || (request('per_page') && request('per_page') != 15))
                        <a href="{{ route('students.index') }}" 
                            class="h-8.5 px-2.5 inline-flex items-center justify-center text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 rounded-lg border border-slate-200 dark:border-slate-700 transition-colors"
                            title="Reset Filter">
                            <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                        </a>
                    @endif
                </div>
            </form>
        </section>

        <!-- TABLE LIST SISWA -->
        <section class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xs overflow-hidden w-full">
            <div class="overflow-x-auto">
                <table class="w-full text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/75 dark:bg-slate-900/50">
                            <th class="px-3 py-2.5 sm:px-4 sm:py-3 text-left text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-12">No</th>
                            <th class="px-3 py-2.5 sm:px-4 sm:py-3 text-left text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-28">NIS</th>
                            <th class="px-3 py-2.5 sm:px-4 sm:py-3 text-left text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Nama Siswa & Tipe</th>
                            <th class="px-3 py-2.5 sm:px-4 sm:py-3 text-left text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider min-w-[200px] sm:min-w-[220px]">Tingkat & Rombel</th>
                            <th class="px-3 py-2.5 sm:px-4 sm:py-3 text-left text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-24 sm:w-28">L/P & Usia</th>
                            <th class="px-3 py-2.5 sm:px-4 sm:py-3 text-left text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider min-w-[160px]">Orang Tua & WA</th>
                            <th class="px-3 py-2.5 sm:px-4 sm:py-3 text-left text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-24">Status</th>
                            <th class="px-3 py-2.5 sm:px-4 sm:py-3 text-right text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-24 sm:w-28">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                        @forelse($students as $index => $s)
                            @php
                                $isPdbk = ($s->student_type && (str_contains(strtoupper($s->student_type), 'PDBK') || str_contains(strtoupper($s->student_type), 'KHUSUS'))) || !empty($s->special_needs_type);
                            @endphp
                            <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/30 transition-colors group">
                                <td class="px-3 py-2.5 sm:px-4 sm:py-3 text-slate-400 font-mono text-[11px]">
                                    {{ $students->firstItem() + $index }}
                                </td>
                                <td class="px-3 py-2.5 sm:px-4 sm:py-3 font-mono text-xs font-bold text-indigo-600 dark:text-indigo-400 whitespace-nowrap">
                                    {{ $s->nis }}
                                </td>
                                <td class="px-3 py-2.5 sm:px-4 sm:py-3">
                                    <div class="flex items-center gap-2.5 sm:gap-3">
                                        @if($s->student_photo_url)
                                            <img src="{{ $s->student_photo_url }}" width="32" height="32" alt="{{ $s->full_name }}" class="w-7 h-7 sm:w-8 sm:h-8 rounded-full object-cover ring-1 ring-slate-200 dark:ring-slate-700 shrink-0 aspect-square">
                                        @else
                                            <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-indigo-50 dark:bg-indigo-950/50 border border-indigo-100 dark:border-indigo-900/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-xs shrink-0 aspect-square">
                                                {{ $s->avatar_initials }}
                                            </div>
                                        @endif
                                        <div class="flex flex-col">
                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                <span class="font-bold text-slate-900 dark:text-slate-100 text-xs tracking-tight hover:text-indigo-600 dark:hover:text-indigo-400 cursor-pointer" @click="openDetailModal({{ $s->id }})">
                                                    {{ $s->full_name }}
                                                </span>
                                                @if($isPdbk)
                                                    <span class="px-1.5 py-0.2 rounded text-[10px] font-bold bg-purple-100 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300 border border-purple-200 dark:border-purple-800 shrink-0" title="{{ $s->special_needs_type ?: 'PDBK' }}">
                                                        PDBK
                                                    </span>
                                                    @if($s->gpkTeacher)
                                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.2 rounded text-[10px] font-medium bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300 border border-amber-200/80 dark:border-amber-800/60 shrink-0" title="Guru Pendamping Khusus: {{ $s->gpkTeacher->full_name }}">
                                                            <i data-lucide="user-check" class="w-2.5 h-2.5 shrink-0"></i>
                                                            <span>GPK: {{ Str::limit($s->gpkTeacher->name, 14) }}</span>
                                                        </span>
                                                    @endif
                                                @endif
                                            </div>
                                            <div class="flex items-center gap-2 mt-0.5 text-[10px] text-slate-400">
                                                @if($s->nik)
                                                    <span>NIK: {{ $s->nik }}</span>
                                                @endif
                                                @if($s->spmb_candidate_id)
                                                    <span class="px-1.5 py-0.2 rounded bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 font-medium">SPMB</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-3 py-2.5 sm:px-4 sm:py-3 min-w-[200px] sm:min-w-[220px]">
                                    <div class="flex flex-col">
                                        <span class="font-semibold text-slate-800 dark:text-slate-200 whitespace-nowrap">
                                            {{ $s->classroom ? $s->classroom->full_name : 'Belum Ditentukan' }}
                                        </span>
                                        <div class="flex items-center gap-1.5 mt-0.5 whitespace-nowrap">
                                            <span class="text-[11px] text-slate-400">
                                                {{ $s->classroom && $s->classroom->classLevel ? $s->classroom->classLevel->name : '-' }}
                                            </span>
                                            @if($s->academicYear)
                                                <span class="text-[10px] px-1.5 py-0.2 rounded bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 font-medium font-mono border border-indigo-100 dark:border-indigo-900/50 shrink-0">
                                                    {{ $s->academicYear->name }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-3 py-2.5 sm:px-4 sm:py-3">
                                    <div class="flex flex-col text-slate-600 dark:text-slate-300">
                                        <span class="font-medium">{{ $s->formatted_gender }}</span>
                                        <span class="text-[10px] text-slate-400">{{ $s->age ?: '-' }}</span>
                                    </div>
                                </td>
                                <td class="px-3 py-2.5 sm:px-4 sm:py-3">
                                    <div class="flex flex-col">
                                        <span class="font-medium text-slate-800 dark:text-slate-200 truncate max-w-[160px]">
                                            {{ $s->father_name ?: ($s->mother_name ?: ($s->guardian_name ?: '-')) }}
                                        </span>
                                        @if($s->clean_parent_phone)
                                            <a href="{{ $s->whatsapp_url }}" target="_blank" class="inline-flex items-center gap-1 text-[11px] text-emerald-600 dark:text-emerald-400 hover:underline font-mono mt-0.5">
                                                <i data-lucide="message-circle" class="w-3 h-3 shrink-0"></i>
                                                {{ $s->parent_phone }}
                                            </a>
                                        @else
                                            <span class="text-[11px] text-slate-400">-</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-3 py-2.5 sm:px-4 sm:py-3">
                                    @if($s->status === 'aktif')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/40">
                                            Aktif
                                        </span>
                                    @elseif($s->status === 'lulus')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-400 border border-blue-200 dark:border-blue-800/40">
                                            Lulus
                                        </span>
                                    @elseif($s->status === 'mutasi')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800/40">
                                            Mutasi
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                                            {{ ucfirst($s->status) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-3 py-2.5 sm:px-4 sm:py-3 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <button type="button" @click="openDetailModal({{ $s->id }})"
                                            class="w-7 h-7 inline-flex items-center justify-center hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-500 hover:text-indigo-600 dark:hover:text-indigo-400 rounded-lg transition-colors cursor-pointer shrink-0"
                                            title="Lihat Detail Profil & Riwayat">
                                            <i data-lucide="eye" class="w-4 h-4 shrink-0"></i>
                                        </button>
                                        <button type="button" @click="openEditModal({{ $s->id }})"
                                            class="w-7 h-7 inline-flex items-center justify-center hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-500 hover:text-amber-600 dark:hover:text-amber-400 rounded-lg transition-colors cursor-pointer shrink-0"
                                            title="Edit 7 Kategori Data">
                                            <i data-lucide="edit-2" class="w-4 h-4 shrink-0"></i>
                                        </button>
                                        <button type="button" @click="confirmDeleteStudent({{ $s->id }}, '{{ addslashes($s->full_name) }}')"
                                            class="w-7 h-7 inline-flex items-center justify-center hover:bg-rose-50 dark:hover:bg-rose-950/40 text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 rounded-lg transition-colors cursor-pointer shrink-0"
                                            title="Hapus Data Siswa">
                                            <i data-lucide="trash-2" class="w-4 h-4 shrink-0"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center text-slate-400">
                                    <div class="flex flex-col items-center justify-center">
                                        <i data-lucide="users" class="w-8 h-8 text-slate-300 dark:text-slate-600 mb-2"></i>
                                        <p class="font-semibold text-slate-600 dark:text-slate-400">Tidak ada data siswa ditemukan</p>
                                        <p class="text-[11px] text-slate-400 mt-0.5">Coba sesuaikan filter atau tambahkan siswa baru / impor dari Excel.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500 dark:text-slate-400">
                <div>
                    Menampilkan <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $students->firstItem() ?? 0 }}</span> - <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $students->lastItem() ?? 0 }}</span> dari <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $students->total() }}</span> siswa
                    @if(request('per_page') === 'all')
                        <span class="ml-1 text-indigo-600 dark:text-indigo-400 font-medium">(Semua ditampilkan)</span>
                    @endif
                </div>
                @if($students->hasPages())
                    <div>
                        {{ $students->links() }}
                    </div>
                @endif
            </div>
        </section>

        <!-- ========================================================= -->
        <!-- MODAL DETAIL SISWA (7-TAB SYSTEM) -->
        <!-- ========================================================= -->
        <div x-show="detailModalOpen" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center p-3 sm:p-4" style="display: none; margin-top: 0px !important; z-index: 9999; background-color: rgba(15, 23, 42, 0.65); backdrop-filter: blur(4px);">
            <div @click.outside="detailModalOpen = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-5xl max-h-[90vh] overflow-hidden shadow-2xl flex flex-col">
                
                <!-- Modal Top Header -->
                <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between bg-slate-50/80 dark:bg-slate-900/80 shrink-0">
                    <div class="flex items-center gap-3.5 min-w-0">
                        <template x-if="selectedStudent?.student_photo_url">
                            <img :src="selectedStudent.student_photo_url" class="w-13 h-13 rounded-2xl object-cover ring-2 ring-indigo-500/30 shrink-0">
                        </template>
                        <template x-if="!selectedStudent?.student_photo_url">
                            <div class="w-13 h-13 rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-700 text-white font-black text-xl flex items-center justify-center shadow-sm shrink-0 ring-2 ring-indigo-500/20" x-text="selectedStudent?.avatar_initials || 'S'"></div>
                        </template>
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="text-base font-black text-slate-900 dark:text-slate-50 truncate" x-text="selectedStudent?.full_name || 'Detail Siswa'"></h3>
                                <template x-if="selectedStudent?.nickname">
                                    <span class="text-xs font-normal text-slate-500 dark:text-slate-400" x-text="'(' + selectedStudent.nickname + ')'"></span>
                                </template>
                                
                                <!-- Status Badge -->
                                <template x-if="selectedStudent?.status === 'aktif'">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">Aktif</span>
                                </template>
                                <template x-if="selectedStudent?.status === 'lulus'">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300 border border-blue-200 dark:border-blue-800">Lulus</span>
                                </template>
                                <template x-if="selectedStudent?.status === 'mutasi'">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-200 dark:border-amber-800">Mutasi</span>
                                </template>
                                <template x-if="selectedStudent?.status === 'keluar'">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-200 dark:border-rose-800">Keluar</span>
                                </template>
                                <template x-if="selectedStudent?.status === 'nonaktif'">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-300 border border-slate-200 dark:border-slate-700">Nonaktif</span>
                                </template>

                                <!-- PDBK Badge -->
                                <template x-if="selectedStudent?.student_type && selectedStudent.student_type.includes('PDBK')">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300 border border-purple-200 dark:border-purple-800 flex items-center gap-1">
                                        <i data-lucide="heart-handshake" class="w-3 h-3"></i> PDBK
                                    </span>
                                </template>

                                <!-- SPMB Badge -->
                                <template x-if="selectedStudent?.spmb_candidate">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 flex items-center gap-1">
                                        <i data-lucide="sparkles" class="w-3 h-3 text-indigo-500"></i>
                                        <span>SPMB: <span x-text="selectedStudent.spmb_candidate.registration_number || 'Terdaftar'"></span></span>
                                    </span>
                                </template>
                            </div>
                            <div class="flex flex-wrap items-center gap-x-2.5 gap-y-0.5 text-xs text-slate-500 dark:text-slate-400 mt-1">
                                <span>NIS: <strong class="font-mono font-bold text-indigo-600 dark:text-indigo-400" x-text="selectedStudent?.nis || '-'"></strong></span>
                                <template x-if="selectedStudent?.nisn">
                                    <span>&bull; NISN: <span class="font-mono text-slate-700 dark:text-slate-300" x-text="selectedStudent.nisn"></span></span>
                                </template>
                                <span>&bull;</span>
                                <span class="font-semibold text-slate-700 dark:text-slate-300" x-text="selectedStudent?.classroom?.name || 'Tanpa Rombel'"></span>
                                <span>&bull;</span>
                                <span x-text="selectedStudent?.academic_year?.name ? 'Tapel ' + selectedStudent.academic_year.name : '-'"></span>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <template x-if="selectedStudent?.whatsapp_url">
                            <a :href="selectedStudent.whatsapp_url" target="_blank" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-semibold text-xs flex items-center gap-1.5 shadow-xs transition-colors">
                                <i data-lucide="message-circle" class="w-3.5 h-3.5"></i>
                                <span class="hidden sm:inline">WA Ortu</span>
                            </a>
                        </template>
                        <button type="button" @click="detailModalOpen = false" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>
                </div>

                <!-- 7-Tab Navigation Bar -->
                <div class="flex items-center gap-1 px-4 border-b border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 overflow-x-auto text-xs font-semibold scrollbar-thin shrink-0">
                    <button type="button" @click="activeDetailTab = 1" :class="activeDetailTab === 1 ? 'text-indigo-600 border-indigo-600 dark:text-indigo-400 dark:border-indigo-400 bg-indigo-50/30 dark:bg-indigo-950/20' : 'text-slate-500 border-transparent hover:text-slate-700 dark:hover:text-slate-300'" class="px-3.5 py-3 border-b-2 flex items-center gap-1.5 whitespace-nowrap transition-colors cursor-pointer">
                        <i data-lucide="id-card" class="w-4 h-4"></i> 1. Identitas & Legalitas
                    </button>
                    <button type="button" @click="activeDetailTab = 2" :class="activeDetailTab === 2 ? 'text-indigo-600 border-indigo-600 dark:text-indigo-400 dark:border-indigo-400 bg-indigo-50/30 dark:bg-indigo-950/20' : 'text-slate-500 border-transparent hover:text-slate-700 dark:hover:text-slate-300'" class="px-3.5 py-3 border-b-2 flex items-center gap-1.5 whitespace-nowrap transition-colors cursor-pointer">
                        <i data-lucide="heart-handshake" class="w-4 h-4"></i> 2. Inklusi / PDBK
                    </button>
                    <button type="button" @click="activeDetailTab = 3" :class="activeDetailTab === 3 ? 'text-indigo-600 border-indigo-600 dark:text-indigo-400 dark:border-indigo-400 bg-indigo-50/30 dark:bg-indigo-950/20' : 'text-slate-500 border-transparent hover:text-slate-700 dark:hover:text-slate-300'" class="px-3.5 py-3 border-b-2 flex items-center gap-1.5 whitespace-nowrap transition-colors cursor-pointer">
                        <i data-lucide="map-pin" class="w-4 h-4"></i> 3. Alamat & Domisili
                    </button>
                    <button type="button" @click="activeDetailTab = 4" :class="activeDetailTab === 4 ? 'text-indigo-600 border-indigo-600 dark:text-indigo-400 dark:border-indigo-400 bg-indigo-50/30 dark:bg-indigo-950/20' : 'text-slate-500 border-transparent hover:text-slate-700 dark:hover:text-slate-300'" class="px-3.5 py-3 border-b-2 flex items-center gap-1.5 whitespace-nowrap transition-colors cursor-pointer">
                        <i data-lucide="users-round" class="w-4 h-4"></i> 4. Orang Tua & Wali
                    </button>
                    <button type="button" @click="activeDetailTab = 5" :class="activeDetailTab === 5 ? 'text-indigo-600 border-indigo-600 dark:text-indigo-400 dark:border-indigo-400 bg-indigo-50/30 dark:bg-indigo-950/20' : 'text-slate-500 border-transparent hover:text-slate-700 dark:hover:text-slate-300'" class="px-3.5 py-3 border-b-2 flex items-center gap-1.5 whitespace-nowrap transition-colors cursor-pointer">
                        <i data-lucide="git-fork" class="w-4 h-4"></i> 5. Keluarga & Saudara
                    </button>
                    <button type="button" @click="activeDetailTab = 6" :class="activeDetailTab === 6 ? 'text-indigo-600 border-indigo-600 dark:text-indigo-400 dark:border-indigo-400 bg-indigo-50/30 dark:bg-indigo-950/20' : 'text-slate-500 border-transparent hover:text-slate-700 dark:hover:text-slate-300'" class="px-3.5 py-3 border-b-2 flex items-center gap-1.5 whitespace-nowrap transition-colors cursor-pointer">
                        <i data-lucide="activity" class="w-4 h-4"></i> 6. Kesehatan & UKS
                    </button>
                    <button type="button" @click="activeDetailTab = 7" :class="activeDetailTab === 7 ? 'text-indigo-600 border-indigo-600 dark:text-indigo-400 dark:border-indigo-400 bg-indigo-50/30 dark:bg-indigo-950/20' : 'text-slate-500 border-transparent hover:text-slate-700 dark:hover:text-slate-300'" class="px-3.5 py-3 border-b-2 flex items-center gap-1.5 whitespace-nowrap transition-colors cursor-pointer">
                        <i data-lucide="history" class="w-4 h-4"></i> 7. Asal & Riwayat Kelas
                    </button>
                </div>

                <!-- Tab Body Content -->
                <div class="p-5 sm:p-6 overflow-y-auto flex-1 min-h-0 space-y-4 text-xs">
                    
                    <!-- TAB 1: IDENTITAS & LEGALITAS -->
                    <div x-show="activeDetailTab === 1" x-cloak class="space-y-4">
                        <!-- Group 1: Profil Pokok Siswa -->
                        <div>
                            <div class="flex items-center gap-2 mb-2 text-slate-800 dark:text-slate-200 font-bold">
                                <i data-lucide="user-check" class="w-4 h-4 text-indigo-600 dark:text-indigo-400"></i>
                                <span>Identitas Pokok Siswa</span>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                                    <span class="text-slate-400 text-[10px] block font-bold uppercase">Nama Lengkap Siswa</span>
                                    <span class="font-bold text-slate-800 dark:text-slate-200 text-sm" x-text="selectedStudent?.full_name || '-'"></span>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                                    <span class="text-slate-400 text-[10px] block font-bold uppercase">Nama Panggilan</span>
                                    <span class="font-semibold text-slate-800 dark:text-slate-200" x-text="selectedStudent?.nickname || '-'"></span>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                                    <span class="text-slate-400 text-[10px] block font-bold uppercase">Jenis Kelamin & Usia</span>
                                    <span class="font-semibold text-slate-800 dark:text-slate-200" x-text="(selectedStudent?.formatted_gender || '-') + (selectedStudent?.age ? ' (' + selectedStudent.age + ')' : '')"></span>
                                </div>

                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                                    <span class="text-slate-400 text-[10px] block font-bold uppercase">NIS (Nomor Induk Siswa)</span>
                                    <span class="font-mono font-bold text-indigo-600 dark:text-indigo-400" x-text="selectedStudent?.nis || '-'"></span>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                                    <span class="text-slate-400 text-[10px] block font-bold uppercase">NISN (Nomor Induk Siswa Nasional)</span>
                                    <span class="font-mono font-semibold text-slate-800 dark:text-slate-200" x-text="selectedStudent?.nisn || '-'"></span>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                                    <span class="text-slate-400 text-[10px] block font-bold uppercase">NIK Siswa (No. KTP/KIA)</span>
                                    <span class="font-mono font-semibold text-slate-800 dark:text-slate-200" x-text="selectedStudent?.nik || '-'"></span>
                                </div>

                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                                    <span class="text-slate-400 text-[10px] block font-bold uppercase">Tempat, Tanggal Lahir</span>
                                    <span class="font-semibold text-slate-800 dark:text-slate-200" x-text="(selectedStudent?.birth_place ? selectedStudent.birth_place + ', ' : '') + (selectedStudent?.birth_date ? selectedStudent.birth_date.substring(0, 10) : '-')"></span>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                                    <span class="text-slate-400 text-[10px] block font-bold uppercase">Agama</span>
                                    <span class="font-semibold text-slate-800 dark:text-slate-200" x-text="selectedStudent?.religion || 'Islam'"></span>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                                    <span class="text-slate-400 text-[10px] block font-bold uppercase">Kewarganegaraan & Suku</span>
                                    <span class="font-semibold text-slate-800 dark:text-slate-200" x-text="(selectedStudent?.citizenship || 'WNI') + (selectedStudent?.ethnic_group ? ' &bull; Suku ' + selectedStudent.ethnic_group : '')"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Group 2: Dokumen Legalitas & Status Registrasi -->
                        <div>
                            <div class="flex items-center gap-2 mb-2 text-slate-800 dark:text-slate-200 font-bold">
                                <i data-lucide="file-text" class="w-4 h-4 text-indigo-600 dark:text-indigo-400"></i>
                                <span>Legalitas Dokumen & Status Registrasi</span>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                                    <span class="text-slate-400 text-[10px] block font-bold uppercase">No. Kartu Keluarga (KK)</span>
                                    <span class="font-mono font-semibold text-slate-800 dark:text-slate-200" x-text="selectedStudent?.no_kk || '-'"></span>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                                    <span class="text-slate-400 text-[10px] block font-bold uppercase">No. Registrasi Akta Kelahiran</span>
                                    <span class="font-mono font-semibold text-slate-800 dark:text-slate-200" x-text="selectedStudent?.birth_certificate_no || '-'"></span>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                                    <span class="text-slate-400 text-[10px] block font-bold uppercase">Status Kesiswaan</span>
                                    <span class="font-bold capitalize" :class="selectedStudent?.status === 'aktif' ? 'text-emerald-600 dark:text-emerald-400' : (selectedStudent?.status === 'lulus' ? 'text-blue-600 dark:text-blue-400' : 'text-amber-600 dark:text-amber-400')" x-text="selectedStudent?.status || 'Aktif'"></span>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                                    <span class="text-slate-400 text-[10px] block font-bold uppercase">Tanggal Terdaftar / Masuk</span>
                                    <span class="font-semibold text-slate-800 dark:text-slate-200" x-text="(selectedStudent?.enrolled_date || selectedStudent?.enrollment_date) ? (selectedStudent.enrolled_date || selectedStudent.enrollment_date).substring(0, 10) : '-'"></span>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                                    <span class="text-slate-400 text-[10px] block font-bold uppercase">Jalur Masuk / Pendaftaran</span>
                                    <span class="font-semibold text-slate-800 dark:text-slate-200" x-text="selectedStudent?.enrollment_type || 'Pendaftaran Reguler / Dapodik'"></span>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                                    <span class="text-slate-400 text-[10px] block font-bold uppercase">Tahun Pelajaran Masuk</span>
                                    <span class="font-semibold text-slate-800 dark:text-slate-200" x-text="selectedStudent?.academic_year?.name ? 'Tapel ' + selectedStudent.academic_year.name : '-'"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Group 3: SPMB Origin Details (If Candidate Linked) -->
                        <template x-if="selectedStudent?.spmb_candidate">
                            <div class="p-4 rounded-xl border border-indigo-200 dark:border-indigo-800/60 bg-indigo-50/30 dark:bg-indigo-950/20 space-y-2">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <i data-lucide="sparkles" class="w-4 h-4 text-indigo-600 dark:text-indigo-400"></i>
                                        <span class="font-bold text-indigo-900 dark:text-indigo-200 text-xs">Riwayat Pendaftaran SPMB Online</span>
                                    </div>
                                    <span class="text-[10px] font-mono font-bold px-2 py-0.5 rounded bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300" x-text="'No. Reg: ' + (selectedStudent.spmb_candidate.registration_number || '-')"></span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs pt-1">
                                    <div>
                                        <span class="text-slate-400 text-[10px] block font-bold uppercase">Gelombang / Jalur</span>
                                        <span class="font-medium text-slate-800 dark:text-slate-200" x-text="selectedStudent.spmb_candidate.phase ? 'Gelombang ' + selectedStudent.spmb_candidate.phase : (selectedStudent.spmb_candidate.category || 'Jalur Reguler')"></span>
                                    </div>
                                    <div>
                                        <span class="text-slate-400 text-[10px] block font-bold uppercase">Status Hasil Tes</span>
                                        <span class="font-bold text-emerald-600 dark:text-emerald-400 capitalize" x-text="selectedStudent.spmb_candidate.status || 'Diterima'"></span>
                                    </div>
                                    <div>
                                        <span class="text-slate-400 text-[10px] block font-bold uppercase">SD Asal SPMB</span>
                                        <span class="font-medium text-slate-800 dark:text-slate-200" x-text="selectedStudent.spmb_candidate.previous_school || '-'"></span>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- TAB 2: INKLUSI & KEKHUSUSAN (PDBK) -->
                    <div x-show="activeDetailTab === 2" x-cloak style="display: none;" class="space-y-4">
                        <div class="p-4 rounded-xl border border-purple-200 dark:border-purple-800/60 bg-purple-50/40 dark:bg-purple-950/20 space-y-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div class="p-2 bg-purple-600 text-white rounded-lg">
                                        <i data-lucide="heart-handshake" class="w-4 h-4"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-purple-900 dark:text-purple-200 text-xs">Klasifikasi Peserta Didik & Program Layanan</h4>
                                        <p class="text-[11px] text-purple-700 dark:text-purple-300">Status program inklusi & kebutuhan pendampingan khusus.</p>
                                    </div>
                                </div>
                                <span class="px-3 py-1 rounded-full text-xs font-bold bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200 border border-purple-300 dark:border-purple-700" x-text="selectedStudent?.student_type || 'REGULER'"></span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                                <div class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-purple-100 dark:border-purple-900/40">
                                    <span class="text-slate-400 text-[10px] block font-bold uppercase">Jenis Ketunaan / Hambatan / Kekhususan</span>
                                    <span class="font-bold text-slate-800 dark:text-slate-200 text-xs" x-text="selectedStudent?.special_needs_type || 'Tidak Ada / Siswa Reguler'"></span>
                                </div>
                                <div class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-purple-100 dark:border-purple-900/40">
                                    <span class="text-slate-400 text-[10px] block font-bold uppercase">Guru Pendamping Khusus (GPK / Shadow Teacher)</span>
                                    <template x-if="selectedStudent?.gpk_teacher">
                                        <div class="flex items-center gap-2 mt-1">
                                            <div class="w-7 h-7 rounded-full bg-purple-100 text-purple-700 dark:bg-purple-900/60 dark:text-purple-300 flex items-center justify-center font-bold text-xs shrink-0">
                                                <i data-lucide="user-check" class="w-3.5 h-3.5"></i>
                                            </div>
                                            <div class="min-w-0">
                                                <span class="font-bold text-slate-800 dark:text-slate-200 text-xs block truncate" x-text="selectedStudent.gpk_teacher.full_name || selectedStudent.gpk_teacher.name"></span>
                                                <span class="text-[10px] text-slate-400 block" x-text="selectedStudent.gpk_teacher.phone_number ? 'No. HP: ' + selectedStudent.gpk_teacher.phone_number : 'NIP: ' + (selectedStudent.gpk_teacher.nip || '-')"></span>
                                            </div>
                                        </div>
                                    </template>
                                    <template x-if="!selectedStudent?.gpk_teacher">
                                        <span class="font-medium text-slate-400 dark:text-slate-500 text-xs italic block mt-1">Belum ditentukan / Siswa Reguler tanpa GPK</span>
                                    </template>
                                </div>
                                <div class="sm:col-span-2 p-3 rounded-xl bg-white dark:bg-slate-900 border border-purple-100 dark:border-purple-900/40">
                                    <span class="text-slate-400 text-[10px] block font-bold uppercase">Catatan Pendampingan Guru / Penanganan Khusus (PPI)</span>
                                    <span class="font-medium text-slate-700 dark:text-slate-300 text-xs leading-relaxed block mt-0.5" x-text="selectedStudent?.special_needs_notes || selectedStudent?.notes || 'Tidak ada catatan pendampingan khusus.'"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 3: ALAMAT & DOMISILI -->
                    <div x-show="activeDetailTab === 3" x-cloak style="display: none;" class="space-y-4">
                        <div>
                            <div class="flex items-center gap-2 mb-2 text-slate-800 dark:text-slate-200 font-bold">
                                <i data-lucide="home" class="w-4 h-4 text-indigo-600 dark:text-indigo-400"></i>
                                <span>Alamat Tempat Tinggal & Wilayah</span>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div class="sm:col-span-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                                    <span class="text-slate-400 text-[10px] block font-bold uppercase">Alamat Lengkap / Jalan / Dusun / Blok</span>
                                    <span class="font-medium text-slate-800 dark:text-slate-200" x-text="selectedStudent?.address || '-'"></span>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                                    <span class="text-slate-400 text-[10px] block font-bold uppercase">RT / RW</span>
                                    <span class="font-semibold text-slate-800 dark:text-slate-200" x-text="(selectedStudent?.rt ? 'RT ' + selectedStudent.rt : '-') + ' / ' + (selectedStudent?.rw ? 'RW ' + selectedStudent.rw : '-')"></span>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                                    <span class="text-slate-400 text-[10px] block font-bold uppercase">Kelurahan / Desa</span>
                                    <span class="font-semibold text-slate-800 dark:text-slate-200" x-text="selectedStudent?.village || '-'"></span>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                                    <span class="text-slate-400 text-[10px] block font-bold uppercase">Kecamatan</span>
                                    <span class="font-semibold text-slate-800 dark:text-slate-200" x-text="selectedStudent?.district || '-'"></span>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                                    <span class="text-slate-400 text-[10px] block font-bold uppercase">Kategori Wilayah (Zonasi)</span>
                                    <span class="font-semibold text-slate-800 dark:text-slate-200" x-text="selectedStudent?.district_category || 'Dalam Kota'"></span>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                                    <span class="text-slate-400 text-[10px] block font-bold uppercase">Kabupaten / Kota</span>
                                    <span class="font-semibold text-slate-800 dark:text-slate-200" x-text="selectedStudent?.city || 'Malang'"></span>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                                    <span class="text-slate-400 text-[10px] block font-bold uppercase">Provinsi & Kode Pos</span>
                                    <span class="font-semibold text-slate-800 dark:text-slate-200" x-text="(selectedStudent?.province || 'Jawa Timur') + (selectedStudent?.postal_code ? ' (' + selectedStudent.postal_code + ')' : '')"></span>
                                </div>
                            </div>
                        </div>

                        <div>
                            <div class="flex items-center gap-2 mb-2 text-slate-800 dark:text-slate-200 font-bold">
                                <i data-lucide="navigation" class="w-4 h-4 text-indigo-600 dark:text-indigo-400"></i>
                                <span>Aksesibilitas & Kontak Rumah</span>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                                    <span class="text-slate-400 text-[10px] block font-bold uppercase">Status Tempat Tinggal</span>
                                    <span class="font-semibold text-slate-800 dark:text-slate-200" x-text="selectedStudent?.residence_status || 'Bersama Orang Tua'"></span>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                                    <span class="text-slate-400 text-[10px] block font-bold uppercase">Jarak Tempuh ke Sekolah</span>
                                    <span class="font-semibold text-slate-800 dark:text-slate-200" x-text="selectedStudent?.distance_to_school || '-'"></span>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                                    <span class="text-slate-400 text-[10px] block font-bold uppercase">No. Telepon Rumah</span>
                                    <span class="font-mono font-semibold text-slate-800 dark:text-slate-200" x-text="selectedStudent?.home_phone || '-'"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 4: ORANG TUA & WALI -->
                    <div x-show="activeDetailTab === 4" x-cloak style="display: none;" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Data Ayah -->
                            <div class="p-4 rounded-xl bg-blue-50/40 dark:bg-blue-950/20 border border-blue-100 dark:border-blue-900/40 space-y-2.5">
                                <div class="flex items-center gap-2 border-b border-blue-100 dark:border-blue-900/40 pb-2">
                                    <div class="p-1.5 bg-blue-600 text-white rounded-lg"><i data-lucide="user" class="w-3.5 h-3.5"></i></div>
                                    <h4 class="font-bold text-blue-900 dark:text-blue-300 text-xs uppercase">Data Ayah Kandung</h4>
                                </div>
                                <div class="space-y-2 text-xs">
                                    <div class="grid grid-cols-2 gap-2">
                                        <div><span class="text-slate-400 text-[10px] block font-bold uppercase">Nama:</span> <strong class="text-slate-800 dark:text-slate-200" x-text="selectedStudent?.father_name || '-'"></strong></div>
                                        <div><span class="text-slate-400 text-[10px] block font-bold uppercase">NIK Ayah:</span> <span class="font-mono font-semibold text-slate-700 dark:text-slate-300" x-text="selectedStudent?.father_nik || '-'"></span></div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2">
                                        <div><span class="text-slate-400 text-[10px] block font-bold uppercase">Tempat, Tgl Lahir:</span> <span class="text-slate-700 dark:text-slate-300" x-text="(selectedStudent?.father_birth_place ? selectedStudent.father_birth_place + ', ' : '') + (selectedStudent?.father_birth_date ? selectedStudent.father_birth_date.substring(0, 10) : '-')"></span></div>
                                        <div><span class="text-slate-400 text-[10px] block font-bold uppercase">Agama:</span> <span class="text-slate-700 dark:text-slate-300" x-text="selectedStudent?.father_religion || 'Islam'"></span></div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <span class="text-slate-400 text-[10px] block font-bold uppercase">No. HP / WA:</span>
                                            <span class="font-mono font-bold text-emerald-600 dark:text-emerald-400" x-text="selectedStudent?.father_phone || '-'"></span>
                                        </div>
                                        <div>
                                            <span class="text-slate-400 text-[10px] block font-bold uppercase">Email:</span>
                                            <span class="text-slate-700 dark:text-slate-300" x-text="selectedStudent?.father_email || '-'"></span>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2">
                                        <div><span class="text-slate-400 text-[10px] block font-bold uppercase">Pendidikan:</span> <span class="text-slate-700 dark:text-slate-300" x-text="selectedStudent?.father_education || '-'"></span></div>
                                        <div><span class="text-slate-400 text-[10px] block font-bold uppercase">Pekerjaan:</span> <span class="text-slate-700 dark:text-slate-300" x-text="selectedStudent?.father_job || '-'"></span></div>
                                    </div>
                                    <div>
                                        <span class="text-slate-400 text-[10px] block font-bold uppercase">Instansi & Alamat Kantor:</span>
                                        <span class="text-slate-700 dark:text-slate-300" x-text="(selectedStudent?.father_company || '-') + (selectedStudent?.father_company_address ? ' (' + selectedStudent.father_company_address + ')' : '') + (selectedStudent?.father_company_phone ? ' &bull; Telp: ' + selectedStudent.father_company_phone : '')"></span>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2">
                                        <div><span class="text-slate-400 text-[10px] block font-bold uppercase">Penghasilan:</span> <span class="text-slate-700 dark:text-slate-300" x-text="selectedStudent?.father_income || '-'"></span></div>
                                        <div><span class="text-slate-400 text-[10px] block font-bold uppercase">Media Sosial:</span> <span class="text-slate-700 dark:text-slate-300" x-text="selectedStudent?.father_social_media || '-'"></span></div>
                                    </div>
                                    <div><span class="text-slate-400 text-[10px] block font-bold uppercase">Alamat Domisili Ayah:</span> <span class="text-slate-700 dark:text-slate-300" x-text="selectedStudent?.father_address || '-'"></span></div>
                                </div>
                            </div>

                            <!-- Data Ibu -->
                            <div class="p-4 rounded-xl bg-rose-50/40 dark:bg-rose-950/20 border border-rose-100 dark:border-rose-900/40 space-y-2.5">
                                <div class="flex items-center gap-2 border-b border-rose-100 dark:border-rose-900/40 pb-2">
                                    <div class="p-1.5 bg-rose-600 text-white rounded-lg"><i data-lucide="user-check" class="w-3.5 h-3.5"></i></div>
                                    <h4 class="font-bold text-rose-900 dark:text-rose-300 text-xs uppercase">Data Ibu Kandung</h4>
                                </div>
                                <div class="space-y-2 text-xs">
                                    <div class="grid grid-cols-2 gap-2">
                                        <div><span class="text-slate-400 text-[10px] block font-bold uppercase">Nama:</span> <strong class="text-slate-800 dark:text-slate-200" x-text="selectedStudent?.mother_name || '-'"></strong></div>
                                        <div><span class="text-slate-400 text-[10px] block font-bold uppercase">NIK Ibu:</span> <span class="font-mono font-semibold text-slate-700 dark:text-slate-300" x-text="selectedStudent?.mother_nik || '-'"></span></div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2">
                                        <div><span class="text-slate-400 text-[10px] block font-bold uppercase">Tempat, Tgl Lahir:</span> <span class="text-slate-700 dark:text-slate-300" x-text="(selectedStudent?.mother_birth_place ? selectedStudent.mother_birth_place + ', ' : '') + (selectedStudent?.mother_birth_date ? selectedStudent.mother_birth_date.substring(0, 10) : '-')"></span></div>
                                        <div><span class="text-slate-400 text-[10px] block font-bold uppercase">Agama:</span> <span class="text-slate-700 dark:text-slate-300" x-text="selectedStudent?.mother_religion || 'Islam'"></span></div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <span class="text-slate-400 text-[10px] block font-bold uppercase">No. HP / WA:</span>
                                            <span class="font-mono font-bold text-emerald-600 dark:text-emerald-400" x-text="selectedStudent?.mother_phone || '-'"></span>
                                        </div>
                                        <div>
                                            <span class="text-slate-400 text-[10px] block font-bold uppercase">Email:</span>
                                            <span class="text-slate-700 dark:text-slate-300" x-text="selectedStudent?.mother_email || '-'"></span>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2">
                                        <div><span class="text-slate-400 text-[10px] block font-bold uppercase">Pendidikan:</span> <span class="text-slate-700 dark:text-slate-300" x-text="selectedStudent?.mother_education || '-'"></span></div>
                                        <div><span class="text-slate-400 text-[10px] block font-bold uppercase">Pekerjaan:</span> <span class="text-slate-700 dark:text-slate-300" x-text="selectedStudent?.mother_job || '-'"></span></div>
                                    </div>
                                    <div>
                                        <span class="text-slate-400 text-[10px] block font-bold uppercase">Instansi & Alamat Kantor:</span>
                                        <span class="text-slate-700 dark:text-slate-300" x-text="(selectedStudent?.mother_company || '-') + (selectedStudent?.mother_company_address ? ' (' + selectedStudent.mother_company_address + ')' : '') + (selectedStudent?.mother_company_phone ? ' &bull; Telp: ' + selectedStudent.mother_company_phone : '')"></span>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2">
                                        <div><span class="text-slate-400 text-[10px] block font-bold uppercase">Penghasilan:</span> <span class="text-slate-700 dark:text-slate-300" x-text="selectedStudent?.mother_income || '-'"></span></div>
                                        <div><span class="text-slate-400 text-[10px] block font-bold uppercase">Media Sosial:</span> <span class="text-slate-700 dark:text-slate-300" x-text="selectedStudent?.mother_social_media || '-'"></span></div>
                                    </div>
                                    <div><span class="text-slate-400 text-[10px] block font-bold uppercase">Alamat Domisili Ibu:</span> <span class="text-slate-700 dark:text-slate-300" x-text="selectedStudent?.mother_address || '-'"></span></div>
                                </div>
                            </div>
                        </div>

                        <!-- Primary Contact Communication Box -->
                        <div class="p-3.5 rounded-xl bg-emerald-50/60 dark:bg-emerald-950/30 border border-emerald-100 dark:border-emerald-900/40 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                            <div class="flex items-center gap-2.5">
                                <div class="p-2 bg-emerald-600 text-white rounded-lg"><i data-lucide="phone-call" class="w-4 h-4"></i></div>
                                <div>
                                    <span class="text-[10px] font-bold uppercase text-emerald-900 dark:text-emerald-300 block">Kontak Utama Orang Tua (Notifikasi Sekolah)</span>
                                    <span class="font-mono font-bold text-sm text-emerald-700 dark:text-emerald-300" x-text="selectedStudent?.parent_phone || selectedStudent?.father_phone || selectedStudent?.mother_phone || '-'"></span>
                                    <template x-if="selectedStudent?.parent_email">
                                        <span class="text-xs text-emerald-800/80 dark:text-emerald-300/80 block" x-text="'Email: ' + selectedStudent.parent_email"></span>
                                    </template>
                                </div>
                            </div>
                            <template x-if="selectedStudent?.whatsapp_url">
                                <a :href="selectedStudent.whatsapp_url" target="_blank" class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-bold text-xs flex items-center gap-1.5 shadow-xs transition-colors">
                                    <i data-lucide="message-circle" class="w-3.5 h-3.5"></i> Hubungi WhatsApp
                                </a>
                            </template>
                        </div>

                        <!-- Data Wali (Optional / Card) -->
                        <template x-if="selectedStudent?.guardian_name">
                            <div class="p-4 rounded-xl bg-amber-50/40 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-900/40 space-y-2.5">
                                <div class="flex items-center gap-2 border-b border-amber-200/60 dark:border-amber-900/40 pb-2">
                                    <div class="p-1.5 bg-amber-600 text-white rounded-lg"><i data-lucide="shield" class="w-3.5 h-3.5"></i></div>
                                    <h4 class="font-bold text-amber-900 dark:text-amber-300 text-xs uppercase">Data Wali Murid</h4>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 text-xs">
                                    <div><span class="text-slate-400 text-[10px] block font-bold uppercase">Nama Wali:</span> <strong class="text-slate-800 dark:text-slate-200" x-text="selectedStudent?.guardian_name || '-'"></strong></div>
                                    <div><span class="text-slate-400 text-[10px] block font-bold uppercase">NIK & Hubungan:</span> <span class="text-slate-700 dark:text-slate-300" x-text="(selectedStudent?.guardian_nik || '-') + ' (' + (selectedStudent?.guardian_relation || 'Wali') + ')'"></span></div>
                                    <div><span class="text-slate-400 text-[10px] block font-bold uppercase">No. HP / WA:</span> <span class="font-mono font-bold text-emerald-600 dark:text-emerald-400" x-text="selectedStudent?.guardian_phone || '-'"></span></div>
                                    <div><span class="text-slate-400 text-[10px] block font-bold uppercase">Tempat, Tgl Lahir:</span> <span class="text-slate-700 dark:text-slate-300" x-text="(selectedStudent?.guardian_birth_place ? selectedStudent.guardian_birth_place + ', ' : '') + (selectedStudent?.guardian_birth_date ? selectedStudent.guardian_birth_date.substring(0, 10) : '-')"></span></div>
                                    <div><span class="text-slate-400 text-[10px] block font-bold uppercase">Pendidikan & Pekerjaan:</span> <span class="text-slate-700 dark:text-slate-300" x-text="(selectedStudent?.guardian_education || '-') + ' / ' + (selectedStudent?.guardian_job || '-')"></span></div>
                                    <div><span class="text-slate-400 text-[10px] block font-bold uppercase">Instansi & Penghasilan:</span> <span class="text-slate-700 dark:text-slate-300" x-text="(selectedStudent?.guardian_company || '-') + ' (' + (selectedStudent?.guardian_income || '-') + ')'"></span></div>
                                    <div class="sm:col-span-2"><span class="text-slate-400 text-[10px] block font-bold uppercase">Alamat Domisili Wali:</span> <span class="text-slate-700 dark:text-slate-300" x-text="selectedStudent?.guardian_address || '-'"></span></div>
                                    <div><span class="text-slate-400 text-[10px] block font-bold uppercase">Email / Medsos:</span> <span class="text-slate-700 dark:text-slate-300" x-text="(selectedStudent?.guardian_email || '-') + (selectedStudent?.guardian_social_media ? ' &bull; ' + selectedStudent.guardian_social_media : '')"></span></div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- TAB 5: KELUARGA & SAUDARA -->
                    <div x-show="activeDetailTab === 5" x-cloak style="display: none;" class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                            <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                                <span class="text-slate-400 text-[10px] block font-bold uppercase">Anak Ke-</span>
                                <span class="font-bold text-slate-800 dark:text-slate-200 text-base" x-text="selectedStudent?.child_number || '-'"></span>
                            </div>
                            <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                                <span class="text-slate-400 text-[10px] block font-bold uppercase">Jumlah Saudara Kandung</span>
                                <span class="font-semibold text-slate-800 dark:text-slate-200 text-sm" x-text="selectedStudent?.siblings_count !== null && selectedStudent?.siblings_count !== undefined ? selectedStudent?.siblings_count + ' orang' : '-'"></span>
                            </div>
                            <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                                <span class="text-slate-400 text-[10px] block font-bold uppercase">Saudara Tiri</span>
                                <span class="font-semibold text-slate-800 dark:text-slate-200 text-sm" x-text="(selectedStudent?.step_siblings_count || 0) + ' orang'"></span>
                            </div>
                            <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                                <span class="text-slate-400 text-[10px] block font-bold uppercase">Saudara Angkat</span>
                                <span class="font-semibold text-slate-800 dark:text-slate-200 text-sm" x-text="(selectedStudent?.adoptive_siblings_count || 0) + ' orang'"></span>
                            </div>
                            <div class="sm:col-span-2 p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                                <span class="text-slate-400 text-[10px] block font-bold uppercase">Bahasa Sehari-hari di Rumah</span>
                                <span class="font-semibold text-slate-800 dark:text-slate-200" x-text="selectedStudent?.home_language || 'Bahasa Indonesia'"></span>
                            </div>
                            <div class="sm:col-span-2 p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                                <span class="text-slate-400 text-[10px] block font-bold uppercase">Suku Bangsa Keluarga</span>
                                <span class="font-semibold text-slate-800 dark:text-slate-200" x-text="selectedStudent?.ethnic_group || '-'"></span>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 6: KESEHATAN & FISIK (UKS) -->
                    <div x-show="activeDetailTab === 6" x-cloak style="display: none;" class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                                <span class="text-slate-400 text-[10px] block font-bold uppercase">Golongan Darah</span>
                                <span class="font-black text-rose-600 dark:text-rose-400 text-base" x-text="selectedStudent?.blood_type || '-'"></span>
                            </div>
                            <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                                <span class="text-slate-400 text-[10px] block font-bold uppercase">Tinggi Badan (cm)</span>
                                <span class="font-semibold text-slate-800 dark:text-slate-200 text-sm" x-text="selectedStudent?.height ? selectedStudent.height + ' cm' : '-'"></span>
                            </div>
                            <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                                <span class="text-slate-400 text-[10px] block font-bold uppercase">Berat Badan (kg)</span>
                                <span class="font-semibold text-slate-800 dark:text-slate-200 text-sm" x-text="selectedStudent?.weight ? selectedStudent.weight + ' kg' : '-'"></span>
                            </div>

                            <!-- IMT / BMI Indicator Card -->
                            <div class="sm:col-span-3 p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800 flex items-center justify-between">
                                <div>
                                    <span class="text-slate-400 text-[10px] block font-bold uppercase">Indeks Massa Tubuh (IMT / BMI)</span>
                                    <template x-if="selectedStudent?.height && selectedStudent?.weight">
                                        <div class="flex items-center gap-2 mt-0.5">
                                            <span class="font-black text-slate-800 dark:text-slate-200 text-sm" x-text="(selectedStudent.weight / Math.pow(selectedStudent.height / 100, 2)).toFixed(1) + ' kg/m²'"></span>
                                            <span class="text-[11px] font-bold px-2 py-0.5 rounded-full"
                                                :class="{
                                                    'bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300': (selectedStudent.weight / Math.pow(selectedStudent.height / 100, 2)) < 18.5,
                                                    'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300': (selectedStudent.weight / Math.pow(selectedStudent.height / 100, 2)) >= 18.5 && (selectedStudent.weight / Math.pow(selectedStudent.height / 100, 2)) <= 25,
                                                    'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300': (selectedStudent.weight / Math.pow(selectedStudent.height / 100, 2)) > 25
                                                }"
                                                x-text="(selectedStudent.weight / Math.pow(selectedStudent.height / 100, 2)) < 18.5 ? 'Kurus / Berat Kurang' : ((selectedStudent.weight / Math.pow(selectedStudent.height / 100, 2)) <= 25 ? 'Normal / Ideal' : 'Kelebihan Berat Badan')">
                                            </span>
                                        </div>
                                    </template>
                                    <template x-if="!selectedStudent?.height || !selectedStudent?.weight">
                                        <span class="text-xs text-slate-400 italic">Data tinggi / berat badan belum lengkap untuk menghitung IMT.</span>
                                    </template>
                                </div>
                            </div>

                            <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                                <span class="text-slate-400 text-[10px] block font-bold uppercase">Warna Kulit</span>
                                <span class="font-semibold text-slate-800 dark:text-slate-200" x-text="selectedStudent?.skin_color || '-'"></span>
                            </div>
                            <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                                <span class="text-slate-400 text-[10px] block font-bold uppercase">Bentuk / Jenis Rambut</span>
                                <span class="font-semibold text-slate-800 dark:text-slate-200" x-text="selectedStudent?.hair_type || '-'"></span>
                            </div>
                            <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                                <span class="text-slate-400 text-[10px] block font-bold uppercase">Warna Rambut</span>
                                <span class="font-semibold text-slate-800 dark:text-slate-200" x-text="selectedStudent?.hair_color || '-'"></span>
                            </div>
                            <div class="sm:col-span-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                                <span class="text-slate-400 text-[10px] block font-bold uppercase">Riwayat Penyakit Berat / Rawat Inap / Alergi Makanan & Obat</span>
                                <span class="font-medium text-slate-800 dark:text-slate-200 leading-relaxed block mt-0.5" x-text="selectedStudent?.severe_disease_history || 'Tidak ada riwayat penyakit berat atau alergi.'"></span>
                            </div>
                            <div class="sm:col-span-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                                <span class="text-slate-400 text-[10px] block font-bold uppercase">Penyakit yang Sering Diderita / Kambuh</span>
                                <span class="font-medium text-slate-800 dark:text-slate-200 block mt-0.5" x-text="selectedStudent?.frequent_disease || '-'"></span>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 7: ASAL SEKOLAH & LIFECYCLE -->
                    <div x-show="activeDetailTab === 7" x-cloak style="display: none;" class="space-y-4">
                        <!-- Asal Sekolah & Kelulusan -->
                        <div>
                            <div class="flex items-center gap-2 mb-2 text-slate-800 dark:text-slate-200 font-bold">
                                <i data-lucide="graduation-cap" class="w-4 h-4 text-indigo-600 dark:text-indigo-400"></i>
                                <span>Pendidikan Asal & Kelulusan</span>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                                    <span class="text-slate-400 text-[10px] block font-bold uppercase">Sekolah Asal (SD / MI / SMP Pindahan)</span>
                                    <span class="font-bold text-slate-800 dark:text-slate-200" x-text="selectedStudent?.previous_school || '-'"></span>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                                    <span class="text-slate-400 text-[10px] block font-bold uppercase">Kategori Asal & No. STTB / Ijazah</span>
                                    <span class="font-semibold text-slate-800 dark:text-slate-200" x-text="(selectedStudent?.origin_category || 'SMP') + ' &bull; ' + (selectedStudent?.sttb_number_date || '-')"></span>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                                    <span class="text-slate-400 text-[10px] block font-bold uppercase">Alamat Sekolah Asal</span>
                                    <span class="font-medium text-slate-800 dark:text-slate-200" x-text="selectedStudent?.previous_school_address || '-'"></span>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                                    <span class="text-slate-400 text-[10px] block font-bold uppercase">Tahun Kelulusan Jenjang Sebelumnya</span>
                                    <span class="font-semibold text-slate-800 dark:text-slate-200" x-text="selectedStudent?.graduation_year || '-'"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Data Kelulusan SMP / Alumni (If Available) -->
                        <template x-if="selectedStudent?.diploma_number || selectedStudent?.continued_school || selectedStudent?.status === 'lulus'">
                            <div class="p-4 rounded-xl bg-blue-50/40 dark:bg-blue-950/20 border border-blue-100 dark:border-blue-900/40 space-y-2">
                                <div class="flex items-center gap-2">
                                    <i data-lucide="award" class="w-4 h-4 text-blue-600 dark:text-blue-400"></i>
                                    <h4 class="font-bold text-blue-900 dark:text-blue-200 text-xs">Data Kelulusan SMP & Sekolah Lanjutan</h4>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs pt-1">
                                    <div>
                                        <span class="text-slate-400 text-[10px] block font-bold uppercase">Nomor Ijazah SMP</span>
                                        <span class="font-mono font-bold text-slate-800 dark:text-slate-200" x-text="selectedStudent?.diploma_number || '-'"></span>
                                    </div>
                                    <div>
                                        <span class="text-slate-400 text-[10px] block font-bold uppercase">Sekolah Lanjutan (SMA / SMK / MA / Pondok)</span>
                                        <span class="font-bold text-slate-800 dark:text-slate-200" x-text="selectedStudent?.continued_school || '-'"></span>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- Catatan Tambahan Kesiswaan -->
                        <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                            <span class="text-slate-400 text-[10px] block font-bold uppercase">Catatan Kesiswaan / Mutasi / Tata Usaha</span>
                            <span class="font-medium text-slate-700 dark:text-slate-300 text-xs leading-relaxed block mt-0.5" x-text="selectedStudent?.notes || 'Tidak ada catatan tambahan.'"></span>
                        </div>

                        <!-- Timeline Riwayat Rombel (Lifecycle Journey) -->
                        <div class="p-4 rounded-xl bg-indigo-50/40 dark:bg-indigo-950/20 border border-indigo-100 dark:border-indigo-900/40 space-y-3">
                            <div class="flex items-center gap-2">
                                <i data-lucide="history" class="w-4 h-4 text-indigo-600 dark:text-indigo-400"></i>
                                <h4 class="font-bold text-indigo-900 dark:text-indigo-200 text-xs">Perjalanan Akademis / Riwayat Kelas</h4>
                            </div>

                            <div class="relative pl-6 space-y-4 before:absolute before:left-2 before:top-2 before:bottom-2 before:w-0.5 before:bg-indigo-200 dark:before:bg-indigo-800">
                                <template x-for="hist in classroomHistories" :key="hist.id">
                                    <div class="relative">
                                        <div class="absolute -left-6 top-1 w-2.5 h-2.5 rounded-full bg-indigo-600 border-2 border-white dark:border-slate-900"></div>
                                        <div class="p-2.5 rounded-lg bg-white dark:bg-slate-900 border border-indigo-100 dark:border-indigo-900/40 text-xs">
                                            <div class="flex items-center justify-between">
                                                <span class="font-bold text-slate-800 dark:text-slate-200" x-text="hist.classroom_name || (hist.classroom ? hist.classroom.name : 'Rombel')"></span>
                                                <span class="text-[10px] font-mono px-1.5 py-0.2 rounded bg-indigo-50 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400 font-semibold" x-text="hist.academic_year ? hist.academic_year.name : '-'"></span>
                                            </div>
                                            <div class="flex flex-wrap items-center gap-x-3 gap-y-1 mt-1 text-[11px] text-slate-500 dark:text-slate-400">
                                                <template x-if="hist.homeroom_teacher_name || hist.classroom?.homeroom_teacher">
                                                    <span class="flex items-center gap-1">
                                                        <i data-lucide="user" class="w-3 h-3 text-slate-400"></i>
                                                        <span>Wali: <strong class="text-slate-700 dark:text-slate-300" x-text="hist.homeroom_teacher_name || (hist.classroom?.homeroom_teacher?.full_name || hist.classroom?.homeroom_teacher?.name)"></strong></span>
                                                    </span>
                                                </template>
                                                <template x-if="hist.gpk_teacher_name">
                                                    <span class="flex items-center gap-1 text-purple-600 dark:text-purple-400">
                                                        <i data-lucide="user-check" class="w-3 h-3"></i>
                                                        <span>GPK: <strong class="text-purple-700 dark:text-purple-300" x-text="hist.gpk_teacher_name"></strong></span>
                                                    </span>
                                                </template>
                                            </div>
                                            <p class="text-[11px] text-slate-400 mt-1" x-text="hist.notes || ('Status: ' + (hist.status || 'aktif'))"></p>
                                        </div>
                                    </div>
                                </template>
                                <template x-if="classroomHistories.length === 0">
                                    <div class="text-xs text-slate-400 italic">Belum ada catatan riwayat kelas sebelumnya.</div>
                                </template>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Modal Footer -->
                <div class="p-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-900/80 flex items-center justify-between">
                    <div class="text-[11px] text-slate-400 hidden sm:block">
                        Tekan <kbd class="px-1.5 py-0.5 rounded bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-mono text-[10px]">ESC</kbd> untuk menutup detail.
                    </div>
                    <div class="flex items-center gap-2 ml-auto">
                        <button type="button" @click="detailModalOpen = false" class="px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 text-slate-700 dark:text-slate-300 rounded-lg text-xs font-semibold transition-colors cursor-pointer">
                            Tutup
                        </button>
                        <button type="button" @click="openEditModal(selectedStudent.id)" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold transition-colors flex items-center gap-1.5 cursor-pointer shadow-xs">
                            <i data-lucide="edit-2" class="w-3.5 h-3.5"></i>
                            Edit 7 Kategori Data
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================================= -->
        <!-- MODAL FORM TAMBAH / EDIT SISWA (7-TAB WIZARD) -->
        <!-- ========================================================= -->
        <div x-show="formModalOpen" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center p-3 sm:p-4" style="display: none; margin-top: 0px !important; z-index: 9999; background-color: rgba(15, 23, 42, 0.65); backdrop-filter: blur(4px);">
            <div @click.outside="formModalOpen = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-5xl max-h-[90vh] overflow-hidden shadow-2xl flex flex-col">
                
                <form @submit.prevent="submitForm" class="flex flex-col flex-1 min-h-0 overflow-hidden">
                    <!-- Form Top Header -->
                    <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between bg-slate-50/80 dark:bg-slate-900/80 shrink-0">
                        <div>
                            <h3 class="text-base font-black text-slate-900 dark:text-slate-50" x-text="isEdit ? 'Edit Data Siswa (7 Kategori)' : 'Tambah Siswa Baru (7 Kategori)'"></h3>
                            <p class="text-xs text-slate-400 mt-0.5">Kelola identitas Dapodik, inklusi, domisili, orang tua, fisik, dan asal sekolah.</p>
                        </div>
                        <button type="button" @click="formModalOpen = false" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

                    <!-- 7-Tab Form Navigation -->
                    <div class="flex items-center gap-1 px-4 border-b border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 overflow-x-auto text-xs font-semibold scrollbar-thin shrink-0">
                        <button type="button" @click="activeFormTab = 1" :class="activeFormTab === 1 ? 'text-indigo-600 border-indigo-600 dark:text-indigo-400 dark:border-indigo-400' : 'text-slate-500 border-transparent hover:text-slate-700'" class="px-3.5 py-3 border-b-2 flex items-center gap-1.5 whitespace-nowrap transition-colors">
                            <i data-lucide="id-card" class="w-3.5 h-3.5"></i> 1. Identitas
                        </button>
                        <button type="button" @click="activeFormTab = 2" :class="activeFormTab === 2 ? 'text-indigo-600 border-indigo-600 dark:text-indigo-400 dark:border-indigo-400' : 'text-slate-500 border-transparent hover:text-slate-700'" class="px-3.5 py-3 border-b-2 flex items-center gap-1.5 whitespace-nowrap transition-colors">
                            <i data-lucide="heart-handshake" class="w-3.5 h-3.5"></i> 2. Inklusi (PDBK)
                        </button>
                        <button type="button" @click="activeFormTab = 3" :class="activeFormTab === 3 ? 'text-indigo-600 border-indigo-600 dark:text-indigo-400 dark:border-indigo-400' : 'text-slate-500 border-transparent hover:text-slate-700'" class="px-3.5 py-3 border-b-2 flex items-center gap-1.5 whitespace-nowrap transition-colors">
                            <i data-lucide="map-pin" class="w-3.5 h-3.5"></i> 3. Alamat
                        </button>
                        <button type="button" @click="activeFormTab = 4" :class="activeFormTab === 4 ? 'text-indigo-600 border-indigo-600 dark:text-indigo-400 dark:border-indigo-400' : 'text-slate-500 border-transparent hover:text-slate-700'" class="px-3.5 py-3 border-b-2 flex items-center gap-1.5 whitespace-nowrap transition-colors">
                            <i data-lucide="users-round" class="w-3.5 h-3.5"></i> 4. Orang Tua & Wali
                        </button>
                        <button type="button" @click="activeFormTab = 5" :class="activeFormTab === 5 ? 'text-indigo-600 border-indigo-600 dark:text-indigo-400 dark:border-indigo-400' : 'text-slate-500 border-transparent hover:text-slate-700'" class="px-3.5 py-3 border-b-2 flex items-center gap-1.5 whitespace-nowrap transition-colors">
                            <i data-lucide="git-fork" class="w-3.5 h-3.5"></i> 5. Keluarga
                        </button>
                        <button type="button" @click="activeFormTab = 6" :class="activeFormTab === 6 ? 'text-indigo-600 border-indigo-600 dark:text-indigo-400 dark:border-indigo-400' : 'text-slate-500 border-transparent hover:text-slate-700'" class="px-3.5 py-3 border-b-2 flex items-center gap-1.5 whitespace-nowrap transition-colors">
                            <i data-lucide="activity" class="w-3.5 h-3.5"></i> 6. Kesehatan
                        </button>
                        <button type="button" @click="activeFormTab = 7" :class="activeFormTab === 7 ? 'text-indigo-600 border-indigo-600 dark:text-indigo-400 dark:border-indigo-400' : 'text-slate-500 border-transparent hover:text-slate-700'" class="px-3.5 py-3 border-b-2 flex items-center gap-1.5 whitespace-nowrap transition-colors">
                            <i data-lucide="history" class="w-3.5 h-3.5"></i> 7. Asal & Status
                        </button>
                    </div>

                    <!-- Form Body Fields -->
                    <div class="p-5 sm:p-6 overflow-y-auto flex-1 min-h-0 space-y-4 text-xs">

                        <!-- TAB 1: IDENTITAS & LEGALITAS -->
                        <div x-show="activeFormTab === 1" x-cloak class="space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div>
                                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">NIS (Wajib) <span class="text-rose-500">*</span></label>
                                    <input type="text" x-model="formData.nis" required placeholder="Contoh: 26.SD.001"
                                        class="w-full h-8.5 px-3 text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 font-mono text-slate-900 dark:text-slate-50">
                                </div>
                                <div>
                                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">NISN</label>
                                    <input type="text" x-model="formData.nisn" placeholder="10 digit NISN"
                                        class="w-full h-8.5 px-3 text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 font-mono text-slate-900 dark:text-slate-50">
                                </div>
                                <div>
                                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">NIK Siswa (16 digit)</label>
                                    <input type="text" x-model="formData.nik" placeholder="3573xxxxxxxxxxxx"
                                        class="w-full h-8.5 px-3 text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 font-mono text-slate-900 dark:text-slate-50">
                                </div>

                                <div class="sm:col-span-2">
                                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama Lengkap Siswa <span class="text-rose-500">*</span></label>
                                    <input type="text" x-model="formData.full_name" required placeholder="Nama lengkap sesuai akte"
                                        class="w-full h-8.5 px-3 text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-slate-900 dark:text-slate-50">
                                </div>
                                <div>
                                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama Panggilan</label>
                                    <input type="text" x-model="formData.nickname" placeholder="Nama panggilan"
                                        class="w-full h-8.5 px-3 text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-slate-900 dark:text-slate-50">
                                </div>

                                <div>
                                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Jenis Kelamin <span class="text-rose-500">*</span></label>
                                    <select x-model="formData.gender" required
                                        class="w-full h-8.5 px-3 text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 cursor-pointer">
                                        <option value="L">Laki-laki</option>
                                        <option value="P">Perempuan</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Tempat Lahir</label>
                                    <input type="text" x-model="formData.birth_place" placeholder="Kota lahir"
                                        class="w-full h-8.5 px-3 text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-slate-900 dark:text-slate-50">
                                </div>
                                <div>
                                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Tanggal Lahir</label>
                                    <input type="date" x-model="formData.birth_date"
                                        class="w-full h-8.5 px-3 text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-slate-900 dark:text-slate-50">
                                </div>

                                <div>
                                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">No. Kartu Keluarga (KK)</label>
                                    <input type="text" x-model="formData.no_kk" placeholder="16 digit No. KK"
                                        class="w-full h-8.5 px-3 text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 font-mono text-slate-900 dark:text-slate-50">
                                </div>
                                <div>
                                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">No. Akta Kelahiran</label>
                                    <input type="text" x-model="formData.birth_certificate_no" placeholder="No. Registrasi Akta"
                                        class="w-full h-8.5 px-3 text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-slate-900 dark:text-slate-50">
                                </div>
                                <div>
                                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Agama</label>
                                    <select x-model="formData.religion"
                                        class="w-full h-8.5 px-3 text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 cursor-pointer">
                                        <option value="Islam">Islam</option>
                                        <option value="Kristen">Kristen</option>
                                        <option value="Katolik">Katolik</option>
                                        <option value="Hindu">Hindu</option>
                                        <option value="Buddha">Buddha</option>
                                        <option value="Konghucu">Konghucu</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                        Tanggal Terdaftar / Masuk
                                        <span class="text-[10px] font-normal text-slate-400 ml-1">(Opsional)</span>
                                    </label>
                                    <input type="date" x-model="formData.enrolled_date"
                                        class="w-full h-8.5 px-3 text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-slate-900 dark:text-slate-50">
                                </div>
                            </div>
                        </div>

                        <!-- TAB 2: INKLUSI (PDBK) -->
                        <div x-show="activeFormTab === 2" x-cloak style="display: none;" class="space-y-4 min-h-[380px] pb-16">
                            <div class="p-5 rounded-2xl border border-purple-200 dark:border-purple-800/60 bg-purple-50/40 dark:bg-purple-950/20 space-y-4">
                                <div class="flex items-center gap-2.5 pb-2 border-b border-purple-200/60 dark:border-purple-800/40">
                                    <div class="p-2 bg-purple-600 text-white rounded-xl shadow-xs">
                                        <i data-lucide="heart-handshake" class="w-4 h-4"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-purple-900 dark:text-purple-200 text-xs">Informasi Program Inklusi & Kebutuhan Khusus (PDBK)</h4>
                                        <p class="text-[11px] text-purple-700 dark:text-purple-300">Tentukan status siswa, jenis kekhususan, dan penugasan Guru Pendamping Khusus (GPK).</p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Tipe Peserta Didik</label>
                                        <select x-model="formData.student_type"
                                            class="w-full h-9 px-3 text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 cursor-pointer font-medium">
                                            <option value="REGULER">REGULER</option>
                                            <option value="PDBK">PDBK (Peserta Didik Berkebutuhan Khusus)</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Jenis Kekhususan / Ketunaan</label>
                                        <input type="text" x-model="formData.special_needs_type" placeholder="Contoh: ADHD, Spektrum Autis, Slow Learner, Speech Delay..."
                                            class="w-full h-9 px-3 text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 text-slate-900 dark:text-slate-50">
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                            Guru Pendamping Khusus (GPK / Shadow Teacher)
                                            <span class="text-[10px] font-normal text-slate-400 ml-1">(Pilih guru pendamping untuk siswa PDBK)</span>
                                        </label>
                                        
                                        <!-- In-Flow Searchable GPK Selector Component -->
                                        <div class="relative" @click.outside="gpkDropdownOpen = false">
                                            <!-- State 1: Collapsed / Compact Trigger Button -->
                                            <div x-show="!gpkDropdownOpen"
                                                @click="gpkDropdownOpen = true; $nextTick(() => $refs.gpkSearchInput?.focus())"
                                                class="w-full h-10 px-3.5 text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:border-purple-400 dark:hover:border-purple-600 rounded-xl flex items-center justify-between gap-2 text-left shadow-xs cursor-pointer transition-all">
                                                
                                                <div class="flex items-center gap-2 truncate">
                                                    <template x-if="formData.gpk_employee_id && selectedGpkName">
                                                        <div class="flex items-center gap-2 truncate">
                                                            <span class="w-5 h-5 rounded-full bg-purple-100 dark:bg-purple-900/60 text-purple-700 dark:text-purple-300 flex items-center justify-center font-bold text-[10px] shrink-0">
                                                                <i data-lucide="user-check" class="w-3 h-3"></i>
                                                            </span>
                                                            <span class="font-bold text-slate-800 dark:text-slate-100 truncate text-xs" x-text="selectedGpkName"></span>
                                                            <span class="text-[10px] text-purple-600 dark:text-purple-400 bg-purple-50 dark:bg-purple-950/50 px-2 py-0.5 rounded font-semibold border border-purple-200 dark:border-purple-800 shrink-0">Guru GPK</span>
                                                        </div>
                                                    </template>
                                                    <template x-if="!formData.gpk_employee_id || !selectedGpkName">
                                                        <span class="text-slate-400 dark:text-slate-500 flex items-center gap-1.5">
                                                            <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400"></i>
                                                            -- Pilih / Cari Guru Pendamping Khusus (GPK) --
                                                        </span>
                                                    </template>
                                                </div>

                                                <div class="flex items-center gap-1.5 shrink-0 text-slate-400">
                                                    <template x-if="formData.gpk_employee_id">
                                                        <span @click.stop="clearGpk()" class="p-1 hover:bg-slate-100 dark:hover:bg-slate-800 rounded text-slate-400 hover:text-rose-500 transition-colors cursor-pointer" title="Kosongkan Pilihan">
                                                            <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                                        </span>
                                                    </template>
                                                    <i data-lucide="chevron-down" class="w-4 h-4 text-purple-500"></i>
                                                </div>
                                            </div>

                                            <!-- State 2: Expanded In-Flow Search & Selection Card -->
                                            <div x-show="gpkDropdownOpen" x-cloak style="display: none;"
                                                class="bg-white dark:bg-slate-900 border-2 border-purple-500/70 dark:border-purple-600 rounded-2xl p-3 shadow-lg space-y-2.5">
                                                
                                                <!-- Search Bar + Close Button -->
                                                <div class="flex items-center gap-2">
                                                    <div class="relative flex-1">
                                                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-purple-500">
                                                            <i data-lucide="search" class="w-4 h-4"></i>
                                                        </span>
                                                        <input type="text" x-ref="gpkSearchInput" x-model.debounce.150ms="gpkSearch" @input="filterGpkTeachers()" placeholder="Ketik nama guru GPK / NIP untuk mencari..."
                                                            style="padding-left: 2.25rem;"
                                                            class="w-full h-9 pr-3 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500/30 focus:border-purple-500 text-slate-900 dark:text-slate-100 placeholder-slate-400 font-medium">
                                                    </div>
                                                    <button type="button" @click="gpkDropdownOpen = false" class="px-3 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-xl text-xs font-semibold flex items-center gap-1 shrink-0 transition-colors cursor-pointer">
                                                        <i data-lucide="x" class="w-3.5 h-3.5"></i> Tutup
                                                    </button>
                                                </div>

                                                <!-- Info Bar -->
                                                <div class="flex items-center justify-between text-[11px] text-slate-400 px-1">
                                                    <span>Menampilkan <strong class="text-purple-600 dark:text-purple-400" x-text="gpkTeacherList.length"></strong> guru GPK:</span>
                                                    <span class="text-slate-400">Klik nama untuk memilih</span>
                                                </div>

                                                <!-- Scrollable Options List (In-Flow) -->
                                                <div class="overflow-y-auto max-h-52 divide-y divide-slate-100 dark:divide-slate-800/60 rounded-xl border border-slate-200/80 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 p-1 space-y-0.5 scrollbar-thin">
                                                    <!-- Option: Kosongkan -->
                                                    <button type="button" @click="clearGpk()"
                                                        class="w-full px-3 py-2 text-left hover:bg-white dark:hover:bg-slate-800 rounded-lg flex items-center justify-between transition-colors cursor-pointer"
                                                        :class="{ 'bg-purple-100/70 dark:bg-purple-950/60 text-purple-900 dark:text-purple-200 font-bold border border-purple-200 dark:border-purple-800': !formData.gpk_employee_id }">
                                                        <span class="text-slate-500 dark:text-slate-400 italic">-- Belum Ditentukan / Tidak Ada GPK --</span>
                                                        <template x-if="!formData.gpk_employee_id">
                                                            <i data-lucide="check" class="w-4 h-4 text-purple-600 shrink-0"></i>
                                                        </template>
                                                    </button>

                                                    <!-- GPK Teachers Options -->
                                                    <template x-for="t in gpkTeacherList" :key="t.id">
                                                        <button type="button" @click="selectGpk(t.id)"
                                                            class="w-full px-3 py-2 text-left hover:bg-purple-50 dark:hover:bg-purple-950/40 rounded-lg flex items-center justify-between gap-2 transition-colors cursor-pointer"
                                                            :class="{ 'bg-purple-100 dark:bg-purple-950/80 text-purple-950 dark:text-purple-100 font-bold border border-purple-300 dark:border-purple-700': formData.gpk_employee_id == t.id }">
                                                            <div class="flex items-center gap-2.5 min-w-0">
                                                                <div class="w-7 h-7 rounded-full bg-purple-100 dark:bg-purple-900/60 text-purple-700 dark:text-purple-300 flex items-center justify-center font-bold text-[11px] shrink-0">
                                                                    <span x-text="(t.name || 'G').charAt(0)"></span>
                                                                </div>
                                                                <div class="flex flex-col min-w-0">
                                                                    <span class="truncate text-slate-800 dark:text-slate-100 text-xs" :class="{ 'font-bold text-purple-700 dark:text-purple-300': formData.gpk_employee_id == t.id }" x-text="t.full_name || t.name"></span>
                                                                    <span class="text-[10px] text-slate-400 truncate" x-text="'Posisi: ' + (t.position || 'GPK') + (t.nip ? ' • NIP: ' + t.nip : '')"></span>
                                                                </div>
                                                            </div>
                                                            <template x-if="formData.gpk_employee_id == t.id">
                                                                <div class="flex items-center gap-1 text-purple-600 dark:text-purple-400 font-bold text-[11px] shrink-0">
                                                                    <span>Terpilih</span>
                                                                    <i data-lucide="check" class="w-4 h-4"></i>
                                                                </div>
                                                            </template>
                                                        </button>
                                                    </template>

                                                    <!-- Empty Search State -->
                                                    <template x-if="gpkTeacherList.length === 0">
                                                        <div class="px-4 py-6 text-center text-slate-400 text-xs">
                                                            <i data-lucide="search-x" class="w-5 h-5 mx-auto mb-1 text-slate-300"></i>
                                                            <p class="font-semibold text-slate-600 dark:text-slate-300">Guru GPK tidak ditemukan</p>
                                                            <p class="text-[10px] text-slate-400 mt-0.5">Coba kata kunci pencarian nama atau NIP yang lain.</p>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Catatan Pendampingan Guru / Penanganan Khusus</label>
                                        <textarea x-model="formData.special_needs_notes" rows="3" placeholder="Deskripsi kebutuhan pendampingan / terapi yang sedang dijalani..."
                                            class="w-full p-2.5 text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- TAB 3: ALAMAT & DOMISILI -->
                        <div x-show="activeFormTab === 3" x-cloak style="display: none;" class="space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div class="sm:col-span-3">
                                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Alamat Domisili Lengkap</label>
                                    <textarea x-model="formData.address" rows="2" placeholder="Jalan, No. Rumah, Blok, Dusun..."
                                        class="w-full p-2.5 text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"></textarea>
                                </div>
                                <div>
                                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">RT / RW</label>
                                    <div class="flex items-center gap-1.5">
                                        <input type="text" x-model="formData.rt" placeholder="RT" class="w-1/2 h-8.5 px-2.5 text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg">
                                        <input type="text" x-model="formData.rw" placeholder="RW" class="w-1/2 h-8.5 px-2.5 text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg">
                                    </div>
                                </div>
                                <div>
                                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Kelurahan / Desa</label>
                                    <input type="text" x-model="formData.village" placeholder="Kelurahan"
                                        class="w-full h-8.5 px-3 text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg">
                                </div>
                                <div>
                                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Kecamatan</label>
                                    <input type="text" x-model="formData.district" placeholder="Kecamatan"
                                        class="w-full h-8.5 px-3 text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg">
                                </div>

                                <div>
                                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Kota / Kabupaten</label>
                                    <input type="text" x-model="formData.city" placeholder="Kota Malang"
                                        class="w-full h-8.5 px-3 text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg">
                                </div>
                                <div>
                                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Provinsi</label>
                                    <input type="text" x-model="formData.province" placeholder="Jawa Timur"
                                        class="w-full h-8.5 px-3 text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg">
                                </div>
                                <div>
                                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Kode Pos</label>
                                    <input type="text" x-model="formData.postal_code" placeholder="Kode Pos"
                                        class="w-full h-8.5 px-3 text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg font-mono">
                                </div>

                                <div>
                                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Status Tempat Tinggal</label>
                                    <input type="text" x-model="formData.residence_status" placeholder="Bersama Orang Tua / Kos / Asrama"
                                        class="w-full h-8.5 px-3 text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg">
                                </div>
                                <div>
                                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Jarak ke Sekolah</label>
                                    <input type="text" x-model="formData.distance_to_school" placeholder="Contoh: < 1 km, 3 km..."
                                        class="w-full h-8.5 px-3 text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg">
                                </div>
                                <div>
                                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">No. Telp Rumah</label>
                                    <input type="text" x-model="formData.home_phone" placeholder="0341-xxxx"
                                        class="w-full h-8.5 px-3 text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg font-mono">
                                </div>
                            </div>
                        </div>

                        <!-- TAB 4: ORANG TUA & WALI -->
                        <div x-show="activeFormTab === 4" x-cloak style="display: none;" class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Data Ayah -->
                                <div class="p-3.5 rounded-xl bg-blue-50/40 dark:bg-blue-950/20 border border-blue-100 dark:border-blue-900/40 space-y-2.5">
                                    <div class="flex items-center gap-2 border-b border-blue-100 dark:border-blue-900/40 pb-1.5">
                                        <i data-lucide="user" class="w-3.5 h-3.5 text-blue-600"></i>
                                        <span class="font-bold text-blue-900 dark:text-blue-300 text-xs uppercase">Data Ayah Kandung</span>
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-0.5">Nama Ayah</label>
                                        <input type="text" x-model="formData.father_name" placeholder="Nama lengkap ayah" class="w-full h-8 px-2.5 text-xs rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
                                    </div>
                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-0.5">NIK Ayah</label>
                                            <input type="text" x-model="formData.father_nik" placeholder="16 digit NIK" class="w-full h-8 px-2.5 text-xs rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 font-mono">
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-0.5">No. HP / WA Ayah</label>
                                            <input type="text" x-model="formData.father_phone" placeholder="08xxxxxxxxxx" class="w-full h-8 px-2.5 text-xs rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 font-mono">
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-0.5">Pendidikan</label>
                                            <input type="text" x-model="formData.father_education" placeholder="S1 / S2 / SMA..." class="w-full h-8 px-2.5 text-xs rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-0.5">Pekerjaan</label>
                                            <input type="text" x-model="formData.father_job" placeholder="PNS / Swasta..." class="w-full h-8 px-2.5 text-xs rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-0.5">Instansi / Perusahaan</label>
                                            <input type="text" x-model="formData.father_company" placeholder="Nama kantor/instansi" class="w-full h-8 px-2.5 text-xs rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-0.5">Penghasilan Bulanan</label>
                                            <input type="text" x-model="formData.father_income" placeholder="Contoh: > 5 Juta" class="w-full h-8 px-2.5 text-xs rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-0.5">Alamat Kantor & Telp Kantor Ayah</label>
                                        <div class="grid grid-cols-3 gap-2">
                                            <input type="text" x-model="formData.father_company_address" placeholder="Alamat kantor..." class="col-span-2 w-full h-8 px-2.5 text-xs rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
                                            <input type="text" x-model="formData.father_company_phone" placeholder="Telp kantor" class="w-full h-8 px-2.5 text-xs rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-0.5">Alamat Rumah & Medsos Ayah</label>
                                        <div class="grid grid-cols-2 gap-2">
                                            <input type="text" x-model="formData.father_address" placeholder="Alamat domisili ayah..." class="w-full h-8 px-2.5 text-xs rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
                                            <input type="text" x-model="formData.father_social_media" placeholder="Akun IG/FB ayah..." class="w-full h-8 px-2.5 text-xs rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
                                        </div>
                                    </div>
                                </div>

                                <!-- Data Ibu -->
                                <div class="p-3.5 rounded-xl bg-rose-50/40 dark:bg-rose-950/20 border border-rose-100 dark:border-rose-900/40 space-y-2.5">
                                    <div class="flex items-center gap-2 border-b border-rose-100 dark:border-rose-900/40 pb-1.5">
                                        <i data-lucide="user-check" class="w-3.5 h-3.5 text-rose-600"></i>
                                        <span class="font-bold text-rose-900 dark:text-rose-300 text-xs uppercase">Data Ibu Kandung</span>
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-0.5">Nama Ibu</label>
                                        <input type="text" x-model="formData.mother_name" placeholder="Nama lengkap ibu" class="w-full h-8 px-2.5 text-xs rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
                                    </div>
                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-0.5">NIK Ibu</label>
                                            <input type="text" x-model="formData.mother_nik" placeholder="16 digit NIK" class="w-full h-8 px-2.5 text-xs rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 font-mono">
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-0.5">No. HP / WA Ibu</label>
                                            <input type="text" x-model="formData.mother_phone" placeholder="08xxxxxxxxxx" class="w-full h-8 px-2.5 text-xs rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 font-mono">
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-0.5">Pendidikan</label>
                                            <input type="text" x-model="formData.mother_education" placeholder="S1 / S2 / SMA..." class="w-full h-8 px-2.5 text-xs rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-0.5">Pekerjaan</label>
                                            <input type="text" x-model="formData.mother_job" placeholder="IRT / Guru / PNS..." class="w-full h-8 px-2.5 text-xs rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-0.5">Instansi / Perusahaan</label>
                                            <input type="text" x-model="formData.mother_company" placeholder="Nama kantor/instansi" class="w-full h-8 px-2.5 text-xs rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-0.5">Penghasilan Bulanan</label>
                                            <input type="text" x-model="formData.mother_income" placeholder="Contoh: > 5 Juta" class="w-full h-8 px-2.5 text-xs rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-0.5">Alamat Kantor & Telp Kantor Ibu</label>
                                        <div class="grid grid-cols-3 gap-2">
                                            <input type="text" x-model="formData.mother_company_address" placeholder="Alamat kantor..." class="col-span-2 w-full h-8 px-2.5 text-xs rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
                                            <input type="text" x-model="formData.mother_company_phone" placeholder="Telp kantor" class="w-full h-8 px-2.5 text-xs rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-0.5">Alamat Rumah & Medsos Ibu</label>
                                        <div class="grid grid-cols-2 gap-2">
                                            <input type="text" x-model="formData.mother_address" placeholder="Alamat domisili ibu..." class="w-full h-8 px-2.5 text-xs rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
                                            <input type="text" x-model="formData.mother_social_media" placeholder="Akun IG/FB ibu..." class="w-full h-8 px-2.5 text-xs rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Data Wali (Opsional) -->
                            <div class="p-3.5 rounded-xl bg-amber-50/40 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-900/40 space-y-2.5">
                                <div class="flex items-center gap-2 border-b border-amber-200/60 dark:border-amber-900/40 pb-1.5">
                                    <i data-lucide="shield" class="w-3.5 h-3.5 text-amber-600"></i>
                                    <span class="font-bold text-amber-900 dark:text-amber-300 text-xs uppercase">Data Wali Murid (Jika Ada / Tinggal Bersama Wali)</span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                                    <div>
                                        <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-0.5">Nama Lengkap Wali</label>
                                        <input type="text" x-model="formData.guardian_name" placeholder="Nama wali murid" class="w-full h-8 px-2.5 text-xs rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-0.5">NIK Wali</label>
                                        <input type="text" x-model="formData.guardian_nik" placeholder="16 digit NIK wali" class="w-full h-8 px-2.5 text-xs rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 font-mono">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-0.5">Hubungan dengan Siswa</label>
                                        <input type="text" x-model="formData.guardian_relation" placeholder="Paman / Kakek / Kakak..." class="w-full h-8 px-2.5 text-xs rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-0.5">No. HP / WA Wali</label>
                                        <input type="text" x-model="formData.guardian_phone" placeholder="08xxxxxxxxxx" class="w-full h-8 px-2.5 text-xs rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 font-mono">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-0.5">Pekerjaan & Instansi</label>
                                        <input type="text" x-model="formData.guardian_job" placeholder="Pekerjaan wali..." class="w-full h-8 px-2.5 text-xs rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-0.5">Alamat Domisili Wali</label>
                                        <input type="text" x-model="formData.guardian_address" placeholder="Alamat rumah wali..." class="w-full h-8 px-2.5 text-xs rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
                                    </div>
                                </div>
                            </div>

                            <!-- Primary WhatsApp Contact -->
                            <div class="p-3.5 rounded-xl bg-emerald-50/60 dark:bg-emerald-950/30 border border-emerald-100 dark:border-emerald-900/40">
                                <label class="block font-bold text-emerald-900 dark:text-emerald-300 mb-1">No. WhatsApp Utama (Notifikasi & Pengumuman Sekolah)</label>
                                <input type="text" x-model="formData.parent_phone" placeholder="08xxxxxxxxxx (WA aktif orang tua)"
                                    class="w-full h-8.5 px-3 text-xs bg-white dark:bg-slate-900 border border-emerald-300 dark:border-emerald-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500/20 font-mono font-bold text-emerald-700 dark:text-emerald-300">
                            </div>
                        </div>

                        <!-- TAB 5: KELUARGA & SAUDARA -->
                        <div x-show="activeFormTab === 5" x-cloak style="display: none;" class="space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                                <div>
                                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Anak Ke-</label>
                                    <input type="number" x-model="formData.child_number" min="1" placeholder="1"
                                        class="w-full h-8.5 px-3 text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg">
                                </div>
                                <div>
                                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Jml Sdr Kandung</label>
                                    <input type="number" x-model="formData.siblings_count" min="0" placeholder="0"
                                        class="w-full h-8.5 px-3 text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg">
                                </div>
                                <div>
                                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Jml Sdr Tiri</label>
                                    <input type="number" x-model="formData.step_siblings_count" min="0" placeholder="0"
                                        class="w-full h-8.5 px-3 text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg">
                                </div>
                                <div>
                                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Jml Sdr Angkat</label>
                                    <input type="number" x-model="formData.adoptive_siblings_count" min="0" placeholder="0"
                                        class="w-full h-8.5 px-3 text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg">
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Bahasa Sehari-hari di Rumah</label>
                                    <input type="text" x-model="formData.home_language" placeholder="Bahasa Indonesia / Jawa / Inggris..."
                                        class="w-full h-8.5 px-3 text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg">
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Suku Bangsa</label>
                                    <input type="text" x-model="formData.ethnic_group" placeholder="Jawa / Madura / Sunda / Batak..."
                                        class="w-full h-8.5 px-3 text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg">
                                </div>
                            </div>
                        </div>

                        <!-- TAB 6: KESEHATAN & FISIK (UKS) -->
                        <div x-show="activeFormTab === 6" x-cloak style="display: none;" class="space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div>
                                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Golongan Darah</label>
                                    <select x-model="formData.blood_type"
                                        class="w-full h-8.5 px-3 text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg cursor-pointer">
                                        <option value="">Pilih / Tidak Tahu</option>
                                        <option value="A">A</option>
                                        <option value="B">B</option>
                                        <option value="AB">AB</option>
                                        <option value="O">O</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Tinggi Badan (cm)</label>
                                    <input type="text" x-model="formData.height" placeholder="Contoh: 145"
                                        class="w-full h-8.5 px-3 text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg">
                                </div>
                                <div>
                                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Berat Badan (kg)</label>
                                    <input type="text" x-model="formData.weight" placeholder="Contoh: 40"
                                        class="w-full h-8.5 px-3 text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg">
                                </div>
                                <div>
                                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Warna Kulit</label>
                                    <input type="text" x-model="formData.skin_color" placeholder="Sawo Matang / Kuning Langsat / Putih"
                                        class="w-full h-8.5 px-3 text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg">
                                </div>
                                <div>
                                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Bentuk / Jenis Rambut</label>
                                    <input type="text" x-model="formData.hair_type" placeholder="Lurus / Ikal / Keriting"
                                        class="w-full h-8.5 px-3 text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg">
                                </div>
                                <div>
                                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Warna Rambut</label>
                                    <input type="text" x-model="formData.hair_color" placeholder="Hitam / Coklat..."
                                        class="w-full h-8.5 px-3 text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg">
                                </div>
                                <div class="sm:col-span-3">
                                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Riwayat Penyakit Berat / Alergi Makanan / Obat</label>
                                    <textarea x-model="formData.severe_disease_history" rows="2" placeholder="Catatan penyakit berat atau alergi penting untuk tim UKS..."
                                        class="w-full p-2.5 text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg"></textarea>
                                </div>
                                <div class="sm:col-span-3">
                                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Penyakit yang Sering Diderita</label>
                                    <input type="text" x-model="formData.frequent_disease" placeholder="Contoh: Asma, Batuk, Demam..."
                                        class="w-full h-8.5 px-3 text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg">
                                </div>
                            </div>
                        </div>

                        <!-- TAB 7: ASAL SEKOLAH & PENEMPATAN KELAS -->
                        <div x-show="activeFormTab === 7" x-cloak style="display: none;" class="space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Rombongan Belajar (Kelas) <span class="text-slate-400 text-[10px] font-normal">(Opsional)</span></label>
                                    <select x-model="formData.classroom_id"
                                        class="w-full h-8.5 px-3 text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 cursor-pointer">
                                        <option value="">Belum Ada Rombel / Tanpa Kelas</option>
                                        @foreach($allClassrooms as $r)
                                            <option value="{{ $r->id }}">
                                                {{ $r->full_name }} (Tapel {{ $r->academicYear->name ?? '-' }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Status Kesiswaan <span class="text-rose-500">*</span></label>
                                    <select x-model="formData.status" required
                                        class="w-full h-8.5 px-3 text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 cursor-pointer">
                                        <option value="aktif">Aktif</option>
                                        <option value="lulus">Lulus</option>
                                        <option value="mutasi">Mutasi</option>
                                        <option value="keluar">Keluar</option>
                                        <option value="nonaktif">Nonaktif</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Asal Sekolah (TK / PAUD / SD Pindahan)</label>
                                    <input type="text" x-model="formData.previous_school" placeholder="Nama TK/SD Asal"
                                        class="w-full h-8.5 px-3 text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg">
                                </div>
                                <div>
                                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">No. & Tanggal STTB / Ijazah</label>
                                    <input type="text" x-model="formData.sttb_number_date" placeholder="No. Ijazah TK"
                                        class="w-full h-8.5 px-3 text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg">
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Catatan Tambahan TU / Catatan Mutasi</label>
                                    <textarea x-model="formData.notes" rows="2" placeholder="Catatan kesiswaan..."
                                        class="w-full p-2.5 text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg"></textarea>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Form Footer Buttons -->
                    <div class="p-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 flex items-center justify-between shrink-0">
                        <div class="flex items-center gap-1.5">
                            <button type="button" x-show="activeFormTab > 1" x-cloak style="display: none;" @click="activeFormTab--" class="px-3 py-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 text-slate-700 dark:text-slate-300 rounded-lg text-xs font-semibold flex items-center gap-1 cursor-pointer">
                                <i data-lucide="chevron-left" class="w-3.5 h-3.5"></i> Sebelumnya
                            </button>
                            <button type="button" x-show="activeFormTab < 7" @click="activeFormTab++" class="px-3 py-1.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-lg text-xs font-semibold flex items-center gap-1 cursor-pointer">
                                Selanjutnya <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                            </button>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" @click="formModalOpen = false" class="px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 text-slate-700 dark:text-slate-300 rounded-lg text-xs font-semibold transition-colors cursor-pointer">
                                Batal
                            </button>
                            <button type="submit" :disabled="saving" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white rounded-lg text-xs font-bold transition-all shadow-xs flex items-center gap-1.5 cursor-pointer">
                                <span x-show="saving" x-cloak style="display: none;" class="w-3.5 h-3.5 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                                <span x-text="saving ? 'Menyimpan...' : (isEdit ? 'Simpan Perubahan' : 'Simpan Siswa Baru')"></span>
                            </button>
                        </div>
                    </div>
                </form>

            </div>
        </div>

        <!-- ========================================================= -->
        <!-- MODAL IMPOR EXCEL SISWA -->
        <!-- ========================================================= -->
        <div x-show="importModalOpen" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center p-4" style="display: none; margin-top: 0px !important; z-index: 9999; background-color: rgba(15, 23, 42, 0.65); backdrop-filter: blur(4px);">
            <div @click.outside="importModalOpen = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-lg overflow-hidden shadow-2xl flex flex-col">
                
                <form action="{{ route('students.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <!-- Header -->
                    <div class="p-5 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between sticky top-0 bg-white/95 dark:bg-slate-900/95 backdrop-blur-sm z-10">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800 text-emerald-600 dark:text-emerald-400 font-bold text-sm flex items-center justify-center">
                                <i data-lucide="file-spreadsheet" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-900 dark:text-slate-50">Impor Siswa Massal (Excel)</h3>
                                <p class="text-xs text-slate-400 mt-0.5">Unggah database siswa untuk kelas berjalan (Kelas 7–9).</p>
                            </div>
                        </div>
                        <button type="button" @click="importModalOpen = false" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

                    <!-- Body -->
                    <div class="p-6 space-y-4 text-xs">
                        <div class="p-4 rounded-xl bg-indigo-50/60 dark:bg-indigo-950/30 border border-indigo-100 dark:border-indigo-900/50 space-y-2.5">
                            <div class="flex items-center justify-between gap-3">
                                <span class="font-bold text-indigo-900 dark:text-indigo-300">Format Template Excel</span>
                                <button type="button" 
                                    @click="downloadTemplate()" 
                                    :disabled="downloadingTemplate"
                                    class="inline-flex items-center gap-2 px-3.5 py-1.5 bg-indigo-600 hover:bg-indigo-700 disabled:bg-indigo-400 text-white rounded-lg text-xs font-semibold transition-all shadow-xs cursor-pointer disabled:cursor-not-allowed">
                                    <svg x-show="downloadingTemplate" class="animate-spin w-3.5 h-3.5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <i x-show="!downloadingTemplate" data-lucide="download" class="w-3.5 h-3.5"></i>
                                    <span x-text="downloadingTemplate ? 'Mengunduh...' : 'Unduh Template (.xlsx)'"></span>
                                </button>
                            </div>
                            <p class="text-[11px] text-indigo-800/80 dark:text-indigo-300/80 leading-relaxed">
                                Gunakan template resmi untuk mengisi data siswa. Di dalam file Excel terdapat lembar <strong>"Referensi Rombel & Tapel"</strong> untuk melihat daftar nama rombel yang aktif di sistem.
                            </p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Rombel Tujuan (Default)</label>
                                <select name="default_classroom_id"
                                    class="w-full h-8.5 px-3 text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 text-slate-900 dark:text-slate-50 cursor-pointer">
                                    <option value="">Gunakan kolom di Excel (Otomatis)</option>
                                    @foreach($allClassrooms as $r)
                                        <option value="{{ $r->id }}">{{ $r->full_name }} (Tapel {{ $r->academicYear->name ?? '-' }})</option>
                                    @endforeach
                                </select>
                                <p class="text-[10px] text-slate-400 mt-1">Pilih rombel jika file tidak memuat kolom kelas, atau pilih "Gunakan kolom di Excel" jika rombel tercantum per baris.</p>
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Tahun Pelajaran Default</label>
                                <select name="default_academic_year_id"
                                    class="w-full h-8.5 px-3 text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 text-slate-900 dark:text-slate-50 cursor-pointer">
                                    @foreach($academicYears as $y)
                                        <option value="{{ $y->id }}" {{ $y->has_active || $y->is_active ? 'selected' : '' }}>Tapel {{ $y->name }} {{ ($y->has_active || $y->is_active) ? '(Aktif)' : '' }}</option>
                                    @endforeach
                                </select>
                                <p class="text-[10px] text-slate-400 mt-1">Tahun pelajaran acuan jika kolom tahun pelajaran di Excel kosong.</p>
                            </div>
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Pilih File Excel (.xlsx, .xls, .csv) <span class="text-rose-500">*</span></label>
                            <input type="file" name="file" required accept=".xlsx,.xls,.csv"
                                class="w-full text-xs text-slate-500 dark:text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 dark:file:bg-indigo-950/60 dark:file:text-indigo-300 hover:file:bg-indigo-100 border border-slate-200 dark:border-slate-800 rounded-lg p-1.5 bg-white dark:bg-slate-900">
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="p-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 flex justify-end gap-2">
                        <button type="button" @click="importModalOpen = false" class="px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 text-slate-700 dark:text-slate-300 rounded-lg text-xs font-semibold transition-colors">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition-all shadow-xs flex items-center gap-1.5">
                            <i data-lucide="upload" class="w-3.5 h-3.5"></i>
                            Mulai Impor Siswa
                        </button>
                    </div>
                </form>

            </div>
        </div>

        <!-- MODAL KONFIRMASI IN-APP (Aman dari native alert/confirm loop) -->
        <div x-show="confirmModal.open" x-cloak style="display: none; margin-top: 0px !important; z-index: 9999;"
            class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
            @click.self="confirmModal.open = false"
            @keydown.escape.window="confirmModal.open = false">
            
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-md overflow-hidden shadow-2xl flex flex-col p-6 text-center animate-in fade-in zoom-in-95 duration-150"
                @click.stop>
                
                <div class="w-12 h-12 rounded-full mx-auto flex items-center justify-center mb-4 bg-rose-100 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400">
                    <i data-lucide="trash-2" class="w-6 h-6"></i>
                </div>

                <h3 class="text-base font-bold text-slate-900 dark:text-slate-50" x-text="confirmModal.title"></h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-2 leading-relaxed" x-html="confirmModal.message"></p>

                <div class="mt-6 flex items-center justify-center gap-3">
                    <button type="button" @click="confirmModal.open = false" :disabled="confirmModal.loading"
                        class="px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-lg text-xs font-semibold transition-colors cursor-pointer">
                        Batal
                    </button>
                    <button type="button" @click="executeConfirmDelete()" :disabled="confirmModal.loading"
                        class="px-5 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-bold transition-all shadow-xs flex items-center gap-1.5 cursor-pointer">
                        <span x-show="confirmModal.loading" class="w-3.5 h-3.5 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                        <span>Hapus Permanen</span>
                    </button>
                </div>
            </div>
        </div>

    </div>
</x-admin-layout>
