<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import { 
    Plus, 
    BookOpen, 
    Clock, 
    Calendar, 
    CheckCircle2, 
    AlertCircle, 
    Sparkles, 
    ChevronRight, 
    MessageSquare,
    Smile,
    Meh,
    Frown,
    Users,
    FileSpreadsheet,
    ChevronLeft,
    RotateCw,
    Search
} from '@lucide/vue';
import { computed, ref, reactive, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
    role: String,
    reports: {
        type: Array,
        default: () => []
    },
    canViewTeamEod: {
        type: Boolean,
        default: false
    },
    departments: {
        type: Array,
        default: () => []
    },
    employees: {
        type: Array,
        default: () => []
    }
});

// Tab state
const activeTab = ref('my');

// Team EOD State & Axios Logic
const teamFilters = reactive({
    date_from: '',
    date_to: '',
    department: '',
    employee_id: '',
    status: '',
});

const teamRows = ref([]);
const teamMeta = reactive({
    total: 0,
    per_page: 15,
    page: 1,
});
const teamLoading = ref(false);
const teamError = ref(null);

const fetchTeamData = async () => {
    teamLoading.value = true;
    teamError.value = null;

    try {
        const res = await axios.get('/eod/employee/data', {
            params: {
                date_from: teamFilters.date_from || undefined,
                date_to: teamFilters.date_to || undefined,
                department: teamFilters.department || undefined,
                employee_id: teamFilters.employee_id || undefined,
                status: teamFilters.status || undefined,
                per_page: teamMeta.per_page,
                page: teamMeta.page,
            },
        });

        teamRows.value = res.data.data;
        teamMeta.total = res.data.total;
        teamMeta.page = res.data.current_page;
        teamMeta.per_page = res.data.per_page;
    } catch (e) {
        teamError.value = e?.response?.data?.message ?? 'Failed to load EOD data';
    } finally {
        teamLoading.value = false;
    }
};

const exportTeamExcel = () => {
    const params = new URLSearchParams();
    if (teamFilters.date_from) params.append('date_from', teamFilters.date_from);
    if (teamFilters.date_to) params.append('date_to', teamFilters.date_to);
    if (teamFilters.department) params.append('department', teamFilters.department);
    if (teamFilters.employee_id) params.append('employee_id', teamFilters.employee_id);
    if (teamFilters.status) params.append('status', teamFilters.status);

    window.location.href = `/eod/employee/export?${params.toString()}`;
};

const resetTeamFilters = () => {
    teamFilters.date_from = '';
    teamFilters.date_to = '';
    teamFilters.department = '';
    teamFilters.employee_id = '';
    teamFilters.status = '';
    teamMeta.page = 1;
};

// Calculate total pages
const totalPages = computed(() => Math.ceil(teamMeta.total / teamMeta.per_page));

// Watch filters
watch(
    () => [teamFilters.date_from, teamFilters.date_to, teamFilters.department, teamFilters.employee_id, teamFilters.status],
    () => {
        teamMeta.page = 1; // reset to first page on filter change
        if (activeTab.value === 'team') {
            fetchTeamData();
        }
    }
);

// Watch page
watch(
    () => teamMeta.page,
    () => {
        if (activeTab.value === 'team') {
            fetchTeamData();
        }
    }
);

// Fetch initial team data when tab becomes active
watch(activeTab, (newTab) => {
    if (newTab === 'team' && teamRows.value.length === 0) {
        fetchTeamData();
    }
});

// Calculate stats for current user
const totalSubmissions = computed(() => props.reports.length);

const lastSubmissionDate = computed(() => {
    if (props.reports.length === 0) return 'Never';
    
    // Find the latest report date
    const dates = props.reports.map(r => new Date(r.report_date));
    const maxDate = new Date(Math.max(...dates));
    return maxDate.toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' });
});

const averageMood = computed(() => {
    if (props.reports.length === 0) return 0;
    const moodReports = props.reports.filter(r => r.mood_rating !== null);
    if (moodReports.length === 0) return 0;
    const total = moodReports.reduce((sum, r) => sum + Number(r.mood_rating), 0);
    return (total / moodReports.length).toFixed(1);
});

const getMoodIcon = (rating) => {
    if (rating >= 4) return Smile;
    if (rating >= 3) return Meh;
    return Frown;
};

const getMoodColor = (rating) => {
    if (rating >= 4) return 'text-emerald-500';
    if (rating >= 3) return 'text-amber-500';
    return 'text-rose-500';
};

const getStatusBadgeClass = (status) => {
    switch (status) {
        case 'reviewed':
            return 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300';
        case 'submitted':
            return 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300';
        case 'late':
            return 'bg-rose-100 text-rose-800 dark:bg-rose-900/30 dark:text-rose-300';
        case 'draft':
        default:
            return 'bg-zinc-100 text-zinc-800 dark:bg-zinc-800 dark:text-zinc-300';
    }
};

const formatDate = (dateStr) => {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    return d.toLocaleDateString(undefined, { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' });
};
</script>

<template>
    <Head title="EOD Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-foreground">End of Day Reports</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                <!-- Header with primary action -->
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.3em] text-muted-foreground">Overview</p>
                        <h3 class="mt-2 text-xl font-bold text-foreground">
                            {{ activeTab === 'my' ? 'My EOD Submissions' : 'Team EOD Submissions' }}
                        </h3>
                    </div>
                    <div class="flex items-center gap-3">
                        <button
                            v-if="activeTab === 'team' && props.canViewTeamEod"
                            @click="exportTeamExcel"
                            :disabled="teamLoading"
                            class="inline-flex items-center gap-2 rounded-2xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-md transition hover:bg-emerald-500 hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <FileSpreadsheet class="size-4" />
                            Download Excel
                        </button>
                        <Link 
                            :href="route('eod.create')"
                            class="inline-flex items-center gap-2 rounded-2xl bg-primary px-5 py-2.5 text-sm font-semibold text-primary-foreground shadow-md transition-all duration-300 hover:bg-primary/90 hover:shadow-lg hover:shadow-primary/20"
                        >
                            <Plus class="size-4" />
                            Submit EOD
                        </Link>
                    </div>
                </div>

                <!-- Tab controls -->
                <div v-if="props.canViewTeamEod" class="flex flex-wrap gap-2 pb-2">
                    <button
                        @click="activeTab = 'my'"
                        class="rounded-xl px-4 py-2 text-sm font-semibold border transition duration-200"
                        :class="activeTab === 'my' ? 'bg-primary text-primary-foreground border-primary' : 'bg-background text-foreground border-border hover:bg-muted'"
                    >
                        My EOD
                    </button>
                    <button
                        @click="activeTab = 'team'"
                        class="rounded-xl px-4 py-2 text-sm font-semibold border transition duration-200"
                        :class="activeTab === 'team' ? 'bg-primary text-primary-foreground border-primary' : 'bg-background text-foreground border-border hover:bg-muted'"
                    >
                        My Team's EOD
                    </button>
                </div>

                <!-- Tab 1: My EOD Submissions -->
                <template v-if="activeTab === 'my'">
                    <!-- Stats Panel -->
                    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        <div class="rounded-[28px] border border-border bg-card p-6 shadow-sm dark:border-zinc-800/80 dark:bg-zinc-900/50">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-semibold text-muted-foreground">Total Reports</span>
                                <div class="rounded-xl bg-neutral-100 p-2 text-neutral-900 dark:bg-zinc-800 dark:text-neutral-100">
                                    <BookOpen class="size-4" />
                                </div>
                            </div>
                            <div class="mt-4">
                                <p class="text-3xl font-extrabold text-foreground">{{ totalSubmissions }}</p>
                                <p class="mt-1 text-xs text-muted-foreground">EOD submissions recorded</p>
                            </div>
                        </div>

                        <div class="rounded-[28px] border border-border bg-card p-6 shadow-sm dark:border-zinc-800/80 dark:bg-zinc-900/50">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-semibold text-muted-foreground">Last Submitted</span>
                                <div class="rounded-xl bg-neutral-100 p-2 text-neutral-900 dark:bg-zinc-800 dark:text-neutral-100">
                                    <Clock class="size-4" />
                                </div>
                            </div>
                            <div class="mt-4">
                                <p class="text-2xl font-extrabold text-foreground truncate">{{ lastSubmissionDate }}</p>
                                <p class="mt-2 text-xs text-muted-foreground">Latest log date</p>
                            </div>
                        </div>

                        <div class="rounded-[28px] border border-border bg-card p-6 shadow-sm dark:border-zinc-800/80 dark:bg-zinc-900/50">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-semibold text-muted-foreground">Avg Mood Rating</span>
                                <div class="rounded-xl bg-neutral-100 p-2 text-neutral-900 dark:bg-zinc-800 dark:text-neutral-100">
                                    <Smile class="size-4" />
                                </div>
                            </div>
                            <div class="mt-4 flex items-baseline gap-2">
                                <p class="text-3xl font-extrabold text-foreground">{{ averageMood }}</p>
                                <span class="text-xs text-muted-foreground">/ 5.0 rating</span>
                            </div>
                        </div>
                    </div>

                    <!-- Main Section: History -->
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <h4 class="text-lg font-bold text-foreground">Recent Submissions</h4>
                            <span class="rounded-full bg-muted px-2.5 py-0.5 text-xs font-semibold text-muted-foreground">
                                {{ reports.length }} Total
                            </span>
                        </div>

                        <!-- Empty State -->
                        <div v-if="reports.length === 0" class="flex flex-col items-center justify-center rounded-[28px] border border-dashed border-border bg-card p-12 text-center dark:border-zinc-800 dark:bg-zinc-900/30">
                            <div class="mx-auto flex size-12 items-center justify-center rounded-full bg-primary/10 text-primary">
                                <BookOpen class="size-6" />
                            </div>
                            <h5 class="mt-4 text-base font-semibold text-foreground">No reports found</h5>
                            <p class="mt-2 text-sm text-muted-foreground max-w-sm">
                                Keep your team in the loop by submitting your first End of Day report today.
                            </p>
                            <div class="mt-6">
                                <Link 
                                    :href="route('eod.create')" 
                                    class="inline-flex items-center gap-2 rounded-2xl bg-primary px-4 py-2.5 text-sm font-semibold text-primary-foreground shadow-xs transition hover:bg-primary/95"
                                >
                                    <Plus class="size-4" />
                                    Submit First EOD
                                </Link>
                            </div>
                        </div>

                        <!-- Reports Grid -->
                        <div v-else class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                            <div 
                                v-for="report in reports" 
                                :key="report.id" 
                                class="group relative flex flex-col justify-between overflow-hidden rounded-[24px] border border-border bg-card p-5 shadow-xs transition-all duration-300 hover:shadow-md hover:border-sidebar-accent dark:border-zinc-800 dark:bg-zinc-900/50 hover:scale-[1.01]"
                            >
                                <div class="space-y-4">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <Calendar class="size-4 text-muted-foreground" />
                                            <span class="text-sm font-semibold text-foreground">{{ formatDate(report.report_date) }}</span>
                                        </div>
                                        <span :class="`rounded-full px-2.5 py-0.5 text-xs font-semibold uppercase tracking-wider ${getStatusBadgeClass(report.status)}`">
                                            {{ report.status }}
                                        </span>
                                    </div>

                                    <div class="space-y-2">
                                        <h5 class="text-xs font-bold uppercase tracking-wider text-muted-foreground">Accomplishments</h5>
                                        <p class="line-clamp-3 text-sm text-foreground/90 leading-relaxed">
                                            {{ report.accomplishments }}
                                        </p>
                                    </div>

                                    <div v-if="report.blockers" class="space-y-1.5">
                                        <h5 class="text-xs font-bold uppercase tracking-wider text-rose-500/80 dark:text-rose-400/80 flex items-center gap-1">
                                            <AlertCircle class="size-3" />
                                            Blockers
                                        </h5>
                                        <p class="line-clamp-1 text-xs text-rose-600 dark:text-rose-400">
                                            {{ report.blockers }}
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between border-t border-border pt-4 mt-6 dark:border-zinc-800">
                                    <div class="flex items-center gap-3 text-xs text-muted-foreground">
                                        <div v-if="report.hours_logged" class="flex items-center gap-1">
                                            <Clock class="size-3.5" />
                                            <span>{{ report.hours_logged }}h</span>
                                        </div>
                                        <div v-if="report.mood_rating" class="flex items-center gap-1">
                                            <component :is="getMoodIcon(report.mood_rating)" :class="`size-3.5 ${getMoodColor(report.mood_rating)}`" />
                                            <span>Mood: {{ report.mood_rating }}</span>
                                        </div>
                                    </div>
                                    
                                    <Link 
                                        :href="route('eod.show', report.id)" 
                                        class="inline-flex size-8 items-center justify-center rounded-full bg-muted text-muted-foreground transition-colors group-hover:bg-primary group-hover:text-primary-foreground"
                                    >
                                        <ChevronRight class="size-4" />
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Tab 2: My Team's EOD (Admins/Managers) -->
                <template v-else-if="activeTab === 'team' && props.canViewTeamEod">
                    <!-- Filters Section -->
                    <div class="rounded-[28px] border border-border bg-card p-6 shadow-sm dark:border-zinc-800/80 dark:bg-zinc-900/50 space-y-4">
                        <div class="flex items-center justify-between border-b border-border pb-3 dark:border-zinc-800">
                            <h4 class="text-sm font-semibold uppercase tracking-wider text-muted-foreground flex items-center gap-2">
                                <Users class="size-4 text-primary" />
                                Filter Team Submissions
                            </h4>
                            <button 
                                @click="resetTeamFilters" 
                                class="text-xs font-semibold text-primary hover:underline flex items-center gap-1"
                            >
                                <RotateCw class="size-3" />
                                Reset Filters
                            </button>
                        </div>
                        
                        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                            <!-- Date From -->
                            <div>
                                <label class="text-xs font-bold uppercase tracking-wider text-muted-foreground">Date From</label>
                                <input 
                                    type="date" 
                                    v-model="teamFilters.date_from" 
                                    class="mt-1.5 w-full rounded-xl border border-border bg-background px-3 py-2 text-sm text-foreground focus:border-primary focus:ring-primary focus:ring-1" 
                                />
                            </div>

                            <!-- Date To -->
                            <div>
                                <label class="text-xs font-bold uppercase tracking-wider text-muted-foreground">Date To</label>
                                <input 
                                    type="date" 
                                    v-model="teamFilters.date_to" 
                                    class="mt-1.5 w-full rounded-xl border border-border bg-background px-3 py-2 text-sm text-foreground focus:border-primary focus:ring-primary focus:ring-1" 
                                />
                            </div>

                            <!-- Department Selection -->
                            <div>
                                <label class="text-xs font-bold uppercase tracking-wider text-muted-foreground">Department</label>
                                <select 
                                    v-model="teamFilters.department" 
                                    class="mt-1.5 w-full rounded-xl border border-border bg-background px-3 py-2 text-sm text-foreground focus:border-primary focus:ring-primary focus:ring-1"
                                >
                                    <option value="">All Departments</option>
                                    <option v-for="dept in props.departments" :key="dept" :value="dept">{{ dept }}</option>
                                </select>
                            </div>

                            <!-- Employee Selection -->
                            <div>
                                <label class="text-xs font-bold uppercase tracking-wider text-muted-foreground">Employee</label>
                                <select 
                                    v-model="teamFilters.employee_id" 
                                    class="mt-1.5 w-full rounded-xl border border-border bg-background px-3 py-2 text-sm text-foreground focus:border-primary focus:ring-primary focus:ring-1"
                                >
                                    <option value="">All Employees</option>
                                    <option v-for="emp in props.employees" :key="emp.id" :value="emp.id">{{ emp.name }}</option>
                                </select>
                            </div>

                            <!-- Status Selection -->
                            <div>
                                <label class="text-xs font-bold uppercase tracking-wider text-muted-foreground">Status</label>
                                <select 
                                    v-model="teamFilters.status" 
                                    class="mt-1.5 w-full rounded-xl border border-border bg-background px-3 py-2 text-sm text-foreground focus:border-primary focus:ring-primary focus:ring-1"
                                >
                                    <option value="">All Statuses</option>
                                    <option value="draft">Draft</option>
                                    <option value="submitted">Submitted</option>
                                    <option value="reviewed">Reviewed</option>
                                    <option value="late">Late</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Team EOD Table -->
                    <div class="space-y-4">
                        <!-- Error notice -->
                        <div v-if="teamError" class="rounded-2xl bg-destructive/10 border border-destructive/20 p-4 text-sm text-destructive flex items-center gap-2">
                            <AlertCircle class="size-4" />
                            <span>{{ teamError }}</span>
                        </div>

                        <!-- Main Table card -->
                        <div class="rounded-[28px] border border-border bg-card overflow-hidden shadow-sm dark:border-zinc-800/80">
                            <div class="overflow-x-auto">
                                <table class="w-full text-left border-collapse">
                                    <thead>
                                        <tr class="bg-muted/50 border-b border-border text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                            <th class="px-6 py-4">Employee</th>
                                            <th class="px-6 py-4">Date</th>
                                            <th class="px-6 py-4">Accomplishments</th>
                                            <th class="px-6 py-4">Tomorrow's Plan</th>
                                            <th class="px-6 py-4">Blockers</th>
                                            <th class="px-6 py-4">Ministries</th>
                                            <th class="px-6 py-4 text-center">Mood</th>
                                            <th class="px-6 py-4 text-center">Status</th>
                                            <th class="px-6 py-4 text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-border text-sm">
                                        <tr v-if="teamLoading" class="hover:bg-muted/10">
                                            <td colspan="9" class="px-6 py-12 text-center text-muted-foreground">
                                                <div class="flex flex-col items-center justify-center gap-3">
                                                    <svg class="animate-spin h-8 w-8 text-primary" viewBox="0 0 24 24" fill="none">
                                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                                    </svg>
                                                    <p class="font-medium">Loading EOD reports...</p>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr v-else-if="teamRows.length === 0">
                                            <td colspan="9" class="px-6 py-12 text-center text-muted-foreground">
                                                <div class="flex flex-col items-center justify-center gap-2">
                                                    <BookOpen class="size-8 text-muted-foreground/60" />
                                                    <p class="font-semibold text-base">No EOD Reports Found</p>
                                                    <p class="text-xs">No records overlap your date range criteria or current selected filter category.</p>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr v-else v-for="row in teamRows" :key="row.id" class="hover:bg-muted/20 transition duration-150">
                                            <!-- Employee Name / Position -->
                                            <td class="px-6 py-4">
                                                <div class="font-semibold text-foreground">{{ row.employee_name }}</div>
                                                <div class="text-xs text-muted-foreground mt-0.5 whitespace-nowrap">
                                                    {{ row.department }} · {{ row.position }}
                                                </div>
                                            </td>
                                            
                                            <!-- Date -->
                                            <td class="px-6 py-4 font-semibold text-foreground whitespace-nowrap">
                                                {{ formatDate(row.date) }}
                                            </td>

                                            <!-- Accomplishments -->
                                            <td class="px-6 py-4 text-foreground/90 max-w-xs truncate">
                                                {{ row.accomplishments }}
                                            </td>

                                            <!-- Tomorrow's Plan -->
                                            <td class="px-6 py-4 text-foreground/90 max-w-xs truncate">
                                                {{ row.tomorrow_plan }}
                                            </td>

                                            <!-- Blockers -->
                                            <td class="px-6 py-4">
                                                <span v-if="row.blockers" class="text-rose-600 dark:text-rose-400 font-medium text-xs bg-rose-500/10 dark:bg-rose-500/5 px-2.5 py-1 rounded-lg border border-rose-500/20">
                                                    {{ row.blockers }}
                                                </span>
                                                <span v-else class="text-muted-foreground text-xs">—</span>
                                            </td>

                                            <!-- Ministries -->
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex flex-wrap gap-1">
                                                    <span 
                                                        v-for="min in row.ministries" 
                                                        :key="min" 
                                                        class="inline-flex rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300 px-2 py-0.5 text-[10px] font-bold border border-blue-200 dark:border-blue-500/20"
                                                    >
                                                        {{ min }}
                                                    </span>
                                                    <span v-if="!row.ministries || row.ministries.length === 0" class="text-xs text-muted-foreground">—</span>
                                                </div>
                                            </td>

                                            <!-- Mood -->
                                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                                <div v-if="row.mood_rating" class="flex items-center justify-center gap-1">
                                                    <component :is="getMoodIcon(row.mood_rating)" :class="`size-4 ${getMoodColor(row.mood_rating)}`" />
                                                    <span class="text-xs font-semibold">{{ row.mood_rating }}</span>
                                                </div>
                                                <span class="text-xs text-muted-foreground" v-else>—</span>
                                            </td>

                                            <!-- Status badge -->
                                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                                <span 
                                                    class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold uppercase tracking-wider border"
                                                    :class="getStatusBadgeClass(row.status)"
                                                >
                                                    {{ row.status }}
                                                </span>
                                            </td>

                                            <!-- View Details action -->
                                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                                <Link 
                                                    :href="route('eod.show', row.id)"
                                                    class="inline-flex items-center gap-1 rounded-xl bg-muted hover:bg-primary hover:text-primary-foreground text-muted-foreground px-3 py-1.5 text-xs font-semibold transition"
                                                >
                                                    View
                                                    <ChevronRight class="size-3" />
                                                </Link>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Table Footer / Pagination -->
                            <div v-if="!teamLoading && teamRows.length > 0" class="flex flex-col sm:flex-row items-center justify-between gap-4 px-6 py-4 border-t border-border bg-muted/20 text-xs text-muted-foreground">
                                <div>
                                    Showing {{ teamRows.length }} rows of {{ teamMeta.total }} submissions
                                </div>
                                
                                <!-- Pagination Controls -->
                                <div class="flex items-center gap-2">
                                    <button
                                        @click="teamMeta.page--"
                                        :disabled="teamMeta.page <= 1"
                                        class="rounded-xl border border-border bg-background p-2 text-foreground transition hover:bg-muted disabled:opacity-50 disabled:cursor-not-allowed"
                                        title="Previous Page"
                                        type="button"
                                    >
                                        <ChevronLeft class="size-4" />
                                    </button>
                                    
                                    <span class="font-medium text-foreground">
                                        Page {{ teamMeta.page }} of {{ totalPages }}
                                    </span>
                                    
                                    <button
                                        @click="teamMeta.page++"
                                        :disabled="teamMeta.page >= totalPages"
                                        class="rounded-xl border border-border bg-background p-2 text-foreground transition hover:bg-muted disabled:opacity-50 disabled:cursor-not-allowed"
                                        title="Next Page"
                                        type="button"
                                    >
                                        <ChevronRight class="size-4" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
