<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm, usePage, router } from '@inertiajs/vue3';
import { Users, Info, ShieldAlert, Pencil, Save, ArrowLeft, Trash2, UploadCloud, X, FileText, AlertCircle, CheckCircle2 } from '@lucide/vue';
import CitechDashboardLayout from '@/components/CitechDashboardLayout.vue';

const props = defineProps({
    userTeam: Object,
    teamMembers: Array,
    isSubmissionOpen: Boolean,
});

const isUploadModalOpen = ref(false);
const fileInput = ref(null);
const selectedFileName = ref('');

const uploadForm = useForm({
    file_dokumen: null,
});

const openUploadModal = () => {
    selectedFileName.value = '';
    uploadForm.clearErrors();
    uploadForm.reset();
    isUploadModalOpen.value = true;
};

const closeUploadModal = () => {
    isUploadModalOpen.value = false;
};

const handleFileChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        uploadForm.file_dokumen = file;
        selectedFileName.value = file.name;
    }
};

const triggerFileInput = () => {
    fileInput.value.click();
};

const submitDocument = () => {
    uploadForm.post(route('peserta.tim.dokumen.store'), {
        onSuccess: () => {
            isUploadModalOpen.value = false;
        }
    });
};

const isConfirmCancelModalOpen = ref(false);

const openConfirmCancelModal = () => {
    isConfirmCancelModalOpen.value = true;
};

const closeConfirmCancelModal = () => {
    isConfirmCancelModalOpen.value = false;
};

const confirmCancelDocument = () => {
    router.delete(route('peserta.tim.dokumen.destroy'), {
        onSuccess: () => {
            isConfirmCancelModalOpen.value = false;
        }
    });
};

// Payment refs and methods
const isPaymentUploadModalOpen = ref(false);
const paymentFileInput = ref(null);
const selectedPaymentFileName = ref('');

const paymentForm = useForm({
    bukti_pembayaran: null,
});

const openPaymentUploadModal = () => {
    selectedPaymentFileName.value = '';
    paymentForm.clearErrors();
    paymentForm.reset();
    isPaymentUploadModalOpen.value = true;
};

const closePaymentUploadModal = () => {
    isPaymentUploadModalOpen.value = false;
};

const handlePaymentFileChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        paymentForm.bukti_pembayaran = file;
        selectedPaymentFileName.value = file.name;
    }
};

const triggerPaymentFileInput = () => {
    paymentFileInput.value.click();
};

const submitPayment = () => {
    paymentForm.post(route('peserta.tim.pembayaran.store'), {
        onSuccess: () => {
            isPaymentUploadModalOpen.value = false;
            // Open WA redirect automatically
            const waNumber = '6282264890113'; // WhatsApp Panitia
            const teamName = props.userTeam?.nama_tim || '';
            const university = props.userTeam?.universitas || '';
            const waMessage = `Halo Panitia CITECH 2026, saya perwakilan dari tim ${teamName} dari ${university} ingin mengonfirmasi bahwa kami telah melakukan pembayaran pendaftaran dan mengunggah bukti pembayaran di website. Mohon untuk diverifikasi. Terima kasih!`;
            window.open(`https://wa.me/${waNumber}?text=${encodeURIComponent(waMessage)}`, '_blank');
        }
    });
};

const isConfirmCancelPaymentModalOpen = ref(false);

const openConfirmCancelPaymentModal = () => {
    isConfirmCancelPaymentModalOpen.value = true;
};

const closeConfirmCancelPaymentModal = () => {
    isConfirmCancelPaymentModalOpen.value = false;
};

const confirmCancelPayment = () => {
    router.delete(route('peserta.tim.pembayaran.destroy'), {
        onSuccess: () => {
            isConfirmCancelPaymentModalOpen.value = false;
        }
    });
};

// Proposal submission refs and methods
const isSubmissionModalOpen = ref(false);
const isConfirmSubmissionModalOpen = ref(false);

const submissionForm = useForm({
    link_file_submission: '',
});

const openSubmissionModal = () => {
    submissionForm.clearErrors();
    submissionForm.reset();
    isSubmissionModalOpen.value = true;
};

const closeSubmissionModal = () => {
    isSubmissionModalOpen.value = false;
};

const triggerConfirmSubmission = () => {
    submissionForm.clearErrors();
    if (!submissionForm.link_file_submission) {
        submissionForm.setError('link_file_submission', 'Link Google Drive wajib diisi.');
        return;
    }
    if (!submissionForm.link_file_submission.startsWith('http://') && !submissionForm.link_file_submission.startsWith('https://')) {
        submissionForm.setError('link_file_submission', 'Format link harus berupa URL valid (harus diawali http:// atau https://).');
        return;
    }
    isConfirmSubmissionModalOpen.value = true;
};

const closeConfirmSubmissionModal = () => {
    isConfirmSubmissionModalOpen.value = false;
};

const submitProposal = () => {
    submissionForm.post(route('peserta.tim.submission.store'), {
        onSuccess: () => {
            isConfirmSubmissionModalOpen.value = false;
            isSubmissionModalOpen.value = false;
        }
    });
};

const page = usePage();
const user = computed(() => page.props.auth.user);

const isCreatingTeam = ref(false);
const isEditing = ref(false);

const form = useForm({
    nama_tim: '',
    universitas: '',
    nim_ketua: '',
    jurusan_ketua: '',
    nama_anggota1: '',
    nim_anggota1: '',
    jurusan_anggota1: '',
    nama_anggota2: '',
    nim_anggota2: '',
    jurusan_anggota2: '',
});

// Sort members to put 'ketua' first
const sortedMembers = computed(() => {
    if (!props.teamMembers) return [];
    return [...props.teamMembers].sort((a, b) => {
        if (a.role === 'ketua') return -1;
        if (b.role === 'ketua') return 1;
        return a.id_member - b.id_member;
    });
});

const mapStatus = (status) => {
    const mapping = {
        'belum_seleksi': 'Belum Seleksi',
        'penyisihan': 'Babak Penyisihan',
        'tidak_lolos_final': 'Tidak Lolos Final',
        'final': 'Babak Final',
    };
    return mapping[status] || status;
};

const startCreate = () => {
    form.reset();
    isEditing.value = false;
    isCreatingTeam.value = true;
};

const startEdit = () => {
    form.reset();
    form.nama_tim = props.userTeam.nama_tim;
    form.universitas = props.userTeam.universitas;

    const ketua = props.teamMembers.find(m => m.role === 'ketua');
    if (ketua) {
        form.nim_ketua = ketua.nim_peserta;
        form.jurusan_ketua = ketua.jurusan || '';
    }

    const anggota = props.teamMembers.filter(m => m.role === 'anggota');
    if (anggota[0]) {
        form.nama_anggota1 = anggota[0].nama_peserta;
        form.nim_anggota1 = anggota[0].nim_peserta;
        form.jurusan_anggota1 = anggota[0].jurusan || '';
    }
    if (anggota[1]) {
        form.nama_anggota2 = anggota[1].nama_peserta;
        form.nim_anggota2 = anggota[1].nim_peserta;
        form.jurusan_anggota2 = anggota[1].jurusan || '';
    }

    isEditing.value = true;
    isCreatingTeam.value = true;
};

const cancelAction = () => {
    form.reset();
    isCreatingTeam.value = false;
    isEditing.value = false;
};

const submit = () => {
    form.post(route('peserta.tim.store'), {
        onSuccess: () => {
            isCreatingTeam.value = false;
            isEditing.value = false;
        }
    });
};

const clearKetua = () => {
    form.nim_ketua = '';
    form.jurusan_ketua = '';
};

const clearAnggota1 = () => {
    form.nama_anggota1 = '';
    form.nim_anggota1 = '';
    form.jurusan_anggota1 = '';
};

const clearAnggota2 = () => {
    form.nama_anggota2 = '';
    form.nim_anggota2 = '';
    form.jurusan_anggota2 = '';
};
</script>

<template>
    <Head title="Manajemen Tim - CITECH 2026" />

    <CitechDashboardLayout activeMenu="peserta.tim" role="peserta">
        <template #header-title>
            <h2 class="text-lg font-black tracking-wide text-slate-800">Manajemen Tim</h2>
        </template>

        <div class="space-y-8 animate-fade-in-up">
            <!-- 1. FORM CREATE / EDIT TIM -->
            <div v-if="isCreatingTeam || !userTeam" class="space-y-6">
                <!-- If not currently in form view and userTeam is empty, show empty state first -->
                <div v-if="!isCreatingTeam && !userTeam" class="bg-white rounded-3xl p-8 shadow-[0_10px_35px_rgba(0,0,0,0.03)] border border-slate-100 text-center py-16 space-y-6 max-w-md mx-auto">
                    <div class="w-20 h-20 rounded-full bg-blue-50 flex items-center justify-center mx-auto text-blue-600">
                        <Users class="w-10 h-10" />
                    </div>
                    <div class="space-y-2">
                        <h4 class="text-xl font-black text-slate-900">Belum Ada Data Tim</h4>
                        <p class="text-xs font-bold text-slate-400 leading-relaxed">
                            Anda belum mendaftarkan kelompok tim Anda. Silakan isi formulir pendaftaran tim baru untuk dapat berpartisipasi.
                        </p>
                    </div>
                    <button 
                        @click="startCreate" 
                        class="px-8 py-3.5 bg-[#1e4d8c] hover:bg-[#153a6b] text-white font-extrabold text-xs rounded-xl transition shadow-md hover:shadow-lg hover:shadow-blue-500/10 uppercase tracking-wider"
                    >
                        Buat Tim Baru
                    </button>
                </div>

                <!-- Create/Edit Form UI -->
                <div v-else class="max-w-3xl mx-auto space-y-6">
                    <div class="flex items-center justify-between">
                        <h3 class="text-2xl font-black text-slate-900 tracking-tight">
                            {{ isEditing ? 'Edit Data Tim' : 'Buat Data Tim' }}
                        </h3>
                        <button 
                            @click="cancelAction" 
                            class="inline-flex items-center space-x-1 px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-bold transition"
                        >
                            <ArrowLeft class="w-3.5 h-3.5" />
                            <span>Kembali</span>
                        </button>
                    </div>

                    <div class="bg-white rounded-3xl p-6 md:p-8 shadow-[0_10px_35px_rgba(0,0,0,0.03)] border border-slate-100 space-y-8">
                        
                        <!-- Data Tim Section -->
                        <div class="space-y-4">
                            <h4 class="text-sm font-black text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-2">Data Tim</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="space-y-1.5">
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider block">Nama Tim</label>
                                    <input 
                                        type="text" 
                                        v-model="form.nama_tim" 
                                        placeholder="Masukkan nama tim" 
                                        class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#1e4d8c] text-sm font-semibold" 
                                    />
                                    <div v-if="form.errors.nama_tim" class="text-xs text-red-500 font-bold mt-1">{{ form.errors.nama_tim }}</div>
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider block">Universitas Asal</label>
                                    <input 
                                        type="text" 
                                        v-model="form.universitas" 
                                        placeholder="Masukkan universitas asal tim" 
                                        class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#1e4d8c] text-sm font-semibold" 
                                    />
                                    <div v-if="form.errors.universitas" class="text-xs text-red-500 font-bold mt-1">{{ form.errors.universitas }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Data Ketua Section (Automatically creator, no Name edit) -->
                        <div class="space-y-4 pt-4 border-t border-slate-100/80">
                            <div class="flex justify-between items-center">
                                <h4 class="text-sm font-black text-slate-800 uppercase tracking-wider">Data Ketua (Otomatis Anda)</h4>
                                <button 
                                    type="button" 
                                    @click="clearKetua" 
                                    class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-600 rounded-lg text-[10px] font-extrabold uppercase tracking-wide transition border border-red-100"
                                >
                                    <Trash2 class="w-3 h-3" />
                                    <span>Hapus Data</span>
                                </button>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="space-y-1.5">
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider block">Nama Ketua</label>
                                    <input 
                                        type="text" 
                                        :value="user.name" 
                                        disabled 
                                        class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 text-slate-500 text-sm font-semibold cursor-not-allowed" 
                                    />
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider block">NIM</label>
                                    <input 
                                        type="text" 
                                        v-model="form.nim_ketua" 
                                        placeholder="Masukkan NIM ketua" 
                                        class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#1e4d8c] text-sm font-semibold" 
                                    />
                                    <div v-if="form.errors.nim_ketua" class="text-xs text-red-500 font-bold mt-1">{{ form.errors.nim_ketua }}</div>
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider block">Jurusan</label>
                                    <input 
                                        type="text" 
                                        v-model="form.jurusan_ketua" 
                                        placeholder="Masukkan jurusan ketua" 
                                        class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#1e4d8c] text-sm font-semibold" 
                                    />
                                    <div v-if="form.errors.jurusan_ketua" class="text-xs text-red-500 font-bold mt-1">{{ form.errors.jurusan_ketua }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Data Anggota 1 Section (Required) -->
                        <div class="space-y-4 pt-4 border-t border-slate-100/80">
                            <div class="flex justify-between items-center">
                                <h4 class="text-sm font-black text-slate-800 uppercase tracking-wider">Data Anggota 1 (Wajib)</h4>
                                <button 
                                    type="button" 
                                    @click="clearAnggota1" 
                                    class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-600 rounded-lg text-[10px] font-extrabold uppercase tracking-wide transition border border-red-100"
                                >
                                    <Trash2 class="w-3 h-3" />
                                    <span>Hapus Data</span>
                                </button>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="space-y-1.5">
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider block">Nama Anggota 1</label>
                                    <input 
                                        type="text" 
                                        v-model="form.nama_anggota1" 
                                        placeholder="Masukkan nama anggota 1" 
                                        class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#1e4d8c] text-sm font-semibold" 
                                    />
                                    <div v-if="form.errors.nama_anggota1" class="text-xs text-red-500 font-bold mt-1">{{ form.errors.nama_anggota1 }}</div>
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider block">NIM</label>
                                    <input 
                                        type="text" 
                                        v-model="form.nim_anggota1" 
                                        placeholder="Masukkan NIM anggota 1" 
                                        class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#1e4d8c] text-sm font-semibold" 
                                    />
                                    <div v-if="form.errors.nim_anggota1" class="text-xs text-red-500 font-bold mt-1">{{ form.errors.nim_anggota1 }}</div>
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider block">Jurusan</label>
                                    <input 
                                        type="text" 
                                        v-model="form.jurusan_anggota1" 
                                        placeholder="Masukkan jurusan anggota 1" 
                                        class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#1e4d8c] text-sm font-semibold" 
                                    />
                                    <div v-if="form.errors.jurusan_anggota1" class="text-xs text-red-500 font-bold mt-1">{{ form.errors.jurusan_anggota1 }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Data Anggota 2 Section (Optional) -->
                        <div class="space-y-4 pt-4 border-t border-slate-100/80">
                            <div class="flex justify-between items-center">
                                <h4 class="text-sm font-black text-slate-800 uppercase tracking-wider">Data Anggota 2 (Opsional)</h4>
                                <button 
                                    type="button" 
                                    @click="clearAnggota2" 
                                    class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-600 rounded-lg text-[10px] font-extrabold uppercase tracking-wide transition border border-red-100"
                                >
                                    <Trash2 class="w-3 h-3" />
                                    <span>Hapus Data</span>
                                </button>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="space-y-1.5">
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider block">Nama Anggota 2</label>
                                    <input 
                                        type="text" 
                                        v-model="form.nama_anggota2" 
                                        placeholder="Masukkan nama anggota 2" 
                                        class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#1e4d8c] text-sm font-semibold" 
                                    />
                                    <div v-if="form.errors.nama_anggota2" class="text-xs text-red-500 font-bold mt-1">{{ form.errors.nama_anggota2 }}</div>
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider block">NIM</label>
                                    <input 
                                        type="text" 
                                        v-model="form.nim_anggota2" 
                                        placeholder="Masukkan NIM anggota 2" 
                                        class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#1e4d8c] text-sm font-semibold" 
                                    />
                                    <div v-if="form.errors.nim_anggota2" class="text-xs text-red-500 font-bold mt-1">{{ form.errors.nim_anggota2 }}</div>
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider block">Jurusan</label>
                                    <input 
                                        type="text" 
                                        v-model="form.jurusan_anggota2" 
                                        placeholder="Masukkan jurusan anggota 2" 
                                        class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#1e4d8c] text-sm font-semibold" 
                                    />
                                    <div v-if="form.errors.jurusan_anggota2" class="text-xs text-red-500 font-bold mt-1">{{ form.errors.jurusan_anggota2 }}</div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center justify-end space-x-3">
                        <button 
                            type="button" 
                            @click="cancelAction" 
                            class="px-6 py-3 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs rounded-xl transition border border-slate-200 shadow-sm"
                        >
                            Batal
                        </button>
                        <button 
                            type="button" 
                            @click="submit" 
                            :disabled="form.processing"
                            class="px-8 py-3 bg-[#1b2a4a] hover:bg-[#15233d] text-white font-bold text-xs rounded-xl transition shadow-md flex items-center space-x-2"
                        >
                            <Save class="w-3.5 h-3.5" />
                            <span>{{ form.processing ? 'Menyimpan...' : 'Simpan' }}</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- 2. DETAIL TIM VIEW (WHEN REGISTERED) -->
            <div v-else class="space-y-6">
                <!-- Header Title & Selection Status -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white rounded-3xl p-6 shadow-[0_10px_35px_rgba(0,0,0,0.03)] border border-slate-100">
                    <div class="min-w-0 flex-1">
                        <h1 class="text-xl md:text-2xl font-black text-blue-900 tracking-tight break-words">{{ userTeam.nama_tim }}</h1>
                        <p class="text-xs font-bold text-slate-400 mt-1 block uppercase tracking-wider break-words">{{ userTeam.universitas }}</p>
                    </div>
                    <div class="flex items-center space-x-2.5 flex-shrink-0">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Status Kelayakan:</span>
                        <span 
                            class="inline-block text-xs font-black uppercase tracking-wider px-4 py-1.5 rounded-full border shadow-sm"
                            :class="[
                                userTeam.status_seleksi === 'final' 
                                    ? 'bg-green-50 text-green-700 border-green-200' 
                                    : (userTeam.status_seleksi === 'tidak_lolos_final' ? 'bg-red-50 text-red-700 border-red-200' : 'bg-blue-50 text-blue-700 border-blue-100')
                            ]"
                        >
                            {{ mapStatus(userTeam.status_seleksi) }}
                        </span>
                    </div>
                </div>

                <!-- Grid Details layout -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                    
                    <!-- Left Column (Members Details) -->
                    <div class="lg:col-span-7 space-y-6">
                        <!-- Edit Button -->
                        <div class="flex">
                            <button 
                                @click="userTeam.dokumen_registrasi?.status_registrasi !== 'berhasil' ? startEdit() : null" 
                                :disabled="userTeam.dokumen_registrasi?.status_registrasi === 'berhasil'"
                                :class="[
                                    'inline-flex items-center space-x-2 px-5 py-2.5 rounded-xl text-xs font-black transition shadow-sm',
                                    userTeam.dokumen_registrasi?.status_registrasi === 'berhasil'
                                        ? 'bg-slate-200 text-slate-400 cursor-not-allowed border border-slate-300/50 shadow-none'
                                        : 'bg-blue-900 hover:bg-blue-800 text-white hover:scale-[1.02]'
                                ]"
                                :title="userTeam.dokumen_registrasi?.status_registrasi === 'berhasil' ? 'Data tim tidak dapat diubah karena berkas persyaratan pendaftaran sudah disetujui' : ''"
                            >
                                <Pencil class="w-3.5 h-3.5" />
                                <span>Edit Data Tim</span>
                            </button>
                        </div>

                        <!-- Card Members details -->
                        <div class="bg-white rounded-3xl p-6 md:p-8 shadow-[0_10px_35px_rgba(0,0,0,0.03)] border border-slate-100 divide-y divide-slate-100 space-y-6">
                            
                            <div 
                                v-for="(member, idx) in sortedMembers" 
                                :key="member.id_member" 
                                class="pt-6 first:pt-0 space-y-3"
                            >
                                <div class="flex justify-between items-center">
                                    <h4 class="text-xs font-black text-slate-400 uppercase tracking-wider">
                                        {{ member.role === 'ketua' ? 'Nama Ketua' : `Nama Anggota ${idx}` }}
                                    </h4>
                                    <span 
                                        class="text-[9px] font-black uppercase tracking-wider px-2 py-0.5 rounded border shadow-sm"
                                        :class="[
                                            member.role === 'ketua' 
                                                ? 'bg-amber-50 text-amber-700 border-amber-200' 
                                                : 'bg-slate-100 text-slate-600 border-slate-200'
                                        ]"
                                    >
                                        {{ member.role }}
                                    </span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 bg-slate-50/50 p-4 rounded-2xl border border-slate-100">
                                    <div class="space-y-0.5">
                                        <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Nama Lengkap</span>
                                        <p class="text-sm font-extrabold text-slate-800">{{ member.nama_peserta }}</p>
                                    </div>
                                    <div class="space-y-0.5">
                                        <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">NIM / Identitas</span>
                                        <p class="text-sm font-extrabold text-slate-800">{{ member.nim_peserta }}</p>
                                    </div>
                                    <div class="space-y-0.5">
                                        <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Program Studi / Jurusan</span>
                                        <p class="text-sm font-extrabold text-slate-800">{{ member.jurusan || '-' }}</p>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Right Column (Requirements & Payment) -->
                    <div class="lg:col-span-5 space-y-6">
                        
                        <!-- Card: Dokumen Persyaratan -->
                        <div class="bg-white rounded-3xl p-6 md:p-8 shadow-[0_10px_35px_rgba(0,0,0,0.03)] border border-slate-100 space-y-5">
                            <h4 class="text-sm font-black text-slate-800 uppercase tracking-wider text-center border-b border-slate-100 pb-3">Persyaratan Pendaftaran</h4>
                            
                            <div class="bg-slate-50 p-5 rounded-2xl border border-slate-100 space-y-4">
                                <ol class="list-decimal list-inside space-y-2 text-xs font-bold text-slate-600">
                                    <li>KTM atau Surat Aktif</li>
                                    <li>Twibbon</li>
                                    <li>Bukti Follow</li>
                                </ol>
                                <p class="text-[10px] text-blue-600 font-extrabold text-center block pt-2">* Kumpulkan menjadi 1 file dalam format PDF</p>
                            </div>

                            <!-- State 1: No Upload / Null -->
                            <div v-if="!userTeam.dokumen_registrasi" class="space-y-3">
                                <button 
                                    @click="openUploadModal"
                                    class="w-full py-3 bg-[#3769a6] hover:bg-[#2b5487] text-white rounded-xl text-xs font-black transition shadow-sm hover:scale-[1.01]"
                                >
                                    Upload Persyaratan
                                </button>
                            </div>

                            <!-- State 2: Pending -->
                            <div v-else-if="userTeam.dokumen_registrasi.status_registrasi === 'pending'" class="text-center space-y-4 pt-2">
                                <div class="space-y-1">
                                    <span class="text-xs font-black tracking-widest text-slate-400 block uppercase">Status Berkas:</span>
                                    <h4 class="text-2xl font-black text-amber-500 uppercase tracking-wider">PENDING</h4>
                                </div>
                                <button 
                                    @click="openConfirmCancelModal"
                                    class="w-full py-3 bg-red-600 hover:bg-red-700 text-white rounded-xl text-xs font-black transition shadow-sm hover:scale-[1.01]"
                                >
                                    Batalkan
                                </button>
                            </div>

                            <!-- State 3: Approved / Berhasil -->
                            <div v-else-if="userTeam.dokumen_registrasi.status_registrasi === 'berhasil'" class="text-center space-y-4 pt-2">
                                <div class="space-y-1">
                                    <span class="text-xs font-black tracking-widest text-slate-400 block uppercase">Status Berkas:</span>
                                    <h4 class="text-2xl font-black text-green-600 uppercase tracking-wider">BERHASIL</h4>
                                </div>
                                <p class="text-xs font-bold text-slate-500 leading-relaxed max-w-xs mx-auto">
                                    Bukti berhasil terkirim. Lakukan yang terbaik untuk di tahap selanjutnya, semoga berhasil!
                                </p>
                            </div>

                            <!-- State 4: Rejected / Ditolak -->
                            <div v-else-if="userTeam.dokumen_registrasi.status_registrasi === 'ditolak'" class="text-center space-y-4 pt-2">
                                <div class="space-y-1">
                                    <span class="text-xs font-black tracking-widest text-slate-400 block uppercase">Status Berkas:</span>
                                    <h4 class="text-2xl font-black text-red-600 uppercase tracking-wider">DITOLAK</h4>
                                </div>
                                
                                <!-- Catatan Admin -->
                                <div class="p-4 bg-red-50/80 border border-red-200/60 rounded-2xl text-left">
                                    <span class="text-[9px] font-black text-red-600 uppercase tracking-wider block">Catatan Admin:</span>
                                    <p class="text-xs font-bold text-red-800 mt-1 leading-relaxed break-words">
                                        {{ userTeam.dokumen_registrasi.catatan_registrasi || 'Tidak ada catatan alasan penolakan.' }}
                                    </p>
                                </div>

                                <button 
                                    @click="openUploadModal"
                                    class="w-full py-3 bg-[#3769a6] hover:bg-[#2b5487] text-white rounded-xl text-xs font-black transition shadow-sm hover:scale-[1.01]"
                                >
                                    Ganti File
                                </button>
                            </div>
                        </div>

                        <!-- Card: Bukti Pembayaran -->
                        <div class="bg-white rounded-3xl p-6 md:p-8 shadow-[0_10px_35px_rgba(0,0,0,0.03)] border border-slate-100 space-y-5">
                            <h4 class="text-sm font-black text-slate-800 uppercase tracking-wider text-center border-b border-slate-100 pb-3">Bukti Pembayaran</h4>
                            
                            <!-- State 1: No Payment Record Uploaded -->
                            <div v-if="!userTeam.pembayaran" class="space-y-5">
                                <div class="flex justify-center py-2">
                                    <img src="/assets/qris-citech.jpeg" alt="QRIS CITECH" class="w-72 h-auto object-contain border border-slate-200 p-2 rounded-2xl bg-white shadow-sm" />
                                </div>

                                <!-- Panduan Pembayaran (Sesuai Desain Foto) -->
                                <div class="bg-slate-50/80 border border-slate-100/80 rounded-2xl p-5 space-y-3.5 text-left">
                                    <h5 class="text-xs font-black text-slate-800 flex items-center space-x-2">
                                        <Info class="w-4 h-4 text-blue-600 flex-shrink-0" />
                                        <span>Panduan Alur Pembayaran:</span>
                                    </h5>
                                    <ol class="list-decimal list-inside text-[11px] font-bold text-slate-600 space-y-2">
                                        <li>Peserta Membayar melalui QRIS yang telah disediakan</li>
                                        <li class="pl-0">
                                            Biaya Pendaftaran Peserta:
                                            <ul class="list-disc list-inside pl-4 mt-1 space-y-0.5 text-slate-500 font-semibold">
                                                <li>Batch 1: Rp 60.000</li>
                                                <li>Batch 2: Rp 80.000</li>
                                            </ul>
                                        </li>
                                        <li>Peserta Mengupload Bukti Pembayaran Melalui Button yang sudah disediakan</li>
                                        <li>Peserta Otomatis Mengirimkan Pesan Konfirmasi Ke Nomor Whatsapp Panitia</li>
                                        <li>Panitia Mengonfirmasi Pembayaran Peserta</li>
                                        <li>Pembayaran Berhasil</li>
                                    </ol>
                                    <p class="text-[10px] text-blue-600 font-extrabold text-center mt-2">
                                        * Mohon pastikan nominal pembayaran sesuai dengan batch yang sedang berjalan.
                                    </p>
                                </div>

                                <button 
                                    @click="openPaymentUploadModal"
                                    class="w-full py-3 bg-[#3769a6] hover:bg-[#2b5487] text-white rounded-xl text-xs font-black transition shadow-sm hover:scale-[1.01]"
                                >
                                    Upload Bukti Pembayaran
                                </button>
                            </div>

                            <!-- State 2: Payment Pending -->
                            <div v-else-if="userTeam.pembayaran.status_pembayaran === 'pending'" class="text-center space-y-4 pt-2">
                                <div class="space-y-1">
                                    <span class="text-xs font-black tracking-widest text-slate-400 block uppercase">Status Pembayaran:</span>
                                    <h4 class="text-2xl font-black text-amber-500 uppercase tracking-wider">PENDING</h4>
                                </div>
                                <p class="text-xs font-bold text-slate-500 leading-relaxed px-4">
                                    Bukti pembayaran Anda sedang ditinjau oleh panitia. Mohon tunggu proses konfirmasi.
                                </p>
                                <button 
                                    @click="openConfirmCancelPaymentModal"
                                    class="w-full py-3 bg-red-600 hover:bg-red-700 text-white rounded-xl text-xs font-black transition shadow-sm hover:scale-[1.01]"
                                >
                                    Batalkan Unggahan
                                </button>
                            </div>

                            <!-- State 3: Payment Approved / Berhasil -->
                            <div v-else-if="userTeam.pembayaran.status_pembayaran === 'berhasil'" class="text-center space-y-4 pt-2">
                                <div class="space-y-1">
                                    <span class="text-xs font-black tracking-widest text-slate-400 block uppercase">Status Pembayaran:</span>
                                    <h4 class="text-2xl font-black text-green-600 uppercase tracking-wider">BERHASIL</h4>
                                </div>
                                <p class="text-xs font-bold text-slate-500 leading-relaxed px-4">
                                    Selamat! Pembayaran pendaftaran tim Anda telah berhasil diverifikasi oleh panitia. Status tim Anda sekarang terdaftar untuk babak penyisihan.
                                </p>
                            </div>

                            <!-- State 4: Payment Rejected / Ditolak -->
                            <div v-else-if="userTeam.pembayaran.status_pembayaran === 'ditolak'" class="text-center space-y-4 pt-2">
                                <div class="space-y-1">
                                    <span class="text-xs font-black tracking-widest text-slate-400 block uppercase">Status Pembayaran:</span>
                                    <h4 class="text-2xl font-black text-red-600 uppercase tracking-wider">DITOLAK</h4>
                                </div>
                                
                                <!-- Catatan Admin -->
                                <div class="p-4 bg-red-50/80 border border-red-200/60 rounded-2xl text-left">
                                    <span class="text-[9px] font-black text-red-600 uppercase tracking-wider block">Catatan Admin:</span>
                                    <p class="text-xs font-bold text-red-800 mt-1 leading-relaxed break-words">
                                        {{ userTeam.pembayaran.catatan_pembayaran || 'Tidak ada catatan alasan penolakan.' }}
                                    </p>
                                </div>

                                <button 
                                    @click="openPaymentUploadModal"
                                    class="w-full py-3 bg-[#3769a6] hover:bg-[#2b5487] text-white rounded-xl text-xs font-black transition shadow-sm hover:scale-[1.01]"
                                >
                                    Ganti Bukti Pembayaran
                                </button>
                            </div>
                        </div>

                        <!-- Card: Gabung Grup WhatsApp -->
                        <div class="bg-white rounded-3xl p-6 md:p-8 shadow-[0_10px_35px_rgba(0,0,0,0.03)] border border-slate-100 space-y-5">
                            <h4 class="text-sm font-black text-slate-800 uppercase tracking-wider text-center border-b border-slate-100 pb-3">Grup Peserta</h4>
                            <p class="text-xs text-slate-500 text-center leading-relaxed">
                                Silakan bergabung ke grup koordinasi resmi peserta CITECH untuk mendapatkan pembaruan informasi terkini.
                            </p>
                            <a 
                                href="#" 
                                class="block w-full py-3 text-center bg-[#3769a6] hover:bg-[#2b5487] text-white rounded-xl text-xs font-black transition shadow-sm hover:scale-[1.01]"
                            >
                                Gabung Grup Peserta
                            </a>
                        </div>

                        <!-- Card: Upload Proposal -->
                        <div class="bg-white rounded-3xl p-6 md:p-8 shadow-[0_10px_35px_rgba(0,0,0,0.03)] border border-slate-100 space-y-5">
                            <h4 class="text-sm font-black text-slate-800 uppercase tracking-wider text-center border-b border-slate-100 pb-3">Proposal & Orisinalitas</h4>
                            
                            <!-- State 1: Already Submitted -->
                            <div v-if="userTeam.submission" class="text-center space-y-4 pt-2">
                                <div class="space-y-1">
                                    <span class="text-xs font-black tracking-widest text-slate-400 block uppercase">Status Pengumpulan:</span>
                                    <h4 class="text-2xl font-black text-green-600 uppercase tracking-wider">SUDAH DIKUMPULKAN</h4>
                                </div>
                                <p class="text-xs font-bold text-slate-500 leading-relaxed px-4">
                                    Proposal dan Surat Orisinalitas tim Anda telah berhasil dikumpulkan. Pengumpulan hanya dilakukan sekali dan tidak dapat dibatalkan atau diubah.
                                </p>
                                <!-- Display link drive -->
                                <div class="p-4 bg-slate-50 border border-slate-100 rounded-2xl flex flex-col items-center space-y-2">
                                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-wider">Link Google Drive Anda:</span>
                                    <a 
                                        :href="userTeam.submission.link_file_submission" 
                                        target="_blank" 
                                        class="inline-flex items-center space-x-1.5 text-blue-600 hover:text-blue-800 transition text-xs font-extrabold break-all px-3 py-1.5 bg-blue-50/50 hover:bg-blue-50 border border-blue-100/50 rounded-xl"
                                    >
                                        <FileText class="w-4 h-4" />
                                        <span>Buka Link Drive</span>
                                        <ExternalLink class="w-3 h-3" />
                                    </a>
                                </div>
                            </div>

                            <!-- State 2: Not Submitted & Deadline Not Over -->
                            <div v-else-if="isSubmissionOpen" class="space-y-4">
                                <p class="text-xs text-slate-500 text-center leading-relaxed">
                                    Silakan kumpulkan proposal dan surat orisinalitas tim Anda dalam satu Link Google Drive. Harap pastikan link dapat diakses oleh panitia (Public).
                                </p>
                                <button 
                                    @click="userTeam.status_seleksi === 'penyisihan' ? openSubmissionModal() : null"
                                    :disabled="userTeam.status_seleksi !== 'penyisihan'"
                                    :class="[
                                        'w-full py-3 text-xs font-black transition shadow-sm rounded-xl',
                                        userTeam.status_seleksi === 'penyisihan'
                                            ? 'bg-[#3769a6] hover:bg-[#2b5487] text-white hover:scale-[1.01]'
                                            : 'bg-slate-200 text-slate-400 cursor-not-allowed border border-slate-300/50 shadow-none'
                                    ]"
                                    :title="userTeam.status_seleksi !== 'penyisihan' ? 'Anda hanya dapat mengumpulkan proposal setelah verifikasi berkas dan pembayaran disetujui' : ''"
                                >
                                    Kumpulkan Link Proposal
                                </button>
                            </div>

                            <!-- State 3: Not Submitted & Deadline Over -->
                            <div v-else class="text-center space-y-4 pt-2">
                                <div class="space-y-1">
                                    <span class="text-xs font-black tracking-widest text-slate-400 block uppercase">Status Pengumpulan:</span>
                                    <h4 class="text-2xl font-black text-red-600 uppercase tracking-wider">BATAS WAKTU HABIS</h4>
                                </div>
                                <p class="text-xs font-bold text-slate-500 leading-relaxed px-4">
                                    Mohon maaf, batas waktu pengumpulan proposal dan surat orisinalitas (Batch 2) telah berakhir.
                                </p>
                            </div>
                        </div>

                    </div>

                </div>
            </div>
        </div>

        <!-- Upload Modal Dialog -->
        <div v-if="isUploadModalOpen" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4 text-center">
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-slate-900/5 backdrop-blur-md transition-opacity" @click="closeUploadModal"></div>

                <!-- Modal Content Wrapper -->
                <div class="bg-white rounded-3xl overflow-hidden shadow-[0_25px_50px_-12px_rgba(0,0,0,0.25)] border border-slate-100 w-full max-w-lg my-8 inline-block text-left align-middle relative z-10 animate-fade-in-up transform transition-all">
                    
                    <!-- Header -->
                    <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                        <div class="space-y-1">
                            <h3 class="text-lg font-black text-slate-900 leading-tight">Unggah Berkas Persyaratan</h3>
                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Format Berkas wajib PDF (Maksimal 5MB)</p>
                        </div>
                        <button @click="closeUploadModal" class="text-slate-400 hover:text-slate-600 transition p-1 hover:bg-slate-100 rounded-lg">
                            <X class="w-5 h-5" />
                        </button>
                    </div>

                    <!-- Body / Upload Form -->
                    <form @submit.prevent="submitDocument" class="p-6 space-y-6 text-left">
                        
                        <!-- Info Box -->
                        <div class="bg-slate-50 border border-slate-100 rounded-2xl p-4 flex items-start space-x-3">
                            <Info class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" />
                            <div class="space-y-1">
                                <h5 class="text-xs font-black text-slate-800">Berkas yang Harus Dikumpulkan:</h5>
                                <ul class="list-disc list-inside text-[11px] font-bold text-slate-500 space-y-0.5">
                                    <li>KTM (Kartu Tanda Mahasiswa) / Surat Keterangan Aktif</li>
                                    <li>Screenshot Twibbon di media sosial</li>
                                    <li>Screenshot Bukti Follow akun penyelenggara</li>
                                </ul>
                                <p class="text-[10px] text-[#1e4d8c] font-extrabold mt-1.5">* Ketiga berkas di atas wajib digabung menjadi 1 file PDF.</p>
                            </div>
                        </div>

                        <!-- Drop / Select Area -->
                        <div 
                            @click="triggerFileInput"
                            class="border-2 border-dashed border-slate-200 hover:border-[#3769a6] rounded-2xl p-8 flex flex-col items-center justify-center cursor-pointer transition bg-slate-50/50 hover:bg-blue-50/10 space-y-3"
                        >
                            <input 
                                type="file" 
                                ref="fileInput" 
                                @change="handleFileChange" 
                                accept=".pdf" 
                                class="hidden" 
                            />
                            
                            <div class="w-12 h-12 rounded-full bg-blue-50 flex items-center justify-center text-[#3769a6]">
                                <UploadCloud class="w-6 h-6" />
                            </div>

                            <div class="text-center space-y-1">
                                <p class="text-xs font-extrabold text-slate-800">
                                    {{ selectedFileName ? 'Ganti File PDF terpilih' : 'Pilih Berkas PDF' }}
                                </p>
                                <p class="text-[10px] font-bold text-slate-400">Klik di sini untuk menjelajahi file</p>
                            </div>
                        </div>

                        <!-- Display selected file -->
                        <div v-if="selectedFileName" class="flex items-center justify-between p-3.5 bg-blue-50/30 border border-blue-100/50 rounded-xl">
                            <div class="flex items-center space-x-3 min-w-0">
                                <FileText class="w-5 h-5 text-[#3769a6] flex-shrink-0" />
                                <span class="text-xs font-bold text-slate-700 truncate">{{ selectedFileName }}</span>
                            </div>
                            <span class="text-[10px] font-extrabold text-blue-600 bg-blue-50 px-2 py-0.5 rounded border border-blue-100">Terpilih</span>
                        </div>

                        <!-- Errors -->
                        <div v-if="uploadForm.errors.file_dokumen" class="flex items-center space-x-2 p-3 bg-red-50 border border-red-100 rounded-xl text-red-600">
                            <AlertCircle class="w-4 h-4 flex-shrink-0" />
                            <span class="text-xs font-bold">{{ uploadForm.errors.file_dokumen }}</span>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center justify-end space-x-3 pt-2">
                            <button 
                                type="button" 
                                @click="closeUploadModal" 
                                class="px-5 py-2.5 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs rounded-xl transition border border-slate-200"
                            >
                                Batal
                            </button>
                            <button 
                                type="submit" 
                                :disabled="uploadForm.processing || !uploadForm.file_dokumen"
                                class="px-6 py-2.5 bg-[#1b2a4a] hover:bg-[#15233d] text-white font-black text-xs rounded-xl transition shadow-md disabled:opacity-50 disabled:cursor-not-allowed flex items-center space-x-2"
                            >
                                <UploadCloud class="w-3.5 h-3.5" />
                                <span>{{ uploadForm.processing ? 'Mengunggah...' : 'Kirim Persyaratan' }}</span>
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>

        <!-- Confirm Cancel Modal -->
        <div v-if="isConfirmCancelModalOpen" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4 text-center">
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-slate-900/5 backdrop-blur-md transition-opacity" @click="closeConfirmCancelModal"></div>

                <!-- Modal Content Wrapper -->
                <div class="bg-white rounded-3xl overflow-hidden shadow-[0_25px_50px_-12px_rgba(0,0,0,0.15)] border border-slate-100 w-full max-w-md my-8 inline-block text-left align-middle relative z-10 animate-fade-in-up transform transition-all">
                    <div class="p-6 space-y-6">
                        
                        <!-- Icon & Title -->
                        <div class="flex items-start space-x-4">
                            <div class="w-10 h-10 rounded-full bg-red-50 flex items-center justify-center text-red-600 flex-shrink-0">
                                <AlertCircle class="w-5 h-5" />
                            </div>
                            <div class="space-y-1 min-w-0">
                                <h3 class="text-base font-black text-slate-900">Batalkan Unggahan Berkas?</h3>
                                <p class="text-xs font-bold text-slate-500 leading-relaxed">
                                    Apakah Anda yakin ingin membatalkan pengiriman dokumen persyaratan ini? Tindakan ini akan menghapus berkas PDF yang telah diunggah secara permanen.
                                </p>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center justify-end space-x-3">
                            <button 
                                type="button" 
                                @click="closeConfirmCancelModal" 
                                class="px-5 py-2.5 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs rounded-xl transition border border-slate-200"
                            >
                                Tidak, Kembali
                            </button>
                            <button 
                                type="button" 
                                @click="confirmCancelDocument"
                                class="px-6 py-2.5 bg-red-600 hover:bg-red-700 text-white font-black text-xs rounded-xl transition shadow-md flex items-center space-x-1.5"
                            >
                                <Trash2 class="w-3.5 h-3.5" />
                                <span>Ya, Batalkan</span>
                            </button>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Upload Modal Dialog -->
        <div v-if="isPaymentUploadModalOpen" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4 text-center">
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-slate-900/5 backdrop-blur-md transition-opacity" @click="closePaymentUploadModal"></div>

                <!-- Modal Content Wrapper -->
                <div class="bg-white rounded-3xl overflow-hidden shadow-[0_25px_50px_-12px_rgba(0,0,0,0.25)] border border-slate-100 w-full max-w-lg my-8 inline-block text-left align-middle relative z-10 animate-fade-in-up transform transition-all">
                    
                    <!-- Header -->
                    <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                        <div class="space-y-1">
                            <h3 class="text-lg font-black text-slate-900 leading-tight">Unggah Bukti Pembayaran</h3>
                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Format Berkas: JPG, PNG, atau PDF (Maksimal 5MB)</p>
                        </div>
                        <button @click="closePaymentUploadModal" class="text-slate-400 hover:text-slate-600 transition p-1 hover:bg-slate-100 rounded-lg">
                            <X class="w-5 h-5" />
                        </button>
                    </div>

                    <!-- Body / Upload Form -->
                    <form @submit.prevent="submitPayment" class="p-6 space-y-6 text-left">
                        
                        <!-- Drop / Select Area -->
                        <div 
                            @click="triggerPaymentFileInput"
                            class="border-2 border-dashed border-slate-200 hover:border-[#3769a6] rounded-2xl p-8 flex flex-col items-center justify-center cursor-pointer transition bg-slate-50/50 hover:bg-blue-50/10 space-y-3"
                        >
                            <input 
                                type="file" 
                                ref="paymentFileInput" 
                                @change="handlePaymentFileChange" 
                                accept="image/*,.pdf" 
                                class="hidden" 
                            />
                            
                            <div class="w-12 h-12 rounded-full bg-blue-50 flex items-center justify-center text-[#3769a6]">
                                <UploadCloud class="w-6 h-6" />
                            </div>

                            <div class="text-center space-y-1">
                                <p class="text-xs font-extrabold text-slate-800">
                                    {{ selectedPaymentFileName ? 'Ganti Bukti Pembayaran terpilih' : 'Pilih Bukti Pembayaran' }}
                                </p>
                                <p class="text-[10px] font-bold text-slate-400">Klik di sini untuk menjelajahi file</p>
                            </div>
                        </div>

                        <!-- Display selected file -->
                        <div v-if="selectedPaymentFileName" class="flex items-center justify-between p-3.5 bg-blue-50/30 border border-blue-100/50 rounded-xl">
                            <div class="flex items-center space-x-3 min-w-0">
                                <FileText class="w-5 h-5 text-[#3769a6] flex-shrink-0" />
                                <span class="text-xs font-bold text-slate-700 truncate">{{ selectedPaymentFileName }}</span>
                            </div>
                            <span class="text-[10px] font-extrabold text-blue-600 bg-blue-50 px-2 py-0.5 rounded border border-blue-100">Terpilih</span>
                        </div>

                        <!-- Errors -->
                        <div v-if="paymentForm.errors.bukti_pembayaran" class="flex items-center space-x-2 p-3 bg-red-50 border border-red-100 rounded-xl text-red-600">
                            <AlertCircle class="w-4 h-4 flex-shrink-0" />
                            <span class="text-xs font-bold">{{ paymentForm.errors.bukti_pembayaran }}</span>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center justify-end space-x-3 pt-2">
                            <button 
                                type="button" 
                                @click="closePaymentUploadModal" 
                                class="px-5 py-2.5 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs rounded-xl transition border border-slate-200"
                            >
                                Batal
                            </button>
                            <button 
                                type="submit" 
                                :disabled="paymentForm.processing || !paymentForm.bukti_pembayaran"
                                class="px-6 py-2.5 bg-[#1b2a4a] hover:bg-[#15233d] text-white font-black text-xs rounded-xl transition shadow-md disabled:opacity-50 disabled:cursor-not-allowed flex items-center space-x-2"
                            >
                                <UploadCloud class="w-3.5 h-3.5" />
                                <span>{{ paymentForm.processing ? 'Mengunggah...' : 'Kirim Bukti' }}</span>
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>

        <!-- Confirm Cancel Payment Modal -->
        <div v-if="isConfirmCancelPaymentModalOpen" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4 text-center">
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-slate-900/5 backdrop-blur-md transition-opacity" @click="closeConfirmCancelPaymentModal"></div>

                <!-- Modal Content Wrapper -->
                <div class="bg-white rounded-3xl overflow-hidden shadow-[0_25px_50px_-12px_rgba(0,0,0,0.15)] border border-slate-100 w-full max-w-md my-8 inline-block text-left align-middle relative z-10 animate-fade-in-up transform transition-all">
                    <div class="p-6 space-y-6">
                        
                        <!-- Icon & Title -->
                        <div class="flex items-start space-x-4">
                            <div class="w-10 h-10 rounded-full bg-red-50 flex items-center justify-center text-red-600 flex-shrink-0">
                                <AlertCircle class="w-5 h-5" />
                            </div>
                            <div class="space-y-1 min-w-0">
                                <h3 class="text-base font-black text-slate-900">Batalkan Unggahan Pembayaran?</h3>
                                <p class="text-xs font-bold text-slate-500 leading-relaxed">
                                    Apakah Anda yakin ingin membatalkan pengiriman bukti pembayaran ini? Tindakan ini akan menghapus bukti transaksi yang telah diunggah secara permanen.
                                </p>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center justify-end space-x-3">
                            <button 
                                type="button" 
                                @click="closeConfirmCancelPaymentModal" 
                                class="px-5 py-2.5 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs rounded-xl transition border border-slate-200"
                            >
                                Tidak, Kembali
                            </button>
                            <button 
                                type="button" 
                                @click="confirmCancelPayment"
                                class="px-6 py-2.5 bg-red-600 hover:bg-red-700 text-white font-black text-xs rounded-xl transition shadow-md flex items-center space-x-1.5"
                            >
                                <Trash2 class="w-3.5 h-3.5" />
                                <span>Ya, Batalkan</span>
                            </button>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- Proposal Submission Link drive Modal Dialog -->
        <div v-if="isSubmissionModalOpen" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4 text-center">
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-slate-900/5 backdrop-blur-md transition-opacity" @click="closeSubmissionModal"></div>

                <!-- Modal Content Wrapper -->
                <div class="bg-white rounded-3xl overflow-hidden shadow-[0_25px_50px_-12px_rgba(0,0,0,0.25)] border border-slate-100 w-full max-w-lg my-8 inline-block text-left align-middle relative z-10 animate-fade-in-up transform transition-all">
                    
                    <!-- Header -->
                    <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                        <div class="space-y-1">
                            <h3 class="text-lg font-black text-slate-900 leading-tight">Kumpulkan Link Proposal</h3>
                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Kumpulkan Proposal & Surat Orisinalitas (Google Drive Link)</p>
                        </div>
                        <button @click="closeSubmissionModal" class="text-slate-400 hover:text-slate-600 transition p-1 hover:bg-slate-100 rounded-lg">
                            <X class="w-5 h-5" />
                        </button>
                    </div>

                    <!-- Body / Form -->
                    <form @submit.prevent="triggerConfirmSubmission" class="p-6 space-y-5 text-left">
                        
                        <!-- Info Box -->
                        <div class="bg-slate-50 border border-slate-100 rounded-2xl p-4 flex items-start space-x-3">
                            <Info class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" />
                            <div class="space-y-1">
                                <h5 class="text-xs font-black text-slate-800">Petunjuk Pengumpulan Link:</h5>
                                <ul class="list-disc list-inside text-[11px] font-bold text-slate-500 space-y-0.5">
                                    <li>Masukkan Proposal dan Surat Orisinalitas dalam satu folder Google Drive</li>
                                    <li>Atur hak akses sharing folder ke: <strong>"Siapa saja yang memiliki link dapat melihat"</strong> (Public)</li>
                                    <li>Tempel (Paste) alamat URL folder Google Drive tersebut pada kolom input di bawah ini</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Link Input -->
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider block">URL Google Drive Folder</label>
                            <input 
                                type="text"
                                v-model="submissionForm.link_file_submission"
                                placeholder="Contoh: https://drive.google.com/drive/folders/..."
                                class="w-full px-4 py-3 rounded-2xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-900 text-xs font-bold text-slate-700 bg-slate-50/50"
                            />
                            <div v-if="submissionForm.errors.link_file_submission" class="flex items-center space-x-1.5 text-red-500 mt-1">
                                <AlertCircle class="w-3.5 h-3.5 flex-shrink-0" />
                                <span class="text-[10px] font-bold">{{ submissionForm.errors.link_file_submission }}</span>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center justify-end space-x-3 pt-2">
                            <button 
                                type="button" 
                                @click="closeSubmissionModal" 
                                class="px-5 py-2.5 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs rounded-xl transition border border-slate-200"
                            >
                                Batal
                            </button>
                            <button 
                                type="submit" 
                                class="px-6 py-2.5 bg-[#1b2a4a] hover:bg-[#15233d] text-white font-black text-xs rounded-xl transition shadow-md flex items-center space-x-2"
                            >
                                <UploadCloud class="w-3.5 h-3.5" />
                                <span>Kumpulkan Link</span>
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>

        <!-- Double Confirm Submit Proposal Modal -->
        <div v-if="isConfirmSubmissionModalOpen" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4 text-center">
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-slate-900/5 backdrop-blur-md transition-opacity" @click="closeConfirmSubmissionModal"></div>

                <!-- Modal Content Wrapper -->
                <div class="bg-white rounded-3xl overflow-hidden shadow-[0_25px_50px_-12px_rgba(0,0,0,0.15)] border border-slate-100 w-full max-w-md my-8 inline-block text-left align-middle relative z-10 animate-fade-in-up transform transition-all">
                    <div class="p-6 space-y-6">
                        
                        <!-- Icon & Title -->
                        <div class="flex items-start space-x-4">
                            <div class="w-10 h-10 rounded-full bg-amber-50 flex items-center justify-center text-amber-600 flex-shrink-0">
                                <AlertCircle class="w-5 h-5" />
                            </div>
                            <div class="space-y-1 min-w-0">
                                <h3 class="text-base font-black text-slate-900">Apakah Anda Yakin Mengumpulkan?</h3>
                                <p class="text-xs font-bold text-slate-500 leading-relaxed">
                                    Pengumpulan proposal dan surat orisinalitas hanya dapat dilakukan <strong class="font-extrabold text-slate-800">1 kali</strong> dan <strong class="font-extrabold text-slate-800">tidak dapat diubah atau dibatalkan</strong> setelah dikirim. Pastikan link Google Drive Anda sudah benar dan dapat diakses secara publik.
                                </p>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center justify-end space-x-3">
                            <button 
                                type="button" 
                                @click="closeConfirmSubmissionModal" 
                                class="px-5 py-2.5 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs rounded-xl transition border border-slate-200"
                            >
                                Periksa Kembali
                            </button>
                            <button 
                                type="button" 
                                @click="submitProposal"
                                :disabled="submissionForm.processing"
                                class="px-6 py-2.5 bg-[#1b2a4a] hover:bg-[#15233d] text-white font-black text-xs rounded-xl transition shadow-md"
                            >
                                {{ submissionForm.processing ? 'Mengirim...' : 'Ya, Kumpulkan' }}
                            </button>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </CitechDashboardLayout>
</template>

<style scoped>
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(16px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in-up {
    animation: fadeInUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
</style>
