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
    Users
} from '@lucide/vue';
import { computed } from 'vue';

const props = defineProps({
    role: String,
    reports: {
        type: Array,
        default: () => []
    }
});

// Calculate stats
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
                        <h3 class="mt-2 text-xl font-bold text-foreground">My EOD Submissions</h3>
                    </div>
                    <div class="flex items-center gap-3">
                        <Link 
                            v-if="['manager', 'hr', 'gm'].includes(role)" 
                            :href="route('eod.team')"
                            class="inline-flex items-center gap-2 rounded-2xl border border-border bg-card px-4 py-2.5 text-sm font-semibold text-foreground shadow-xs transition hover:bg-muted dark:border-zinc-800"
                        >
                            <Users class="size-4" />
                            Team EODs
                        </Link>
                        <Link 
                            :href="route('eod.create')"
                            class="inline-flex items-center gap-2 rounded-2xl bg-primary px-5 py-2.5 text-sm font-semibold text-primary-foreground shadow-md transition-all duration-300 hover:bg-primary/90 hover:shadow-lg hover:shadow-primary/20"
                        >
                            <Plus class="size-4" />
                            Submit EOD
                        </Link>
                    </div>
                </div>

                <!-- Manager Info Banner -->
                <div v-if="['manager', 'hr', 'gm'].includes(role)" class="relative overflow-hidden rounded-[28px] border border-primary/20 bg-gradient-to-r from-primary/5 via-transparent to-transparent p-6 dark:border-primary/30">
                    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                        <div class="space-y-1">
                            <h4 class="text-base font-bold text-foreground flex items-center gap-2">
                                <Sparkles class="size-4 text-primary animate-pulse" />
                                Manager View Available
                            </h4>
                            <p class="text-sm text-muted-foreground">You can review, approve, and track submissions for your team members.</p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <Link v-if="role === 'manager' || role === 'gm' || role === 'hr'" :href="route('eod.team')" class="rounded-xl bg-primary/10 px-4 py-2 text-xs font-semibold text-primary transition hover:bg-primary/20">
                                Team Reports
                            </Link>
                            <Link v-if="role === 'gm'" :href="route('eod.gm')" class="rounded-xl bg-muted px-4 py-2 text-xs font-semibold text-foreground transition hover:bg-muted/80">
                                GM View
                            </Link>
                            <Link v-if="role === 'hr'" :href="route('eod.hr')" class="rounded-xl bg-muted px-4 py-2 text-xs font-semibold text-foreground transition hover:bg-muted/80">
                                HR View
                            </Link>
                        </div>
                    </div>
                </div>

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
            </div>
        </div>
    </AuthenticatedLayout>
</template>
