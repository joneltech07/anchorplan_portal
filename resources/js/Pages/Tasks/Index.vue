<script setup>
import { ref, computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import draggable from 'vuedraggable';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';

const props = defineProps({
    tasks: Array,
    team: Array,
});

const columns = [
    { key: 'pending', label: 'Pending', color: 'bg-yellow-100 text-yellow-800' },
    { key: 'in_progress', label: 'In Progress', color: 'bg-blue-100 text-blue-800' },
    { key: 'review', label: 'Review', color: 'bg-indigo-100 text-indigo-800' },
    { key: 'completed', label: 'Completed', color: 'bg-emerald-100 text-emerald-800' },
];

const grouped = computed(() => {
    return columns.reduce((carry, column) => {
        carry[column.key] = props.tasks.filter((task) => task.status === column.key);
        return carry;
    }, {});
});

const form = useForm({ title: '', description: '', assigned_to: '', priority: 'medium', due_date: '' });

const submitTask = () => {
    form.post(route('tasks.store'));
};
</script>

<template>
    <Head title="Task Board" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-1">
                <p class="text-sm font-medium uppercase tracking-[0.3em] text-muted-foreground">Tasks</p>
                <h2 class="text-2xl font-semibold leading-tight text-foreground">Organize work like Laravel 13</h2>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                <section class="rounded-[28px] border border-border bg-card p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                    <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-[0.3em] text-muted-foreground">Create task</p>
                            <h3 class="mt-2 text-xl font-semibold text-foreground">Add a new ticket</h3>
                        </div>
                        <button @click.prevent="submitTask" class="inline-flex items-center rounded-2xl bg-primary px-5 py-2.5 text-sm font-semibold text-primary-foreground shadow-sm transition hover:bg-primary/90">
                            Create Task
                        </button>
                    </div>

                    <div class="mt-6 grid gap-4 lg:grid-cols-2">
                        <input v-model="form.title" placeholder="Task title" class="block w-full rounded-2xl border border-border bg-muted px-4 py-3 text-sm text-foreground shadow-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-100" />

                        <select v-model="form.assigned_to" class="block w-full rounded-2xl border border-border bg-muted px-4 py-3 text-sm text-foreground shadow-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-100">
                            <option value="">Assign to</option>
                            <option v-for="member in props.team" :key="member.id" :value="member.id">{{ member.name }} — {{ member.role }}</option>
                        </select>

                        <select v-model="form.priority" class="block w-full rounded-2xl border border-border bg-muted px-4 py-3 text-sm text-foreground shadow-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-100">
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                            <option value="critical">Critical</option>
                        </select>

                        <input v-model="form.due_date" type="date" class="block w-full rounded-2xl border border-border bg-muted px-4 py-3 text-sm text-foreground shadow-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-100" />

                        <textarea v-model="form.description" placeholder="Task description" class="col-span-full min-h-[120px] rounded-2xl border border-border bg-muted px-4 py-3 text-sm text-foreground shadow-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-100"></textarea>
                    </div>
                </section>

                <section class="grid gap-4 xl:grid-cols-4">
                    <div v-for="column in columns" :key="column.key" class="rounded-[28px] border border-border bg-card p-4 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                        <div class="mb-4 flex items-center justify-between gap-3">
                            <h3 class="text-sm font-semibold text-foreground">{{ column.label }}</h3>
                            <span :class="column.color + ' rounded-full px-2 py-1 text-xs font-semibold'">{{ grouped[column.key].length }}</span>
                        </div>
                        <draggable v-model="grouped[column.key]" item-key="id">
                            <template #item="{ element }">
                                <div class="mb-4 rounded-3xl border border-border bg-muted p-4 dark:border-slate-800 dark:bg-slate-900">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <h4 class="font-semibold text-foreground">{{ element.title }}</h4>
                                            <p class="mt-2 text-sm text-muted-foreground">Assigned to: {{ element.assignee?.name || 'Unassigned' }}</p>
                                        </div>
                                        <span class="text-xs text-muted-foreground">Due {{ element.due_date || 'n/a' }}</span>
                                    </div>
                                    <p class="mt-4 text-sm leading-6 text-muted-foreground">{{ element.description }}</p>
                                    <p class="mt-3 text-xs text-muted-foreground">{{ element.comments.length }} comments</p>
                                </div>
                            </template>
                        </draggable>
                    </div>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
