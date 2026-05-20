<script setup>
import { ref, computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    periods: Array,
    employeeSlips: Array,
});

const form = useForm({
    name: '',
    start_date: '',
    end_date: '',
});

const hasPeriods = computed(() => props.periods.length > 0);
</script>

<template>
    <Head title="Payroll" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Payroll Management
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                <section class="rounded-xl bg-white p-6 shadow-sm dark:bg-gray-800">
                    <h3 class="text-sm font-semibold text-gray-500">Generate Payroll Period</h3>
                    <form class="mt-5 space-y-4" @submit.prevent="form.post(route('payroll.periods.store'))">
                        <div class="grid gap-4 sm:grid-cols-3">
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Period Name</label>
                                <input v-model="form.name" type="text" class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100" />
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Start Date</label>
                                <input v-model="form.start_date" type="date" class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100" />
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">End Date</label>
                                <input v-model="form.end_date" type="date" class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100" />
                            </div>
                        </div>
                        <button type="submit" class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-500">
                            Create Period
                        </button>
                    </form>
                </section>

                <section class="rounded-xl bg-white p-6 shadow-sm dark:bg-gray-800">
                    <h3 class="text-sm font-semibold text-gray-500">Payroll History</h3>
                    <div v-if="props.employeeSlips.length" class="mt-6 overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Period</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Regular Hours</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Overtime</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Net Pay</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="item in props.employeeSlips" :key="item.id">
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">{{ item.payroll_period.name }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">{{ item.regular_hours }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">{{ item.overtime_hours }}</td>
                                    <td class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-gray-100">${{ item.net_pay }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="mt-6 text-sm text-gray-500 dark:text-gray-400">No payroll items available yet.</div>
                </section>

                <section v-if="hasPeriods" class="rounded-xl bg-white p-6 shadow-sm dark:bg-gray-800">
                    <h3 class="text-sm font-semibold text-gray-500">Payroll Periods</h3>
                    <div class="mt-4 grid gap-4 md:grid-cols-2">
                        <div v-for="period in props.periods" :key="period.id" class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-semibold text-gray-900 dark:text-gray-100">{{ period.name }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ period.start_date }} — {{ period.end_date }}</p>
                                </div>
                                <span class="rounded-full bg-blue-100 px-2 py-1 text-xs font-semibold text-blue-700 dark:bg-blue-900 dark:text-blue-200">{{ period.status }}</span>
                            </div>
                            <div class="mt-4 flex flex-wrap gap-2">
                                <a :href="route('payroll.export', { id: period.id })" class="rounded-md bg-gray-100 px-3 py-2 text-sm text-gray-700 hover:bg-gray-200 dark:bg-gray-900 dark:text-gray-200">Export CSV</a>
                                <form :action="route('payroll.calculate', { id: period.id })" method="post">
                                    <input type="hidden" name="_token" :value="$page.props.csrfToken" />
                                    <button type="submit" class="rounded-md bg-emerald-600 px-3 py-2 text-sm text-white hover:bg-emerald-500">Recalculate</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
