<script setup>
import { ref, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import { Search, FileText, ExternalLink, Calendar, CheckCircle2 } from '@lucide/vue';
import CitechDashboardLayout from '@/components/CitechDashboardLayout.vue';

const props = defineProps({
    teams: Array,
});

const searchQuery = ref('');

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

const getKetuaName = (members) => {
    if (!members) return '-';
    const ketua = members.find(m => m.role === 'ketua');
    return ketua ? ketua.nama_peserta : '-';
};
</script>

<template>
    <Head title="Submission Karya - CITECH 2026" />

    <CitechDashboardLayout activeMenu="admin.submission" role="admin">
        <template #header-title>
            <h2 class="text-lg font-black tracking-wide text-slate-800 font-sans">Submission</h2>
        </template>

        <div class="space-y-6 animate-fade-in-up">
            <!-- Header Card -->
            <div class="bg-white rounded-3xl p-6 md:p-8 shadow-[0_10px_35px_rgba(0,0,0,0.03)] border border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="space-y-2">
                    <h3 class="text-xl font-black text-slate-800 tracking-tight flex items-center space-x-2">
                        <FileText class="w-6 h-6 text-blue-900" />
                        <span>Proposal & Surat Orisinalitas</span>
                    </h3>
                    <p class="text-slate-500 text-xs font-bold leading-relaxed max-w-2xl">
                        Tinjau link Google Drive pengumpulan proposal dan surat orisinalitas dari tim-tim peserta yang terdaftar resmi.
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
                                <th class="py-4 px-6">Tanggal Pengumpulan</th>
                                <th class="py-4 px-6 text-center">Tautan Proposal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs font-bold text-slate-700">
                            <tr v-if="filteredTeams.length === 0">
                                <td colspan="4" class="py-12 text-center text-slate-400 font-bold">
                                    Tidak ada data submission yang ditemukan.
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

                                <!-- Upload Date -->
                                <td class="py-4 px-6 text-slate-500 font-semibold">
                                    <div class="flex items-center space-x-1.5">
                                        <Calendar class="w-3.5 h-3.5 text-slate-400" />
                                        <span>{{ team.submission ? formatDate(team.submission.uploaded_at) : '-' }}</span>
                                    </div>
                                </td>

                                <!-- Link Google Drive -->
                                <td class="py-4 px-6 text-center">
                                    <a 
                                        v-if="team.submission"
                                        :href="team.submission.link_file_submission" 
                                        target="_blank"
                                        class="inline-flex items-center space-x-1 px-3.5 py-2 bg-blue-50 hover:bg-blue-100 text-blue-600 rounded-xl text-[10px] font-black uppercase tracking-wider transition border border-blue-100/50 shadow-sm"
                                    >
                                        <FileText class="w-3.5 h-3.5" />
                                        <span>Buka Link Drive</span>
                                        <ExternalLink class="w-2.5 h-2.5" />
                                    </a>
                                    <span v-else class="text-slate-400 font-bold">-</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
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
