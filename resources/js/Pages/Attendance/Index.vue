<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';

const props = defineProps({
    todayRecord: Object,
    timesheets: Array,
    teamTimesheets: Array,
    pendingOtApprovals: Array,
    isAdminOrHr: Boolean,
    filters: Object,
});

const form = useForm({ latitude: '', longitude: '' });
const isClocking = ref(false);

const captureLocationAndSubmit = async (action) => {
    isClocking.value = true;

    try {
        if (!navigator.geolocation) {
            throw new Error('Geolocation not available in your browser.');
        }

        navigator.geolocation.getCurrentPosition((position) => {
            form.latitude = position.coords.latitude;
            form.longitude = position.coords.longitude;
            form.post(route(action), {
                preserveState: true,
                onFinish: () => {
                    isClocking.value = false;
                },
            });
        }, () => {
            form.post(route(action), {
                preserveState: true,
                onFinish: () => {
                    isClocking.value = false;
                },
            });
        }, { enableHighAccuracy: true, timeout: 10000 });
    } catch (error) {
        isClocking.value = false;
        form.post(route(action), {
            preserveState: true,
            onFinish: () => {
                isClocking.value = false;
            },
        });
    }
};

const todayStatus = computed(() => {
    if (!props.todayRecord) {
        return 'Not yet clocked in';
    }
    if (props.todayRecord.clock_out_time) {
        return 'Completed';
    }
    return 'Clocked in';
});

// Filters
const startDate = ref(props.filters?.start_date || '');
const endDate = ref(props.filters?.end_date || '');
const activeTab = ref('my'); // 'my', 'team', 'pending'

const applyFilters = () => {
    router.get(route('attendance.index'), {
        start_date: startDate.value,
        end_date: endDate.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    startDate.value = '';
    endDate.value = '';
    applyFilters();
};

// OT Approvals
const approveOt = (id) => {
    router.post(route('attendance.approve-ot', id), {}, {
        preserveScroll: true,
    });
};

const rejectOt = (id) => {
    router.post(route('attendance.reject-ot', id), {}, {
        preserveScroll: true,
    });
};

// Formatting helpers
const formatTime = (timeStr) => {
    if (!timeStr) return '—';
    try {
        const date = new Date(timeStr);
        return date.toLocaleTimeString(undefined, { hour: '2-digit', minute: '2-digit', hour12: true });
    } catch (e) {
        return timeStr;
    }
};

// Live ticker for ongoing shift
const liveElapsed = ref('');
let timerInterval = null;

const updateLiveTimer = () => {
    if (props.todayRecord && props.todayRecord.clock_in_time && !props.todayRecord.clock_out_time) {
        const clockIn = new Date(props.todayRecord.clock_in_time);
        const now = new Date();
        const diffMs = now - clockIn;
        const diffSecs = Math.max(0, Math.floor(diffMs / 1000));
        
        const h = Math.floor(diffSecs / 3600);
        const m = Math.floor((diffSecs % 3600) / 60);
        const s = diffSecs % 60;
        
        let parts = [];
        if (h > 0) parts.push(`${h} hrs`);
        if (m > 0) parts.push(`${m} mins`);
        if (s > 0 || parts.length === 0) parts.push(`${s} secs`);
        
        liveElapsed.value = parts.join(' ');
    } else {
        liveElapsed.value = '';
    }
};

onMounted(() => {
    updateLiveTimer();
    timerInterval = setInterval(updateLiveTimer, 1000);
});

onUnmounted(() => {
    if (timerInterval) {
        clearInterval(timerInterval);
    }
});

// Computed list based on active tab
const filteredTimesheets = computed(() => {
    if (activeTab.value === 'team') {
        return props.teamTimesheets || [];
    } else if (activeTab.value === 'pending') {
        return props.pendingOtApprovals || [];
    }
    return props.timesheets || [];
});
</script>

<template>
    <Head title="Attendance" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-1">
                <p class="text-sm font-medium uppercase tracking-[0.3em] text-muted-foreground">Attendance</p>
                <h2 class="text-2xl font-semibold leading-tight text-foreground">Track time and attendance</h2>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                <!-- Combined Today widget -->
                <section class="rounded-[28px] border border-border bg-card p-6 shadow-sm">
                    <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                        <div class="space-y-4">
                            <div class="flex items-center gap-3">
                                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-muted-foreground">Today's Shift</p>
                                <span class="rounded-full bg-primary/10 px-3 py-1 text-xs font-semibold text-primary dark:bg-primary/15 dark:text-primary-foreground">
                                    {{ todayStatus }}
                                </span>
                            </div>
                            
                            <div class="flex flex-wrap items-center gap-x-6 gap-y-4">
                                <div>
                                    <p class="text-3xl font-semibold text-foreground tracking-tight">
                                        {{ props.todayRecord ? (props.todayRecord.status === 'late' ? 'Late Arrival' : 'On Time') : 'Ready to Start' }}
                                    </p>
                                    <p class="mt-1 text-sm text-muted-foreground">
                                        {{ props.todayRecord ? (props.todayRecord.status === 'late' ? 'Late arrival recorded for today.' : 'You checked in on time.') : 'Ready to start your shift.' }}
                                    </p>
                                </div>

                                <div class="flex flex-wrap items-center gap-4 border-t border-border pt-4 lg:border-t-0 lg:border-l lg:pt-0 lg:pl-6">
                                    <div class="rounded-2xl bg-muted px-4 py-2 text-xs text-muted-foreground border border-border">
                                        <p class="font-medium text-foreground">Clock In</p>
                                        <p class="mt-0.5 font-semibold text-sm">{{ formatTime(props.todayRecord?.clock_in_time) }}</p>
                                    </div>
                                    <div class="rounded-2xl bg-muted px-4 py-2 text-xs text-muted-foreground border border-border">
                                        <p class="font-medium text-foreground">Clock Out</p>
                                        <p class="mt-0.5 font-semibold text-sm">{{ formatTime(props.todayRecord?.clock_out_time) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                            <button
                                @click.prevent="captureLocationAndSubmit('attendance.clock-in')"
                                class="inline-flex items-center justify-center rounded-2xl bg-primary px-6 py-3 text-sm font-semibold text-primary-foreground shadow-sm transition hover:bg-primary/90 disabled:cursor-not-allowed disabled:opacity-60"
                                :disabled="isClocking || (props.todayRecord && props.todayRecord.clock_in_time)"
                            >
                                <svg v-if="isClocking" class="mr-2 h-4 w-4 animate-spin text-primary-foreground" viewBox="0 0 24 24" fill="none">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                {{ props.todayRecord && props.todayRecord.clock_in_time ? 'Clocked In' : 'Clock In' }}
                            </button>
                            <button
                                @click.prevent="captureLocationAndSubmit('attendance.clock-out')"
                                class="inline-flex items-center justify-center rounded-2xl bg-emerald-600 px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-500 disabled:cursor-not-allowed disabled:opacity-60"
                                :disabled="isClocking || !props.todayRecord || props.todayRecord.clock_out_time"
                            >
                                <svg v-if="isClocking" class="mr-2 h-4 w-4 animate-spin text-white" viewBox="0 0 24 24" fill="none">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                {{ props.todayRecord && props.todayRecord.clock_out_time ? 'Clocked Out' : 'Clock Out' }}
                            </button>
                        </div>
                    </div>
                </section>

                <!-- Timesheets Table Section -->
                <section class="rounded-[28px] border border-border bg-card p-6 shadow-sm space-y-6">
                    <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                        <!-- Left: Title / Tab controls -->
                        <div class="space-y-3">
                            <h2 class="text-2xl font-bold tracking-tight text-foreground">Timesheets</h2>
                            
                            <div v-if="props.isAdminOrHr" class="flex flex-wrap gap-2">
                                <button
                                    @click="activeTab = 'my'"
                                    class="rounded-xl px-4 py-2 text-xs font-semibold border transition"
                                    :class="activeTab === 'my' ? 'bg-primary text-primary-foreground border-primary' : 'bg-background text-foreground border-border hover:bg-muted'"
                                >
                                    My Timesheets
                                </button>
                                <button
                                    @click="activeTab = 'team'"
                                    class="rounded-xl px-4 py-2 text-xs font-semibold border transition"
                                    :class="activeTab === 'team' ? 'bg-primary text-primary-foreground border-primary' : 'bg-background text-foreground border-border hover:bg-muted'"
                                >
                                    Team Timesheets
                                </button>
                                <button
                                    @click="activeTab = 'pending'"
                                    class="relative rounded-xl px-4 py-2 text-xs font-semibold border transition"
                                    :class="activeTab === 'pending' ? 'bg-primary text-primary-foreground border-primary' : 'bg-background text-foreground border-border hover:bg-muted'"
                                >
                                    Pending OT Approvals
                                    <span v-if="props.pendingOtApprovals?.length" class="absolute -top-1.5 -right-1.5 flex h-4 w-4 items-center justify-center rounded-full bg-destructive text-[10px] font-bold text-destructive-foreground">
                                        {{ props.pendingOtApprovals.length }}
                                    </span>
                                </button>
                            </div>
                        </div>

                        <!-- Right: Filters & Live counter -->
                        <div class="flex flex-wrap items-center gap-6 lg:justify-end">
                            <!-- Live ticking counter -->
                            <div v-if="liveElapsed" class="flex items-center gap-2 rounded-2xl bg-muted px-4 py-2 border border-border text-sm font-semibold text-muted-foreground shadow-inner">
                                <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                <span>{{ liveElapsed }}</span>
                                <button @click="updateLiveTimer" class="ml-1 text-muted-foreground hover:text-foreground transition rounded-lg hover:bg-muted p-0.5" title="Refresh timer">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 7.89M9 11l3-3 3 3m-3-3v12"></path></svg>
                                </button>
                            </div>

                            <!-- Date range filters -->
                            <div class="flex flex-wrap items-center gap-3">
                                <div>
                                    <input 
                                        type="date" 
                                        v-model="startDate" 
                                        class="rounded-xl border-border bg-background text-xs font-medium text-foreground shadow-sm focus:border-primary focus:ring-primary"
                                    />
                                </div>
                                <span class="text-muted-foreground text-xs font-medium">to</span>
                                <div>
                                    <input 
                                        type="date" 
                                        v-model="endDate" 
                                        class="rounded-xl border-border bg-background text-xs font-medium text-foreground shadow-sm focus:border-primary focus:ring-primary"
                                    />
                                </div>
                                <button 
                                    @click="applyFilters" 
                                    class="inline-flex items-center justify-center rounded-xl bg-primary px-4 py-2 text-xs font-semibold text-primary-foreground shadow-sm hover:bg-primary/90 transition"
                                >
                                    Filter
                                </button>
                                <button 
                                    @click="clearFilters" 
                                    class="inline-flex items-center justify-center rounded-xl border border-border bg-background px-4 py-2 text-xs font-semibold text-foreground shadow-sm hover:bg-muted transition"
                                >
                                    Clear
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Table component -->
                    <div class="overflow-x-auto rounded-2xl border border-border">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-muted/50 border-b border-border text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                    <th class="px-6 py-4">Date</th>
                                    <th class="px-6 py-4">Team</th>
                                    <th class="px-6 py-4 text-right">Shift</th>
                                    <th class="px-6 py-4 text-right">Actual</th>
                                    <th class="px-6 py-4 text-right">Rate</th>
                                    <th class="px-6 py-4 text-right">Est. Payment</th>
                                    <th class="px-6 py-4 text-center">Status</th>
                                    <th v-if="props.isAdminOrHr" class="px-6 py-4 text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border text-sm">
                                <tr v-for="record in filteredTimesheets" :key="record.id" class="hover:bg-muted/20 transition duration-150">
                                    <!-- Date column -->
                                    <td class="px-6 py-4 font-semibold text-foreground whitespace-nowrap">
                                        {{ record.date }}
                                    </td>
                                    
                                    <!-- Team column (Department + Position) -->
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-foreground">{{ record.user_name }}</div>
                                        <div class="text-xs text-muted-foreground mt-0.5 whitespace-nowrap">
                                            {{ record.user_department }} · {{ record.user_position }}
                                        </div>
                                    </td>
                                    
                                    <!-- Shift column -->
                                    <td class="px-6 py-4 text-right font-medium text-muted-foreground whitespace-nowrap">
                                        {{ record.shift_hours }}
                                    </td>
                                    
                                    <!-- Actual column (live-ticking if Ongoing) -->
                                    <td class="px-6 py-4 text-right font-semibold text-foreground whitespace-nowrap">
                                        {{ record.status === 'Ongoing' ? (liveElapsed || record.actual_duration) : record.actual_duration }}
                                    </td>
                                    
                                    <!-- Rate column -->
                                    <td class="px-6 py-4 text-right font-medium text-muted-foreground whitespace-nowrap">
                                        {{ record.rate }}
                                    </td>
                                    
                                    <!-- Est. Payment column -->
                                    <td class="px-6 py-4 text-right font-bold text-foreground whitespace-nowrap">
                                        {{ record.estimated_payment }}
                                    </td>
                                    
                                    <!-- Status column -->
                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        <span 
                                            class="inline-flex rounded-full px-3 py-1 text-xs font-semibold border"
                                            :class="{
                                                'bg-muted text-muted-foreground border-border': record.status === 'Ongoing',
                                                'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/10 dark:text-emerald-400 border-emerald-200 dark:border-emerald-500/20': record.status === 'Approved',
                                                'bg-amber-100 text-amber-800 dark:bg-amber-500/10 dark:text-amber-400 border-amber-200 dark:border-amber-500/20': record.status === 'Request for approval'
                                            }"
                                        >
                                            {{ record.status }}
                                        </span>
                                    </td>

                                    <!-- Actions column (Approve/Reject OT) -->
                                    <td v-if="props.isAdminOrHr" class="px-6 py-4 text-center whitespace-nowrap">
                                        <div v-if="record.status === 'Request for approval'" class="flex items-center justify-center gap-2">
                                            <button 
                                                @click="approveOt(record.id)"
                                                class="rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white px-2.5 py-1.5 text-xs font-semibold shadow-sm transition"
                                                title="Approve OT"
                                            >
                                                Approve
                                            </button>
                                            <button 
                                                @click="rejectOt(record.id)"
                                                class="rounded-lg bg-destructive hover:bg-destructive/90 text-destructive-foreground px-2.5 py-1.5 text-xs font-semibold shadow-sm transition"
                                                title="Reject OT"
                                            >
                                                Reject
                                            </button>
                                        </div>
                                        <span v-else class="text-xs text-muted-foreground">
                                            {{ record.ot_status === 'approved' ? 'OT Approved' : (record.ot_status === 'rejected' ? 'OT Rejected' : '—') }}
                                        </span>
                                    </td>
                                </tr>
                                
                                <tr v-if="!filteredTimesheets.length">
                                    <td :colspan="props.isAdminOrHr ? 8 : 7" class="px-6 py-10 text-center text-muted-foreground">
                                        <div class="flex flex-col items-center justify-center gap-2">
                                            <svg class="h-8 w-8 text-muted-foreground/60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                            <p class="font-semibold text-base">No Timesheet Records Found</p>
                                            <p class="text-xs max-w-sm">No records overlap your date range criteria or current selected tab category.</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Footer: page description / summary counts -->
                    <div class="flex items-center justify-between text-xs text-muted-foreground pt-4 border-t border-border">
                        <p>Showing {{ filteredTimesheets.length }} timesheet rows</p>
                        <p>Rates are determined by shift configuration or employee base hourly rate.</p>
                    </div>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
