<script setup>
import { ref, computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import draggable from 'vuedraggable';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

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
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Task Board
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                <section class="rounded-xl bg-white p-6 shadow-sm dark:bg-gray-800">
                    <h3 class="text-sm font-semibold text-gray-500">Create Task</h3>
                    <div class="mt-6 grid gap-4 lg:grid-cols-2">
                        <input v-model="form.title" placeholder="Task title" class="rounded-md border-gray-300 p-3 shadow-sm dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100" />
                        <select v-model="form.assigned_to" class="rounded-md border-gray-300 p-3 shadow-sm dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">
                            <option value="">Assign to</option>
                            <option v-for="member in props.team" :key="member.id" :value="member.id">{{ member.name }} — {{ member.role }}</option>
                        </select>
                        <select v-model="form.priority" class="rounded-md border-gray-300 p-3 shadow-sm dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                            <option value="critical">Critical</option>
                        </select>
                        <input v-model="form.due_date" type="date" class="rounded-md border-gray-300 p-3 shadow-sm dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100" />
                        <textarea v-model="form.description" placeholder="Task description" class="col-span-full min-h-[100px] rounded-md border-gray-300 p-3 shadow-sm dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100"></textarea>
                    </div>
                    <div class="mt-4">
                        <button @click.prevent="submitTask" class="rounded-md bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-500">Create Task</button>
                    </div>
                </section>

                <section class="grid gap-4 xl:grid-cols-4">
                    <div v-for="column in columns" :key="column.key" class="rounded-xl bg-white p-4 shadow-sm dark:bg-gray-800">
                        <div class="mb-4 flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-300">{{ column.label }}</h3>
                            <span :class="column.color + ' rounded-full px-2 py-1 text-xs font-semibold'">{{ grouped[column.key].length }}</span>
                        </div>
                        <draggable v-model="grouped[column.key]" item-key="id">
                            <template #item="{ element }">
                                <div class="mb-4 rounded-xl border border-gray-200 p-4 dark:border-gray-700">
                                    <div class="flex items-center justify-between">
                                        <h4 class="font-semibold text-gray-900 dark:text-gray-100">{{ element.title }}</h4>
                                        <span class="text-xs text-gray-500">Due {{ element.due_date || 'n/a' }}</span>
                                    </div>
                                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Assigned to: {{ element.assignee?.name || 'Unassigned' }}</p>
                                    <div class="mt-3 space-y-2 text-sm text-gray-500 dark:text-gray-400">
                                        <p>{{ element.description }}</p>
                                        <p>{{ element.comments.length }} comments</p>
                                    </div>
                                </div>
                            </template>
                        </draggable>
                    </div>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
