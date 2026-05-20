<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                    <div class="rounded-xl bg-white p-6 shadow-sm dark:bg-gray-800">
                        <h3 class="text-sm font-semibold text-gray-500">Attendance</h3>
                        <p class="mt-4 text-3xl font-bold text-gray-900 dark:text-gray-100">Track check-ins and late status</p>
                        <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">Clock in/out with GPS and view your attendance history.</p>
                    </div>
                    <div class="rounded-xl bg-white p-6 shadow-sm dark:bg-gray-800">
                        <h3 class="text-sm font-semibold text-gray-500">Payroll</h3>
                        <p class="mt-4 text-3xl font-bold text-gray-900 dark:text-gray-100">Manage pay periods</p>
                        <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">Generate payroll periods, calculate hours, and export CSV.</p>
                    </div>
                    <div class="rounded-xl bg-white p-6 shadow-sm dark:bg-gray-800">
                        <h3 class="text-sm font-semibold text-gray-500">Tasks</h3>
                        <p class="mt-4 text-3xl font-bold text-gray-900 dark:text-gray-100">Kanban work board</p>
                        <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">Assign work, change status, and collaborate with comments.</p>
                    </div>
                    <div class="rounded-xl bg-white p-6 shadow-sm dark:bg-gray-800">
                        <h3 class="text-sm font-semibold text-gray-500">Calendar</h3>
                        <p class="mt-4 text-3xl font-bold text-gray-900 dark:text-gray-100">Events and attendance</p>
                        <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">Create events, invite attendees, and visualize schedules.</p>
                    </div>
                    <div class="rounded-xl bg-white p-6 shadow-sm dark:bg-gray-800">
                        <h3 class="text-sm font-semibold text-gray-500">Inventory</h3>
                        <p class="mt-4 text-3xl font-bold text-gray-900 dark:text-gray-100">Stock control</p>
                        <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">Record inbound/outbound movements and monitor low-stock items.</p>
                    </div>
                    <div class="rounded-xl bg-white p-6 shadow-sm dark:bg-gray-800">
                        <h3 class="text-sm font-semibold text-gray-500">Leave Requests</h3>
                        <p class="mt-4 text-3xl font-bold text-gray-900 dark:text-gray-100">Request approvals</p>
                        <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">Submit leave requests and route approvals for HR and management.</p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
