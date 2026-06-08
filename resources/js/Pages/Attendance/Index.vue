<script setup>
import { computed, ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';

const props = defineProps({
    todayRecord: Object,
    history: Array,
    teamEmployees: Array,
    teamAttendance: Array,
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
                <section class="rounded-[28px] border border-border bg-card p-6 shadow-sm">
                    <div class="flex flex-col gap-6 xl:flex-row xl:items-center xl:justify-between">
                        <div class="space-y-3">
                            <p class="text-sm font-semibold uppercase tracking-[0.3em] text-muted-foreground">Today</p>
                            <div class="flex flex-wrap items-center gap-4">
                                <h1 class="text-3xl font-semibold text-foreground">Attendance dashboard</h1>
                                <span class="rounded-full bg-primary/10 px-3 py-1 text-sm font-medium text-primary dark:bg-primary/15 dark:text-primary-foreground">{{ todayStatus }}</span>
                            </div>
                            <p class="max-w-2xl text-sm leading-6 text-muted-foreground">Clock in, clock out, and monitor the team attendance history from one clean, consistent dashboard.</p>
                        </div>
                        <div class="flex flex-col gap-3 sm:flex-row">
                            <button
                                @click.prevent="captureLocationAndSubmit('attendance.clock-in')"
                                class="inline-flex items-center justify-center rounded-2xl bg-primary px-5 py-2.5 text-sm font-semibold text-primary-foreground shadow-sm transition hover:bg-primary/90 disabled:cursor-not-allowed disabled:opacity-60"
                                :disabled="isClocking || (props.todayRecord && props.todayRecord.clock_in_time)"
                            >
                                {{ props.todayRecord && props.todayRecord.clock_in_time ? 'Clocked In' : 'Clock In' }}
                            </button>
                            <button
                                @click.prevent="captureLocationAndSubmit('attendance.clock-out')"
                                class="inline-flex items-center justify-center rounded-2xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-500 disabled:cursor-not-allowed disabled:opacity-60"
                                :disabled="isClocking || !props.todayRecord || props.todayRecord.clock_out_time"
                            >
                                {{ props.todayRecord && props.todayRecord.clock_out_time ? 'Clocked Out' : 'Clock Out' }}
                            </button>
                        </div>
                    </div>
                </section>

                <div class="grid gap-6 xl:grid-cols-3">
                    <section class="rounded-[28px] border border-border bg-card p-6 shadow-sm">
                        <p class="text-sm font-semibold uppercase tracking-[0.3em] text-muted-foreground">Current status</p>
                        <div class="mt-4 space-y-3">
                            <p class="text-3xl font-semibold text-foreground">{{ todayStatus }}</p>
                            <p class="text-sm leading-6 text-muted-foreground">{{ props.todayRecord ? (props.todayRecord.status === 'late' ? 'Late arrival' : 'On time today') : 'Ready to start your shift.' }}</p>
                            <div class="grid gap-2 sm:grid-cols-2">
                                <div class="rounded-2xl bg-muted px-4 py-3 text-sm text-muted-foreground">
                                    <p class="font-medium text-foreground">Clock In</p>
                                    <p class="mt-1">{{ props.todayRecord?.clock_in_time ?? '—' }}</p>
                                </div>
                                <div class="rounded-2xl bg-muted px-4 py-3 text-sm text-muted-foreground">
                                    <p class="font-medium text-foreground">Clock Out</p>
                                    <p class="mt-1">{{ props.todayRecord?.clock_out_time ?? '—' }}</p>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="rounded-[28px] border border-border bg-card p-6 shadow-sm xl:col-span-2">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p class="text-sm font-semibold uppercase tracking-[0.3em] text-muted-foreground">Points</p>
                                <h2 class="mt-2 text-xl font-semibold text-foreground">Clock controls</h2>
                            </div>
                            <p class="text-sm text-muted-foreground">Your location is captured if permitted by the browser.</p>
                        </div>
                        <div class="mt-6 grid gap-4 sm:grid-cols-2">
                            <div class="rounded-2xl border border-border bg-muted p-4">
                                <p class="text-sm font-medium text-foreground">Today&apos;s shift</p>
                                <p class="mt-3 text-sm leading-6 text-muted-foreground">{{ props.todayRecord ? (props.todayRecord.status === 'late' ? 'Late arrival recorded' : 'On schedule') : 'Use the buttons to start or end your day.' }}</p>
                            </div>
                            <div class="rounded-2xl border border-border bg-muted p-4">
                                <p class="text-sm font-medium text-foreground">Attendance note</p>
                                <p class="mt-3 text-sm leading-6 text-muted-foreground">{{ props.todayRecord ? 'Shift details are saved automatically.' : 'Make sure location access is enabled.' }}</p>
                            </div>
                        </div>
                    </section>
                </div>

                <div class="grid gap-6 xl:grid-cols-2">
                    <section class="rounded-[28px] border border-border bg-card p-6 shadow-sm">
                        <p class="text-sm font-semibold uppercase tracking-[0.3em] text-muted-foreground">Last 7 days</p>
                        <div class="mt-5 space-y-4">
                            <div
                                v-for="record in props.history"
                                :key="record.id"
                                class="rounded-3xl border border-border bg-muted p-4"
                            >
                                <div class="flex items-center justify-between gap-3">
                                    <div>
                                        <p class="font-semibold text-foreground">{{ record.date }}</p>
                                        <p class="text-sm text-muted-foreground">Clock In: {{ record.clock_in_time || '—' }} · Clock Out: {{ record.clock_out_time || '—' }}</p>
                                    </div>
                                    <span class="rounded-full px-3 py-1 text-xs font-semibold" :class="record.status === 'late' ? 'bg-amber-100 text-amber-800 dark:bg-amber-200/20 dark:text-amber-300' : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-200/20 dark:text-emerald-300'">{{ record.status }}</span>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="rounded-[28px] border border-border bg-card p-6 shadow-sm">
                        <p class="text-sm font-semibold uppercase tracking-[0.3em] text-muted-foreground">Team attendance</p>
                        <div class="mt-5 grid gap-4 sm:grid-cols-2">
                            <div
                                v-for="employee in props.teamEmployees"
                                :key="employee.id"
                                class="rounded-3xl border border-border bg-muted p-4"
                            >
                                <p class="font-semibold text-foreground">{{ employee.name }}</p>
                                <p class="mt-1 text-sm text-muted-foreground">{{ employee.role }}</p>
                            </div>
                        </div>
                        <p class="mt-4 text-xs text-muted-foreground" v-if="!props.teamEmployees.length">Team data is visible to admins, managers and HR only.</p>
                    </section>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
