<script setup lang="ts">
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Clock, CreditCard, ListTodo, Calendar, Package, UserMinus } from '@lucide/vue';

declare const route: any;
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Grid Layout matching Laravel 13 aesthetic with real portal card content -->
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                
                <!-- Attendance Card -->
                <Link :href="route('attendance.index')" class="group relative flex flex-col justify-between overflow-hidden rounded-xl border border-sidebar-border/70 bg-card p-6 shadow-xs transition-all hover:shadow-md hover:border-sidebar-accent dark:border-sidebar-border dark:bg-zinc-900/50">
                    <div class="flex items-center gap-4">
                        <div class="flex size-10 items-center justify-center rounded-lg bg-neutral-100 text-neutral-900 transition-colors group-hover:bg-neutral-900 group-hover:text-white dark:bg-zinc-800 dark:text-neutral-100 dark:group-hover:bg-white dark:group-hover:text-black">
                            <Clock class="size-5" />
                        </div>
                        <h3 class="font-semibold text-foreground">Attendance</h3>
                    </div>
                    <div class="mt-4">
                        <p class="text-2xl font-bold text-foreground">Track check-ins</p>
                        <p class="mt-2 text-sm text-muted-foreground">Clock in/out with GPS and view your attendance history.</p>
                    </div>
                </Link>

                <!-- Payroll Card -->
                <Link :href="route('payroll.index')" class="group relative flex flex-col justify-between overflow-hidden rounded-xl border border-sidebar-border/70 bg-card p-6 shadow-xs transition-all hover:shadow-md hover:border-sidebar-accent dark:border-sidebar-border dark:bg-zinc-900/50">
                    <div class="flex items-center gap-4">
                        <div class="flex size-10 items-center justify-center rounded-lg bg-neutral-100 text-neutral-900 transition-colors group-hover:bg-neutral-900 group-hover:text-white dark:bg-zinc-800 dark:text-neutral-100 dark:group-hover:bg-white dark:group-hover:text-black">
                            <CreditCard class="size-5" />
                        </div>
                        <h3 class="font-semibold text-foreground">Payroll</h3>
                    </div>
                    <div class="mt-4">
                        <p class="text-2xl font-bold text-foreground">Manage periods</p>
                        <p class="mt-2 text-sm text-muted-foreground">Generate payroll periods, calculate hours, and export CSV.</p>
                    </div>
                </Link>

                <!-- Tasks Card -->
                <Link :href="route('tasks.index')" class="group relative flex flex-col justify-between overflow-hidden rounded-xl border border-sidebar-border/70 bg-card p-6 shadow-xs transition-all hover:shadow-md hover:border-sidebar-accent dark:border-sidebar-border dark:bg-zinc-900/50">
                    <div class="flex items-center gap-4">
                        <div class="flex size-10 items-center justify-center rounded-lg bg-neutral-100 text-neutral-900 transition-colors group-hover:bg-neutral-900 group-hover:text-white dark:bg-zinc-800 dark:text-neutral-100 dark:group-hover:bg-white dark:group-hover:text-black">
                            <ListTodo class="size-5" />
                        </div>
                        <h3 class="font-semibold text-foreground">Tasks</h3>
                    </div>
                    <div class="mt-4">
                        <p class="text-2xl font-bold text-foreground">Kanban board</p>
                        <p class="mt-2 text-sm text-muted-foreground">Assign work, change status, and collaborate with comments.</p>
                    </div>
                </Link>

                <!-- Calendar Card -->
                <Link :href="route('calendar.index')" class="group relative flex flex-col justify-between overflow-hidden rounded-xl border border-sidebar-border/70 bg-card p-6 shadow-xs transition-all hover:shadow-md hover:border-sidebar-accent dark:border-sidebar-border dark:bg-zinc-900/50">
                    <div class="flex items-center gap-4">
                        <div class="flex size-10 items-center justify-center rounded-lg bg-neutral-100 text-neutral-900 transition-colors group-hover:bg-neutral-900 group-hover:text-white dark:bg-zinc-800 dark:text-neutral-100 dark:group-hover:bg-white dark:group-hover:text-black">
                            <Calendar class="size-5" />
                        </div>
                        <h3 class="font-semibold text-foreground">Calendar</h3>
                    </div>
                    <div class="mt-4">
                        <p class="text-2xl font-bold text-foreground">Events</p>
                        <p class="mt-2 text-sm text-muted-foreground">Create events, invite attendees, and visualize schedules.</p>
                    </div>
                </Link>

                <!-- Inventory Card -->
                <Link :href="route('inventory.index')" class="group relative flex flex-col justify-between overflow-hidden rounded-xl border border-sidebar-border/70 bg-card p-6 shadow-xs transition-all hover:shadow-md hover:border-sidebar-accent dark:border-sidebar-border dark:bg-zinc-900/50">
                    <div class="flex items-center gap-4">
                        <div class="flex size-10 items-center justify-center rounded-lg bg-neutral-100 text-neutral-900 transition-colors group-hover:bg-neutral-900 group-hover:text-white dark:bg-zinc-800 dark:text-neutral-100 dark:group-hover:bg-white dark:group-hover:text-black">
                            <Package class="size-5" />
                        </div>
                        <h3 class="font-semibold text-foreground">Inventory</h3>
                    </div>
                    <div class="mt-4">
                        <p class="text-2xl font-bold text-foreground">Stock control</p>
                        <p class="mt-2 text-sm text-muted-foreground">Record inbound/outbound movements and monitor low-stock items.</p>
                    </div>
                </Link>

                <!-- Leave Requests Card -->
                <Link :href="route('leave.index')" class="group relative flex flex-col justify-between overflow-hidden rounded-xl border border-sidebar-border/70 bg-card p-6 shadow-xs transition-all hover:shadow-md hover:border-sidebar-accent dark:border-sidebar-border dark:bg-zinc-900/50">
                    <div class="flex items-center gap-4">
                        <div class="flex size-10 items-center justify-center rounded-lg bg-neutral-100 text-neutral-900 transition-colors group-hover:bg-neutral-900 group-hover:text-white dark:bg-zinc-800 dark:text-neutral-100 dark:group-hover:bg-white dark:group-hover:text-black">
                            <UserMinus class="size-5" />
                        </div>
                        <h3 class="font-semibold text-foreground">Leave Requests</h3>
                    </div>
                    <div class="mt-4">
                        <p class="text-2xl font-bold text-foreground">Approvals</p>
                        <p class="mt-2 text-sm text-muted-foreground">Submit leave requests and route approvals for HR and management.</p>
                    </div>
                </Link>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
