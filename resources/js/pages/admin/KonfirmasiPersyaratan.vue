<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { FileCheck, Check, X, ExternalLink, FileText, Search, Users, AlertCircle, Info } from '@lucide/vue';
import CitechDashboardLayout from '@/components/CitechDashboardLayout.vue';

const props = defineProps({
    teams: Array,
});

const searchQuery = ref('');
const isRejectModalOpen = ref(false);
const selectedRegistrasiId = ref(null);

const rejectionForm = useForm({
    status: 'ditolak',
    catatan: '',
});

// Filter teams by search query
const filteredTeams = computed(() => {
    if (!props.teams) return [];
    return props.teams.filter(team => {
        const query = searchQuery.value.toLowerCase();
        return team.nama_tim.toLowerCase().includes(query) ||
               team.universitas.toLowerCase().includes(query);
    });
});

const formatDate = (dateStr) => {
    if (!dateStr) return '-';
    const options = { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' };
    return new Date(dateStr).toLocaleDateString('id-ID', options);
};

const isConfirmApproveModalOpen = ref(false);
const selectedApproveId = ref(null);

const openConfirmApproveModal = (id_registrasi) => {
    selectedApproveId.value = id_registrasi;
    isConfirmApproveModalOpen.value = true;
};

const closeConfirmApproveModal = () => {
    isConfirmApproveModalOpen.value = false;
};

const submitApproval = () => {
    router.post(route('admin.persyaratan.update', selectedApproveId.value), {
        status: 'berhasil'
    }, {
        onSuccess: () => {
            isConfirmApproveModalOpen.value = false;
        }
    });
};

const openRejectModal = (id_registrasi) => {
    selectedRegistrasiId.value = id_registrasi;
    rejectionForm.catatan = '';
    rejectionForm.clearErrors();
    isRejectModalOpen.value = true;
};

const closeRejectModal = () => {
    isRejectModalOpen.value = false;
};

const submitRejection = () => {
    rejectionForm.post(route('admin.persyaratan.update', selectedRegistrasiId.value), {
        onSuccess: () => {
            isRejectModalOpen.value = false;
        }
    });
};
</script>

<template>
    <Head title="Konfirmasi Persyaratan - CITECH 2026" />

    <CitechDashboardLayout activeMenu="admin.persyaratan" role="admin">
        <template #header-title>
            <h2 class="text-lg font-black tracking-wide text-slate-800 font-sans">Konfirmasi Persyaratan</h2>
        </template>

        <div class="space-y-6 animate-fade-in-up">
            <!-- Header Card -->
            <div class="bg-white rounded-3xl p-6 md:p-8 shadow-[0_10px_35px_rgba(0,0,0,0.03)] border border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="space-y-2">
                    <h3 class="text-xl font-black text-slate-800 tracking-tight flex items-center space-x-2">
                        <FileCheck class="w-6 h-6 text-blue-900" />
                        <span>Verifikasi Dokumen Pendaftaran</span>
                    </h3>
                    <p class="text-slate-500 text-xs font-bold leading-relaxed max-w-2xl">
                        Halaman ini digunakan oleh administrator untuk meninjau, menyetujui, atau menolak berkas administratif (gabungan KTM, twibbon, dan bukti follow PDF) yang diunggah oleh peserta.
                    </p>
                </div>

                <!-- Search Input -->
                <div class="relative w-full md:w-80 flex-shrink-0">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                        <Search class="w-4 h-4" />
                    </span>
                    <input 
                        type="text" 
                        v-model="searchQuery" 
                        placeholder="Cari tim atau universitas..." 
                        class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-900 text-xs font-bold text-slate-700 bg-slate-50/50" 
                    />
                </div>
            </div>

            <!-- Table Card -->
            <div class="bg-white rounded-3xl shadow-[0_10px_35px_rgba(0,0,0,0.03)] border border-slate-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/70 border-b border-slate-100 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                <th class="py-4 px-6">Nama Tim / Instansi</th>
                                <th class="py-4 px-6">Anggota Tim</th>
                                <th class="py-4 px-6">Tanggal Upload</th>
                                <th class="py-4 px-6 text-center">Status Berkas</th>
                                <th class="py-4 px-6 text-center">Tinjau File</th>
                                <th class="py-4 px-6 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs font-bold text-slate-700">
                            <tr v-if="filteredTeams.length === 0">
                                <td colspan="6" class="py-12 text-center text-slate-400 font-bold">
                                    Tidak ada data tim yang cocok.
                                </td>
                            </tr>
                            <tr 
                                v-for="team in filteredTeams" 
                                :key="team.id_tim"
                                class="hover:bg-slate-50/30 transition-colors"
                            >
                                <!-- Team / Univ -->
                                <td class="py-4 px-6 space-y-1">
                                    <div class="text-sm font-extrabold text-blue-900">{{ team.nama_tim }}</div>
                                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">{{ team.universitas }}</div>
                                </td>
                                
                                <!-- Members count/details -->
                                <td class="py-4 px-6">
                                    <div class="flex items-center space-x-1.5 text-slate-500">
                                        <Users class="w-4 h-4 text-slate-400 flex-shrink-0" />
                                        <span>{{ team.members ? team.members.length : 0 }} Anggota</span>
                                    </div>
                                </td>

                                <!-- Upload Date -->
                                <td class="py-4 px-6 text-slate-500">
                                    {{ team.dokumen_registrasi ? formatDate(team.dokumen_registrasi.uploaded_at) : '-' }}
                                </td>

                                <!-- Status Badge -->
                                <td class="py-4 px-6 text-center">
                                    <div class="inline-flex flex-col items-center space-y-1">
                                        <span 
                                            class="inline-block text-[9px] font-black uppercase tracking-wider px-3 py-1 rounded-full border shadow-sm"
                                            :class="[
                                                !team.dokumen_registrasi 
                                                    ? 'bg-slate-50 text-slate-500 border-slate-200' 
                                                    : (team.dokumen_registrasi.status_registrasi === 'pending'
                                                        ? 'bg-amber-50 text-amber-600 border-amber-200'
                                                        : (team.dokumen_registrasi.status_registrasi === 'berhasil'
                                                            ? 'bg-green-50 text-green-600 border-green-200'
                                                            : 'bg-red-50 text-red-600 border-red-200'))
                                            ]"
                                        >
                                            {{ !team.dokumen_registrasi ? 'Belum Mengunggah' : team.dokumen_registrasi.status_registrasi }}
                                        </span>

                                        <!-- Catatan Penolakan (Jika ada) -->
                                        <span 
                                            v-if="team.dokumen_registrasi && team.dokumen_registrasi.status_registrasi === 'ditolak'"
                                            class="block text-[9px] text-red-500 max-w-[150px] truncate"
                                            :title="team.dokumen_registrasi.catatan_registrasi"
                                        >
                                            Ket: {{ team.dokumen_registrasi.catatan_registrasi || '-' }}
                                        </span>
                                    </div>
                                </td>

                                <!-- Berkas File link -->
                                <td class="py-4 px-6 text-center">
                                    <a 
                                        v-if="team.dokumen_registrasi"
                                        :href="'/storage/' + team.dokumen_registrasi.link_file_registrasi" 
                                        target="_blank"
                                        class="inline-flex items-center space-x-1 px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-600 rounded-lg text-[10px] font-black uppercase tracking-wider transition border border-blue-100"
                                    >
                                        <FileText class="w-3.5 h-3.5" />
                                        <span>Buka PDF</span>
                                        <ExternalLink class="w-2.5 h-2.5" />
                                    </a>
                                    <span v-else class="text-slate-400 font-bold">-</span>
                                </td>

                                <!-- Actions -->
                                <td class="py-4 px-6 text-center">
                                    <div 
                                        v-if="team.dokumen_registrasi && team.dokumen_registrasi.status_registrasi === 'pending'"
                                        class="flex items-center justify-center space-x-2"
                                    >
                                        <button 
                                            @click="openConfirmApproveModal(team.dokumen_registrasi.id_registrasi)"
                                            class="p-2 bg-green-50 hover:bg-green-100 text-green-600 rounded-xl border border-green-100 transition shadow-sm"
                                            title="Setujui Berkas"
                                        >
                                            <Check class="w-4 h-4" />
                                        </button>
                                        <button 
                                            @click="openRejectModal(team.dokumen_registrasi.id_registrasi)"
                                            class="p-2 bg-red-50 hover:bg-red-100 text-red-600 rounded-xl border border-red-100 transition shadow-sm"
                                            title="Tolak Berkas"
                                        >
                                            <X class="w-4 h-4" />
                                        </button>
                                    </div>
                                    <span v-else class="text-slate-400 font-bold">-</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Rejection Reason Modal -->
        <div v-if="isRejectModalOpen" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4 text-center">
                <!-- Backdrop (Clean blur, no dark overlay) -->
                <div class="fixed inset-0 bg-[#0f172a]/5 backdrop-blur-md transition-opacity" @click="closeRejectModal"></div>

                <!-- Modal Content -->
                <div class="bg-white rounded-3xl overflow-hidden shadow-[0_25px_50px_-12px_rgba(0,0,0,0.15)] border border-slate-100 w-full max-w-md my-8 inline-block text-left align-middle relative z-10 animate-fade-in-up transform transition-all">
                    
                    <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                        <div class="space-y-0.5">
                            <h3 class="text-md font-black text-slate-950">Catatan Penolakan Dokumen</h3>
                            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wide">Tolak dokumen pendaftaran peserta</p>
                        </div>
                        <button @click="closeRejectModal" class="text-slate-400 hover:text-slate-600 transition p-1 hover:bg-slate-100 rounded-lg">
                            <X class="w-5 h-5" />
                        </button>
                    </div>

                    <form @submit.prevent="submitRejection" class="p-6 space-y-4">
                        <div class="bg-amber-50 border border-amber-200/50 rounded-2xl p-4 flex items-start space-x-2 text-amber-800">
                            <Info class="w-5 h-5 flex-shrink-0 mt-0.5" />
                            <div class="text-[11px] font-bold leading-relaxed">
                                Silakan tuliskan alasan/catatan penolakan secara jelas agar peserta dapat memperbaiki dokumen dan mengunggahnya kembali.
                            </div>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider">Alasan Penolakan</label>
                            <textarea 
                                v-model="rejectionForm.catatan" 
                                placeholder="Contoh: Berkas KTM tidak terbaca jelas atau file twibbon salah." 
                                rows="4"
                                class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-900 text-xs font-semibold text-slate-700 bg-slate-50/50"
                            ></textarea>
                            <div v-if="rejectionForm.errors.catatan" class="text-[10px] text-red-500 font-bold mt-1">
                                {{ rejectionForm.errors.catatan }}
                            </div>
                        </div>

                        <div class="flex items-center justify-end space-x-3 pt-2">
                            <button 
                                type="button" 
                                @click="closeRejectModal" 
                                class="px-5 py-2.5 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs rounded-xl transition border border-slate-200 shadow-sm"
                            >
                                Batal
                            </button>
                            <button 
                                type="submit" 
                                :disabled="rejectionForm.processing || !rejectionForm.catatan"
                                class="px-6 py-2.5 bg-red-600 hover:bg-red-700 text-white font-black text-xs rounded-xl transition shadow-md disabled:opacity-50 flex items-center space-x-1.5"
                            >
                                <X class="w-3.5 h-3.5" />
                                <span>{{ rejectionForm.processing ? 'Menolak...' : 'Tolak Berkas' }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Confirm Approval Modal -->
        <div v-if="isConfirmApproveModalOpen" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4 text-center">
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-slate-900/5 backdrop-blur-md transition-opacity" @click="closeConfirmApproveModal"></div>

                <!-- Modal Content -->
                <div class="bg-white rounded-3xl overflow-hidden shadow-[0_25px_50px_-12px_rgba(0,0,0,0.25)] border border-slate-100 w-full max-w-md my-8 inline-block text-left align-middle relative z-10 animate-fade-in-up transform transition-all">
                    <div class="p-6 space-y-6">
                        
                        <!-- Icon & Title -->
                        <div class="flex items-start space-x-4">
                            <div class="w-10 h-10 rounded-full bg-green-50 flex items-center justify-center text-green-600 flex-shrink-0">
                                <Check class="w-5 h-5" />
                            </div>
                            <div class="space-y-1 min-w-0">
                                <h3 class="text-base font-black text-slate-950">Setujui Berkas Persyaratan?</h3>
                                <p class="text-xs font-bold text-slate-500 leading-relaxed">
                                    Apakah Anda yakin ingin menyetujui dokumen persyaratan tim ini? Status verifikasi berkas akan diubah menjadi berhasil/lolos tahap administrasi.
                                </p>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center justify-end space-x-3">
                            <button 
                                type="button" 
                                @click="closeConfirmApproveModal" 
                                class="px-5 py-2.5 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs rounded-xl transition border border-slate-200"
                            >
                                Batal
                            </button>
                            <button 
                                type="button" 
                                @click="submitApproval"
                                class="px-6 py-2.5 bg-green-600 hover:bg-green-700 text-white font-black text-xs rounded-xl transition shadow-md flex items-center space-x-1.5"
                            >
                                <Check class="w-3.5 h-3.5" />
                                <span>Ya, Setujui</span>
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
    from { opacity: 0; transform: translateY(12px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in-up {
    animation: fadeInUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
</style>
