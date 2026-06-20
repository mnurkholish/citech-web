<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { CreditCard, Check, X, ExternalLink, FileText, Search, Users, AlertCircle, Info } from '@lucide/vue';
import CitechDashboardLayout from '@/components/CitechDashboardLayout.vue';

const props = defineProps({
    teams: Array,
});

const searchQuery = ref('');
const isRejectModalOpen = ref(false);
const selectedPembayaranId = ref(null);

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

const formatPrice = (batchNum) => {
    if (batchNum === 1) return 'Batch 1 (Rp 60.000)';
    if (batchNum === 2) return 'Batch 2 (Rp 80.000)';
    return 'Belum Ditentukan';
};

const isConfirmApproveModalOpen = ref(false);
const selectedApproveId = ref(null);

const openConfirmApproveModal = (id_pembayaran) => {
    selectedApproveId.value = id_pembayaran;
    isConfirmApproveModalOpen.value = true;
};

const closeConfirmApproveModal = () => {
    isConfirmApproveModalOpen.value = false;
};

const submitApproval = () => {
    router.post(route('admin.pembayaran.update', selectedApproveId.value), {
        status: 'berhasil'
    }, {
        onSuccess: () => {
            isConfirmApproveModalOpen.value = false;
        }
    });
};

const openRejectModal = (id_pembayaran) => {
    selectedPembayaranId.value = id_pembayaran;
    rejectionForm.catatan = '';
    rejectionForm.clearErrors();
    isRejectModalOpen.value = true;
};

const closeRejectModal = () => {
    isRejectModalOpen.value = false;
};

const submitRejection = () => {
    rejectionForm.post(route('admin.pembayaran.update', selectedPembayaranId.value), {
        onSuccess: () => {
            isRejectModalOpen.value = false;
        }
    });
};
</script>

<template>
    <Head title="Konfirmasi Pembayaran - CITECH 2026" />

    <CitechDashboardLayout activeMenu="admin.pembayaran" role="admin">
        <template #header-title>
            <h2 class="text-lg font-black tracking-wide text-slate-800 font-sans">Konfirmasi Pembayaran</h2>
        </template>

        <div class="space-y-6 animate-fade-in-up">
            <!-- Header Card -->
            <div class="bg-white rounded-3xl p-6 md:p-8 shadow-[0_10px_35px_rgba(0,0,0,0.03)] border border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="space-y-2">
                    <h3 class="text-xl font-black text-slate-800 tracking-tight flex items-center space-x-2">
                        <CreditCard class="w-6 h-6 text-blue-900" />
                        <span>Verifikasi Bukti Pembayaran</span>
                    </h3>
                    <p class="text-slate-500 text-xs font-bold leading-relaxed max-w-2xl">
                        Halaman ini digunakan oleh administrator untuk meninjau, menyetujui, atau menolak bukti transaksi pembayaran biaya pendaftaran yang diunggah oleh peserta.
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
                                <th class="py-4 px-6">Tahap / Nominal</th>
                                <th class="py-4 px-6">Tanggal Upload</th>
                                <th class="py-4 px-6 text-center">Status Pembayaran</th>
                                <th class="py-4 px-6 text-center">Tinjau Bukti</th>
                                <th class="py-4 px-6 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs font-bold text-slate-700">
                            <tr v-if="filteredTeams.length === 0">
                                <td colspan="6" class="py-12 text-center text-slate-400 font-bold">
                                    Tidak ada data pembayaran tim yang cocok.
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
                                
                                <!-- Batch details -->
                                <td class="py-4 px-6">
                                    <span class="text-slate-600 font-bold">{{ formatPrice(team.batch || 1) }}</span>
                                </td>

                                <!-- Upload Date -->
                                <td class="py-4 px-6 text-slate-500">
                                    {{ team.pembayaran ? formatDate(team.pembayaran.uploaded_at) : '-' }}
                                </td>

                                <!-- Status Badge -->
                                <td class="py-4 px-6 text-center">
                                    <div class="inline-flex flex-col items-center space-y-1">
                                        <span 
                                            class="inline-block text-[9px] font-black uppercase tracking-wider px-3 py-1 rounded-full border shadow-sm"
                                            :class="[
                                                !team.pembayaran 
                                                    ? 'bg-slate-50 text-slate-500 border-slate-200' 
                                                    : (team.pembayaran.status_pembayaran === 'pending'
                                                        ? 'bg-amber-50 text-amber-600 border-amber-200'
                                                        : (team.pembayaran.status_pembayaran === 'berhasil'
                                                            ? 'bg-green-50 text-green-600 border-green-200'
                                                            : 'bg-red-50 text-red-600 border-red-200'))
                                            ]"
                                        >
                                            {{ !team.pembayaran ? 'Belum Mengunggah' : team.pembayaran.status_pembayaran }}
                                        </span>

                                        <!-- Catatan Penolakan (Jika ada) -->
                                        <span 
                                            v-if="team.pembayaran && team.pembayaran.status_pembayaran === 'ditolak'"
                                            class="block text-[9px] text-red-500 max-w-[150px] truncate"
                                            :title="team.pembayaran.catatan_pembayaran"
                                        >
                                            Ket: {{ team.pembayaran.catatan_pembayaran || '-' }}
                                        </span>
                                    </div>
                                </td>

                                <!-- Berkas File link -->
                                <td class="py-4 px-6 text-center">
                                    <a 
                                        v-if="team.pembayaran"
                                        :href="'/storage/' + team.pembayaran.bukti_pembayaran" 
                                        target="_blank"
                                        class="inline-flex items-center space-x-1 px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-600 rounded-lg text-[10px] font-black uppercase tracking-wider transition border border-blue-100"
                                    >
                                        <FileText class="w-3.5 h-3.5" />
                                        <span>Buka Bukti</span>
                                        <ExternalLink class="w-2.5 h-2.5" />
                                    </a>
                                    <span v-else class="text-slate-400 font-bold">-</span>
                                </td>

                                <!-- Actions -->
                                <td class="py-4 px-6 text-center">
                                    <div 
                                        v-if="team.pembayaran && team.pembayaran.status_pembayaran === 'pending'"
                                        class="flex items-center justify-center space-x-2"
                                    >
                                        <button 
                                            @click="openConfirmApproveModal(team.pembayaran.id_pembayaran)"
                                            class="p-2 bg-green-50 hover:bg-green-100 text-green-600 rounded-xl border border-green-100 transition shadow-sm animate-scale-up"
                                            title="Setujui Pembayaran"
                                        >
                                            <Check class="w-4 h-4" />
                                        </button>
                                        <button 
                                            @click="openRejectModal(team.pembayaran.id_pembayaran)"
                                            class="p-2 bg-red-50 hover:bg-red-100 text-red-600 rounded-xl border border-red-100 transition shadow-sm animate-scale-up"
                                            title="Tolak Pembayaran"
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

        <!-- Custom Confirm Approve Modal -->
        <div v-if="isConfirmApproveModalOpen" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4 text-center">
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-slate-900/5 backdrop-blur-md transition-opacity" @click="closeConfirmApproveModal"></div>

                <!-- Modal Content Wrapper -->
                <div class="bg-white rounded-3xl overflow-hidden shadow-[0_25px_50px_-12px_rgba(0,0,0,0.15)] border border-slate-100 w-full max-w-md my-8 inline-block text-left align-middle relative z-10 animate-fade-in-up transform transition-all">
                    <div class="p-6 space-y-6">
                        
                        <!-- Icon & Title -->
                        <div class="flex items-start space-x-4">
                            <div class="w-10 h-10 rounded-full bg-green-50 flex items-center justify-center text-green-600 flex-shrink-0">
                                <Check class="w-5 h-5" />
                            </div>
                            <div class="space-y-1 min-w-0">
                                <h3 class="text-base font-black text-slate-900">Setujui Pembayaran Peserta?</h3>
                                <p class="text-xs font-bold text-slate-500 leading-relaxed">
                                    Apakah Anda yakin ingin menyetujui bukti pembayaran pendaftaran tim ini? Setelah disetujui, status seleksi tim ini otomatis akan berubah menjadi babak penyisihan.
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
                                class="px-6 py-2.5 bg-green-600 hover:bg-green-700 text-white font-black text-xs rounded-xl transition shadow-md"
                            >
                                Ya, Setujui
                            </button>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- Custom Reject Modal -->
        <div v-if="isRejectModalOpen" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4 text-center">
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-slate-900/5 backdrop-blur-md transition-opacity" @click="closeRejectModal"></div>

                <!-- Modal Content Wrapper -->
                <div class="bg-white rounded-3xl overflow-hidden shadow-[0_25px_50px_-12px_rgba(0,0,0,0.25)] border border-slate-100 w-full max-w-lg my-8 inline-block text-left align-middle relative z-10 animate-fade-in-up transform transition-all">
                    
                    <!-- Header -->
                    <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                        <div class="space-y-1">
                            <h3 class="text-base font-black text-slate-900 leading-tight">Tolak Bukti Pembayaran</h3>
                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Silakan tuliskan alasan penolakan secara jelas.</p>
                        </div>
                        <button @click="closeRejectModal" class="text-slate-400 hover:text-slate-600 transition p-1 hover:bg-slate-100 rounded-lg">
                            <X class="w-5 h-5" />
                        </button>
                    </div>

                    <!-- Body / Form -->
                    <form @submit.prevent="submitRejection" class="p-6 space-y-5 text-left">
                        
                        <!-- Textarea -->
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider block">Catatan / Alasan Penolakan</label>
                            <textarea 
                                v-model="rejectionForm.catatan"
                                rows="4" 
                                placeholder="Contoh: Bukti transfer terpotong atau nominal tidak sesuai..."
                                class="w-full px-4 py-3 rounded-2xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-900 text-xs font-bold text-slate-700 bg-slate-50/50"
                            ></textarea>
                            <div v-if="rejectionForm.errors.catatan" class="flex items-center space-x-1 text-red-500 mt-1">
                                <AlertCircle class="w-3.5 h-3.5 flex-shrink-0" />
                                <span class="text-[10px] font-bold">{{ rejectionForm.errors.catatan }}</span>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center justify-end space-x-3 pt-2">
                            <button 
                                type="button" 
                                @click="closeRejectModal" 
                                class="px-5 py-2.5 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs rounded-xl transition border border-slate-200"
                            >
                                Batal
                            </button>
                            <button 
                                type="submit" 
                                :disabled="rejectionForm.processing || !rejectionForm.catatan.trim()"
                                class="px-6 py-2.5 bg-red-600 hover:bg-red-700 text-white font-black text-xs rounded-xl transition shadow-md disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                {{ rejectionForm.processing ? 'Memproses...' : 'Tolak Bukti Pembayaran' }}
                            </button>
                        </div>

                    </form>
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
