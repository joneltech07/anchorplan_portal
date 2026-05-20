<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

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
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Leave Requests
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                <section class="rounded-xl bg-white p-6 shadow-sm dark:bg-gray-800">
                    <h3 class="text-sm font-semibold text-gray-500">Submit Leave Request</h3>
                    <div class="mt-5 grid gap-4 lg:grid-cols-4">
                        <select v-model="form.type" class="rounded-md border-gray-300 p-3 shadow-sm dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">
                            <option value="sick">Sick</option>
                            <option value="vacation">Vacation</option>
                            <option value="casual">Casual</option>
                            <option value="unpaid">Unpaid</option>
                        </select>
                        <input v-model="form.start_date" type="date" class="rounded-md border-gray-300 p-3 shadow-sm dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100" />
                        <input v-model="form.end_date" type="date" class="rounded-md border-gray-300 p-3 shadow-sm dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100" />
                        <select v-model="form.user_id" class="rounded-md border-gray-300 p-3 shadow-sm dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">
                            <option value="">Who is requesting?</option>
                            <option v-for="user in props.users" :key="user.id" :value="user.id">{{ user.name }}</option>
                        </select>
                    </div>
                    <div class="mt-4">
                        <button @click.prevent="form.post(route('leave.store'))" class="rounded-md bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-500">Submit Request</button>
                    </div>
                </section>

                <section class="rounded-xl bg-white p-6 shadow-sm dark:bg-gray-800">
                    <h3 class="text-sm font-semibold text-gray-500">Leave History</h3>
                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Employee</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Type</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Period</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="request in props.requests" :key="request.id">
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">{{ request.user.name }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">{{ request.type }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">{{ request.start_date }} — {{ request.end_date }}</td>
                                    <td class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ request.status }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
