<script setup>
import { ref, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import { Search, Users, Award, ShieldCheck, Info, FileText, CheckCircle2, X } from '@lucide/vue';
import CitechDashboardLayout from '@/components/CitechDashboardLayout.vue';

const props = defineProps({
    teams: Array,
});

const searchQuery = ref('');
const selectedTeamDetails = ref(null);
const isDetailModalOpen = ref(false);

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

const mapStatusSeleksi = (status) => {
    const mapping = {
        'penyisihan': 'Babak Penyisihan',
        'tidak_lolos_final': 'Tidak Lolos Final',
        'final': 'Babak Final',
    };
    return mapping[status] || status;
};

const getKetuaName = (members) => {
    if (!members) return '-';
    const ketua = members.find(m => m.role === 'ketua');
    return ketua ? ketua.nama_peserta : '-';
};

const openTeamDetails = (team) => {
    // Sort members: leader first
    const membersList = team.members ? [...team.members].sort((a, b) => {
        if (a.role === 'ketua') return -1;
        if (b.role === 'ketua') return 1;
        return a.id_member - b.id_member;
    }) : [];

    selectedTeamDetails.value = {
        ...team,
        sortedMembers: membersList
    };
    isDetailModalOpen.value = true;
};

const closeTeamDetails = () => {
    isDetailModalOpen.value = false;
};
</script>

<template>
    <Head title="Tim Terdaftar - CITECH 2026" />

    <CitechDashboardLayout activeMenu="admin.tim-terdaftar" role="admin">
        <template #header-title>
            <h2 class="text-lg font-black tracking-wide text-slate-800 font-sans">Tim Terdaftar</h2>
        </template>

        <div class="space-y-6 animate-fade-in-up">
            <!-- Header Card -->
            <div class="bg-white rounded-3xl p-6 md:p-8 shadow-[0_10px_35px_rgba(0,0,0,0.03)] border border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="space-y-2">
                    <h3 class="text-xl font-black text-slate-800 tracking-tight flex items-center space-x-2">
                        <CheckCircle2 class="w-6 h-6 text-green-600" />
                        <span>Daftar Tim Terverifikasi</span>
                    </h3>
                    <p class="text-slate-500 text-xs font-bold leading-relaxed max-w-2xl">
                        Menampilkan daftar seluruh tim peserta CITECH 2026 yang telah berhasil melewati verifikasi berkas persyaratan administratif dan penyelesaian pembayaran pendaftaran.
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
                        placeholder="Cari nama tim atau universitas..." 
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
                                <th class="py-4 px-6">Ketua Tim</th>
                                <th class="py-4 px-6">Batch Lomba</th>
                                <th class="py-4 px-6">Tanggal Verifikasi</th>
                                <th class="py-4 px-6 text-center">Status Lomba</th>
                                <th class="py-4 px-6 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs font-bold text-slate-700">
                            <tr v-if="filteredTeams.length === 0">
                                <td colspan="6" class="py-12 text-center text-slate-400 font-bold">
                                    Tidak ada data tim terdaftar yang ditemukan.
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
                                
                                <!-- Ketua Tim -->
                                <td class="py-4 px-6 text-slate-600 font-semibold">
                                    {{ getKetuaName(team.members) }}
                                </td>

                                <!-- Batch -->
                                <td class="py-4 px-6">
                                    <span class="inline-block px-2.5 py-1 bg-slate-100 text-slate-600 rounded-lg text-[10px] font-black uppercase tracking-wider">
                                        Batch {{ team.batch || 1 }}
                                    </span>
                                </td>

                                <!-- Verification Date -->
                                <td class="py-4 px-6 text-slate-500 font-semibold">
                                    {{ team.pembayaran ? formatDate(team.pembayaran.uploaded_at) : '-' }}
                                </td>

                                <!-- Selection Status -->
                                <td class="py-4 px-6 text-center">
                                    <span 
                                        class="inline-block text-[9px] font-black uppercase tracking-wider px-3 py-1 rounded-full border shadow-sm"
                                        :class="[
                                            team.status_seleksi === 'final'
                                                ? 'bg-green-50 text-green-600 border-green-200'
                                                : (team.status_seleksi === 'tidak_lolos_final'
                                                    ? 'bg-red-50 text-red-600 border-red-200'
                                                    : 'bg-blue-50 text-blue-600 border-blue-200')
                                        ]"
                                    >
                                        {{ mapStatusSeleksi(team.status_seleksi) }}
                                    </span>
                                </td>

                                <!-- Action (Details Modal) -->
                                <td class="py-4 px-6 text-center">
                                    <button 
                                        @click="openTeamDetails(team)"
                                        class="inline-flex items-center space-x-1 px-3 py-1.5 bg-blue-950 hover:bg-blue-900 text-white rounded-lg text-[10px] font-black uppercase tracking-wider transition shadow-sm"
                                    >
                                        <Info class="w-3.5 h-3.5" />
                                        <span>Detail Anggota</span>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Custom Details Modal (Centralized glassmorphic look) -->
        <div v-if="isDetailModalOpen && selectedTeamDetails" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4 text-center">
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-slate-900/5 backdrop-blur-md transition-opacity" @click="closeTeamDetails"></div>

                <!-- Modal Content Wrapper -->
                <div class="bg-white rounded-3xl overflow-hidden shadow-[0_25px_50px_-12px_rgba(0,0,0,0.25)] border border-slate-100 w-full max-w-2xl my-8 inline-block text-left align-middle relative z-10 animate-fade-in-up transform transition-all">
                    
                    <!-- Header -->
                    <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                        <div class="space-y-1">
                            <h3 class="text-lg font-black text-slate-900 leading-tight">Detail Anggota Kelompok</h3>
                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">{{ selectedTeamDetails.nama_tim }} - {{ selectedTeamDetails.universitas }}</p>
                        </div>
                        <button @click="closeTeamDetails" class="text-slate-400 hover:text-slate-600 transition p-1 hover:bg-slate-100 rounded-lg">
                            <X class="w-5 h-5" />
                        </button>
                    </div>

                    <!-- Body -->
                    <div class="p-6 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div 
                                v-for="(member, index) in selectedTeamDetails.sortedMembers" 
                                :key="member.id_member" 
                                class="bg-slate-50 border border-slate-100 rounded-2xl p-5 space-y-3"
                            >
                                <div class="flex justify-between items-center">
                                    <span class="text-[9px] font-black uppercase tracking-wider text-slate-400">
                                        {{ member.role === 'ketua' ? 'Ketua Kelompok' : `Anggota ${index}` }}
                                    </span>
                                    <span 
                                        class="text-[8px] font-black uppercase tracking-wider px-2 py-0.5 rounded border"
                                        :class="member.role === 'ketua' ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-slate-100 text-slate-600 border-slate-200'"
                                    >
                                        {{ member.role }}
                                    </span>
                                </div>
                                <div class="space-y-1.5">
                                    <div class="space-y-0.5">
                                        <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Nama Lengkap</span>
                                        <p class="text-xs font-extrabold text-slate-800">{{ member.nama_peserta }}</p>
                                    </div>
                                    <div class="space-y-0.5">
                                        <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">NIM / Identitas</span>
                                        <p class="text-xs font-extrabold text-slate-800">{{ member.nim_peserta }}</p>
                                    </div>
                                    <div class="space-y-0.5">
                                        <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Jurusan</span>
                                        <p class="text-xs font-extrabold text-slate-800">{{ member.jurusan || '-' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="p-6 border-t border-slate-100 flex justify-end">
                        <button 
                            @click="closeTeamDetails"
                            class="px-5 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl transition shadow-sm"
                        >
                            Tutup Detail
                        </button>
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
