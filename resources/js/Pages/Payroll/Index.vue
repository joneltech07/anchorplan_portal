<script setup>
import { ref, computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';

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
            <!-- Header removed for responsive full-width layout -->
        </template>

        <div class="py-12">
            <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">
                <section class="rounded-[28px] border border-border bg-card p-6 shadow-sm w-full">
                    <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-[0.3em] text-muted-foreground">Generate payroll period</p>
                            <h3 class="mt-2 text-xl font-semibold text-foreground">Create a new payroll cycle</h3>
                        </div>
                        <button type="submit" form="payroll-form" class="inline-flex items-center rounded-2xl bg-primary px-5 py-2.5 text-sm font-semibold text-primary-foreground shadow-sm transition hover:bg-primary/90">
                            Create Period
                        </button>
                    </div>

                    <form id="payroll-form" class="mt-6 grid gap-4 md:grid-cols-3" @submit.prevent="form.post(route('payroll.periods.store'))">
                        <label class="space-y-2">
                            <span class="text-sm font-medium text-foreground">Period name</span>
                            <input v-model="form.name" type="text" placeholder="e.g. May 2026" class="block w-full rounded-2xl border border-border bg-muted px-4 py-3 text-sm text-foreground shadow-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20" />
                        </label>
                        <label class="space-y-2">
                            <span class="text-sm font-medium text-foreground">Start date</span>
                            <input v-model="form.start_date" type="date" class="block w-full rounded-2xl border border-border bg-muted px-4 py-3 text-sm text-foreground shadow-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20" />
                        </label>
                        <label class="space-y-2">
                            <span class="text-sm font-medium text-foreground">End date</span>
                            <input v-model="form.end_date" type="date" class="block w-full rounded-2xl border border-border bg-muted px-4 py-3 text-sm text-foreground shadow-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20" />
                        </label>
                    </form>
                </section>

                <section class="rounded-[28px] border border-border bg-card p-6 shadow-sm w-full">
                    <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-[0.3em] text-muted-foreground">Payroll history</p>
                            <h3 class="mt-2 text-xl font-semibold text-foreground">Recent payroll items</h3>
                        </div>
                        <p class="text-sm text-muted-foreground">Browse generated periods and employee slips.</p>
                    </div>

                    <div v-if="props.employeeSlips.length" class="mt-6 overflow-x-auto">
                        <table class="min-w-full divide-y divide-border text-sm">
                            <thead class="bg-muted text-left text-xs uppercase tracking-[0.2em] text-muted-foreground">
                                <tr>
                                    <th class="px-4 py-3">Period</th>
                                    <th class="px-4 py-3">Regular hours</th>
                                    <th class="px-4 py-3">Overtime</th>
                                    <th class="px-4 py-3">Net pay</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                <tr v-for="item in props.employeeSlips" :key="item.id" class="hover:bg-muted/50">
                                    <td class="px-4 py-4">{{ item.payroll_period.name }}</td>
                                    <td class="px-4 py-4">{{ item.regular_hours }}</td>
                                    <td class="px-4 py-4">{{ item.overtime_hours }}</td>
                                    <td class="px-4 py-4 font-semibold text-foreground">${{ item.net_pay }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="mt-6 rounded-3xl border border-border bg-muted p-6 text-sm text-muted-foreground">
                        No payroll items available yet.
                    </div>
                </section>

                <section v-if="hasPeriods" class="rounded-[28px] border border-border bg-card p-6 shadow-sm w-full">
                    <p class="text-sm font-semibold uppercase tracking-[0.3em] text-muted-foreground">Payroll periods</p>
                    <div class="mt-5 grid gap-4 md:grid-cols-2">
                        <div v-for="period in props.periods" :key="period.id" class="rounded-3xl border border-border bg-muted p-5">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="font-semibold text-foreground">{{ period.name }}</p>
                                    <p class="mt-1 text-sm text-muted-foreground">{{ period.start_date }} — {{ period.end_date }}</p>
                                </div>
                                <span class="rounded-full px-3 py-1 text-xs font-semibold" :class="period.status === 'active' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-200/15 dark:text-emerald-200' : 'bg-neutral-100 text-neutral-800 dark:bg-neutral-800 dark:text-neutral-200'">{{ period.status }}</span>
                            </div>
                            <div class="mt-5 flex flex-wrap gap-2">
                                <a :href="route('payroll.export', { id: period.id })" class="rounded-2xl bg-muted px-4 py-2 text-sm text-foreground transition hover:bg-muted/80">Export CSV</a>
                                <form :action="route('payroll.calculate', { id: period.id })" method="post" class="inline-block">
                                    <input type="hidden" name="_token" :value="$page.props.csrfToken" />
                                    <button type="submit" class="rounded-2xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-500">Recalculate</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
