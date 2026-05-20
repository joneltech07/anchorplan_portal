<script setup>
import { computed, ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

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
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Attendance
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                <div class="grid gap-6 lg:grid-cols-3">
                    <div class="rounded-xl bg-white p-6 shadow-sm dark:bg-gray-800">
                        <h3 class="text-sm font-semibold text-gray-500">Today's Status</h3>
                        <p class="mt-4 text-2xl font-bold text-gray-900 dark:text-gray-100">{{ todayStatus }}</p>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                            {{ props.todayRecord ? (props.todayRecord.status === 'late' ? 'Late' : 'On time') : 'Ready to start your shift.' }}
                        </p>
                    </div>

                    <div class="rounded-xl bg-white p-6 shadow-sm dark:bg-gray-800 lg:col-span-2">
                        <h3 class="text-sm font-semibold text-gray-500">Clock Controls</h3>
                        <div class="mt-6 flex flex-col gap-4 sm:flex-row">
                            <button
                                @click.prevent="captureLocationAndSubmit('attendance.clock-in')"
                                class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-white shadow-sm hover:bg-indigo-500"
                                :disabled="isClocking || (props.todayRecord && props.todayRecord.clock_in_time)"
                            >
                                {{ props.todayRecord && props.todayRecord.clock_in_time ? 'Clocked In' : 'Clock In' }}
                            </button>
                            <button
                                @click.prevent="captureLocationAndSubmit('attendance.clock-out')"
                                class="inline-flex items-center justify-center rounded-md bg-emerald-600 px-4 py-2 text-white shadow-sm hover:bg-emerald-500"
                                :disabled="isClocking || !props.todayRecord || props.todayRecord.clock_out_time"
                            >
                                {{ props.todayRecord && props.todayRecord.clock_out_time ? 'Clocked Out' : 'Clock Out' }}
                            </button>
                        </div>
                        <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">
                            GPS coordinates are captured automatically if your browser allows location.
                        </p>
                    </div>
                </div>

                <div class="grid gap-6 lg:grid-cols-2">
                    <section class="rounded-xl bg-white p-6 shadow-sm dark:bg-gray-800">
                        <h3 class="text-sm font-semibold text-gray-500">Last 7 Days</h3>
                        <div class="mt-4 space-y-3">
                            <div
                                v-for="record in props.history"
                                :key="record.id"
                                class="rounded-lg border border-gray-200 p-4 dark:border-gray-700"
                            >
                                <div class="flex items-center justify-between">
                                    <p class="font-medium text-gray-800 dark:text-gray-200">{{ record.date }}</p>
                                    <span
                                        :class="record.status === 'late' ? 'text-amber-600' : 'text-green-600'"
                                        class="text-sm font-semibold"
                                    >
                                        {{ record.status }}
                                    </span>
                                </div>
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                    Clock In: {{ record.clock_in_time || '—' }} · Clock Out: {{ record.clock_out_time || '—' }}
                                </p>
                            </div>
                        </div>
                    </section>

                    <section class="rounded-xl bg-white p-6 shadow-sm dark:bg-gray-800">
                        <h3 class="text-sm font-semibold text-gray-500">Team Attendance</h3>
                        <div class="mt-4">
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div
                                    v-for="employee in props.teamEmployees"
                                    :key="employee.id"
                                    class="rounded-lg border border-gray-200 p-4 dark:border-gray-700"
                                >
                                    <p class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ employee.name }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ employee.role }}</p>
                                </div>
                            </div>
                            <p class="mt-4 text-xs text-gray-400" v-if="!props.teamEmployees.length">
                                Team data is visible to admins, managers and HR only.
                            </p>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
