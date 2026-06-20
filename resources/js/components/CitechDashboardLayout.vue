<script setup>
import { ref, computed, onMounted } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import { 
  Home, 
  Users, 
  User, 
  LogOut, 
  Menu, 
  FileCheck, 
  CreditCard, 
  Users2, 
  UploadCloud, 
  CalendarDays,
  Handshake,
  X
} from '@lucide/vue';

const props = defineProps({
  activeMenu: {
    type: String,
    required: true
  },
  role: {
    type: String,
    default: 'peserta' // 'peserta' | 'admin'
  }
});

const page = usePage();
const user = computed(() => page.props.auth.user);
// Get team name if it exists (e.g. from userTeam prop or user relationship)
const teamName = computed(() => {
  return page.props.userTeam?.nama_tim || page.props.auth?.user?.tim?.nama_tim || '';
});

const isSidebarExpanded = ref(false);

onMounted(() => {
  if (window.innerWidth >= 1024) {
    isSidebarExpanded.value = true;
  }
});

const toggleSidebar = () => {
  isSidebarExpanded.value = !isSidebarExpanded.value;
};

const handleMenuClick = () => {
  if (window.innerWidth < 1024) {
    isSidebarExpanded.value = false;
  }
};

// Menus based on role
const menus = computed(() => {
  if (props.role === 'admin') {
    return [
      { name: 'Beranda / Dashboard', route: 'admin.dashboard', icon: Home },
      { name: 'Konfirmasi Persyaratan', route: 'admin.persyaratan', icon: FileCheck },
      { name: 'Konfirmasi Pembayaran', route: 'admin.pembayaran', icon: CreditCard },
      { name: 'Tim Terdaftar', route: 'admin.tim-terdaftar', icon: Users2 },
      { name: 'Submission', route: 'admin.submission', icon: UploadCloud },
      { name: 'Atur Tanggal', route: 'admin.atur-tanggal', icon: CalendarDays },
      { name: 'Kelola Sponsor', route: 'admin.kelola-sponsor', icon: Handshake },
    ];
  } else {
    return [
      { name: 'Beranda', route: 'dashboard', icon: Home },
      { name: 'Tim', route: 'peserta.tim', icon: Users },
      { name: 'Profil', route: 'peserta.profil', icon: User },
    ];
  }
});

const logout = () => {
  handleMenuClick();
  router.post(route('logout'));
};
</script>

<template>
  <div class="min-h-screen bg-[#f0f4f9] flex font-sans">
    
    <!-- Sidebar -->
    <aside 
      class="bg-[#1b2a4a] text-white flex flex-col transition-all duration-300 z-30 fixed h-full lg:sticky lg:top-0 lg:h-screen"
      :class="[isSidebarExpanded ? 'translate-x-0 w-64' : '-translate-x-full lg:translate-x-0 lg:w-20']"
    >
      <!-- Sidebar Header -->
      <div class="p-6 border-b border-slate-700/50 flex items-center justify-between min-h-[80px]">
        <div v-if="isSidebarExpanded" class="animate-fade-in flex items-center space-x-3">
          <img src="/assets/logo-citech.png" alt="Logo CITECH" class="h-8 w-auto object-contain flex-shrink-0" />
          <div>
            <h1 class="text-white text-xl font-black tracking-wider leading-none">CITECH</h1>
            <p class="text-slate-400 text-[9px] font-bold uppercase tracking-wider mt-1 leading-none">
              {{ role === 'admin' ? 'Dashboard Admin' : 'Dashboard Peserta' }}
            </p>
          </div>
        </div>
        <div v-else class="mx-auto flex items-center justify-center animate-fade-in">
          <img src="/assets/logo-citech.png" alt="Logo CITECH" class="h-8 w-auto object-contain" />
        </div>
        
        <!-- Mobile close button -->
        <button @click="toggleSidebar" class="lg:hidden text-slate-400 hover:text-white">
          <X class="w-5 h-5" />
        </button>
      </div>

      <!-- Navigation Links -->
      <nav class="flex-grow pt-6 space-y-1">
        <div v-for="menu in menus" :key="menu.route">
          <Link
            :href="route(menu.route)"
            @click="handleMenuClick"
            class="flex items-center transition duration-200 group relative"
            :class="[
              activeMenu === menu.route 
                ? 'bg-[#203156] text-white border-l-4 border-l-[#eab308]' 
                : 'text-slate-300 hover:bg-[#203156] hover:text-white'
            ]"
            :title="!isSidebarExpanded ? menu.name : ''"
          >
            <!-- Highlight bar -->
            <div 
              class="flex items-center py-4 px-6 w-full"
              :class="[!isSidebarExpanded ? 'justify-center' : '']"
            >
              <component :is="menu.icon" class="w-5 h-5 flex-shrink-0" :class="[activeMenu === menu.route ? 'text-[#eab308]' : 'text-slate-400 group-hover:text-slate-200']" />
              <span v-if="isSidebarExpanded" class="ml-4 font-bold text-sm tracking-wide">{{ menu.name }}</span>
            </div>
          </Link>
        </div>
      </nav>

      <!-- Sidebar Footer (Logout) -->
      <div class="p-4 border-t border-slate-700/50">
        <button
          @click="logout"
          class="flex items-center w-full transition duration-200 group text-slate-300 hover:bg-[#203156] hover:text-white rounded-xl py-3 px-4"
          :class="[!isSidebarExpanded ? 'justify-center' : '']"
          :title="!isSidebarExpanded ? 'Keluar' : ''"
        >
          <LogOut class="w-5 h-5 flex-shrink-0 text-slate-400 group-hover:text-slate-200" />
          <span v-if="isSidebarExpanded" class="ml-4 font-bold text-sm tracking-wide">Logout</span>
        </button>
      </div>
    </aside>

    <!-- Overlay for mobile sidebar -->
    <div 
      v-if="isSidebarExpanded" 
      @click="toggleSidebar" 
      class="lg:hidden fixed inset-0 bg-black/40 z-20 transition-opacity"
    ></div>

    <!-- Main Content Wrapper -->
    <div class="flex-grow flex flex-col min-w-0">
      
      <!-- Topbar Header -->
      <header class="bg-white h-20 border-b border-slate-200/80 px-6 md:px-8 flex items-center justify-between flex-shrink-0">
        <div class="flex items-center space-x-4">
          <!-- Toggle Sidebar Button -->
          <button 
            @click="toggleSidebar" 
            class="p-2 rounded-lg hover:bg-slate-100 text-slate-600 transition"
          >
            <Menu class="w-6 h-6" />
          </button>
          
          <slot name="header-title"></slot>
        </div>

        <!-- Right Side Brand/Team Name -->
        <div class="flex items-center space-x-4">
          <span class="text-xs sm:text-sm font-black tracking-wider text-slate-800 uppercase bg-slate-100 hover:bg-slate-200/80 transition px-3 py-1.5 sm:px-4 sm:py-2 rounded-xl border border-slate-200/50 truncate max-w-[140px] sm:max-w-[200px] md:max-w-[280px] lg:max-w-[360px]">
            {{ role === 'admin' ? 'ADMINISTRATOR' : (teamName || user.name) }}
          </span>
        </div>
      </header>

      <!-- Page Content -->
      <main class="flex-grow p-6 md:p-8 overflow-y-auto max-w-7xl w-full mx-auto">
        <slot />
      </main>

    </div>
  </div>
</template>

<style scoped>
.animate-fade-in {
  animation: fadeIn 0.2s ease-out forwards;
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateX(-5px); }
  to { opacity: 1; transform: translateX(0); }
}
</style>
