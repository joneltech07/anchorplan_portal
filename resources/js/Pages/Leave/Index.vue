<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';

const props = defineProps({
    requests: Array,
    users: Array,
});

const form = useForm({ type: 'sick', start_date: '', end_date: '', user_id: '' });
</script>

<template>
    <Head title="Leave Requests" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-1">
                <p class="text-sm font-medium uppercase tracking-[0.3em] text-muted-foreground">Leave requests</p>
                <h2 class="text-2xl font-semibold leading-tight text-foreground">Manage requests with clarity</h2>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                <section class="rounded-[28px] border border-border bg-card p-6 shadow-sm">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-[0.3em] text-muted-foreground">Submit request</p>
                            <h3 class="mt-2 text-xl font-semibold text-foreground">Request time off</h3>
                            <p class="mt-2 text-sm leading-6 text-muted-foreground">Choose a leave type, set the range, and submit your request for approval.</p>
                        </div>
                        <button @click.prevent="form.post(route('leave.store'))" class="inline-flex items-center rounded-2xl bg-primary px-5 py-2.5 text-sm font-semibold text-primary-foreground shadow-sm transition hover:bg-primary/90">
                            Submit Request
                        </button>
                    </div>

                    <div class="mt-6 grid gap-4 lg:grid-cols-4">
                        <label class="space-y-2">
                            <span class="text-sm font-medium text-foreground">Leave type</span>
                            <select v-model="form.type" class="block w-full rounded-2xl border border-border bg-muted px-4 py-3 text-sm text-foreground shadow-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
                                <option value="sick">Sick</option>
                                <option value="vacation">Vacation</option>
                                <option value="casual">Casual</option>
                                <option value="unpaid">Unpaid</option>
                            </select>
                        </label>
                        <label class="space-y-2">
                            <span class="text-sm font-medium text-foreground">Start date</span>
                            <input v-model="form.start_date" type="date" class="block w-full rounded-2xl border border-border bg-muted px-4 py-3 text-sm text-foreground shadow-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20" />
                        </label>
                        <label class="space-y-2">
                            <span class="text-sm font-medium text-foreground">End date</span>
                            <input v-model="form.end_date" type="date" class="block w-full rounded-2xl border border-border bg-muted px-4 py-3 text-sm text-foreground shadow-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20" />
                        </label>
                        <label class="space-y-2">
                            <span class="text-sm font-medium text-foreground">Requesting for</span>
                            <select v-model="form.user_id" class="block w-full rounded-2xl border border-border bg-muted px-4 py-3 text-sm text-foreground shadow-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
                                <option value="">Select employee</option>
                                <option v-for="user in props.users" :key="user.id" :value="user.id">{{ user.name }}</option>
                            </select>
                        </label>
                    </div>
                </section>

                <section class="rounded-[28px] border border-border bg-card p-6 shadow-sm">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-[0.3em] text-muted-foreground">Leave history</p>
                            <h3 class="mt-2 text-xl font-semibold text-foreground">All requests</h3>
                        </div>
                        <p class="text-sm text-muted-foreground">Review status and request details at a glance.</p>
                    </div>
                    <div class="mt-6 overflow-x-auto">
                        <table class="min-w-full divide-y divide-border text-sm text-foreground">
                            <thead class="bg-muted text-left text-xs uppercase tracking-[0.2em] text-muted-foreground">
                                <tr>
                                    <th class="px-4 py-3">Employee</th>
                                    <th class="px-4 py-3">Type</th>
                                    <th class="px-4 py-3">Period</th>
                                    <th class="px-4 py-3">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                <tr v-for="request in props.requests" :key="request.id" class="hover:bg-muted/60">
                                    <td class="px-4 py-4">{{ request.user.name }}</td>
                                    <td class="px-4 py-4">{{ request.type }}</td>
                                    <td class="px-4 py-4">{{ request.start_date }} — {{ request.end_date }}</td>
                                    <td class="px-4 py-4">
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold" :class="request.status === 'approved' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-200/15 dark:text-emerald-200' : request.status === 'pending' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-200/15 dark:text-yellow-200' : 'bg-rose-100 text-rose-800 dark:bg-rose-200/15 dark:text-rose-200'">{{ request.status }}</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
