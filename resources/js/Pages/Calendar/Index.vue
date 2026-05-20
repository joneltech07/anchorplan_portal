<script setup>
import { ref, computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    events: Array,
    users: Array,
});

const calendarEvents = computed(() => props.events.map((event) => ({
    id: event.id,
    title: event.title,
    start: event.start_time,
    end: event.end_time,
    backgroundColor: event.type === 'holiday' ? '#f59e0b' : event.type === 'leave' ? '#10b981' : '#6366f1',
    borderColor: event.type === 'holiday' ? '#f59e0b' : event.type === 'leave' ? '#10b981' : '#6366f1',
})));

const calendarPlugins = [dayGridPlugin, timeGridPlugin, interactionPlugin];
const calendarHeaderToolbar = {
    left: 'prev,next today',
    center: 'title',
    right: 'dayGridMonth,timeGridWeek,timeGridDay',
};
const initialView = 'dayGridMonth';

const form = useForm({ title: '', description: '', start_time: '', end_time: '', type: 'meeting', attendees: [] });

const submitEvent = () => {
    form.post(route('calendar.store'));
};

const clearForm = () => {
    form.title = '';
    form.description = '';
    form.start_time = '';
    form.end_time = '';
    form.type = 'meeting';
    form.attendees = [];
};
</script>

<template>
    <Head title="Calendar" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Calendar Events
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 grid gap-6 xl:grid-cols-3">
                <section class="rounded-xl bg-white p-6 shadow-sm dark:bg-gray-800 xl:col-span-1">
                    <h3 class="text-sm font-semibold text-gray-500">Create Event</h3>
                    <div class="mt-5 space-y-4">
                        <input v-model="form.title" placeholder="Event title" class="w-full rounded-md border-gray-300 p-3 shadow-sm dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100" />
                        <textarea v-model="form.description" placeholder="Description" class="w-full rounded-md border-gray-300 p-3 shadow-sm dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100"></textarea>
                        <input v-model="form.start_time" type="datetime-local" class="w-full rounded-md border-gray-300 p-3 shadow-sm dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100" />
                        <input v-model="form.end_time" type="datetime-local" class="w-full rounded-md border-gray-300 p-3 shadow-sm dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100" />
                        <select v-model="form.type" class="w-full rounded-md border-gray-300 p-3 shadow-sm dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">
                            <option value="meeting">Meeting</option>
                            <option value="holiday">Holiday</option>
                            <option value="leave">Leave</option>
                            <option value="other">Other</option>
                        </select>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Invite Attendees</label>
                            <select v-model="form.attendees" multiple class="mt-2 w-full rounded-md border-gray-300 p-3 shadow-sm dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">
                                <option v-for="user in props.users" :key="user.id" :value="user.id">{{ user.name }}</option>
                            </select>
                        </div>
                        <div class="flex gap-2">
                            <button @click.prevent="submitEvent" class="rounded-md bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-500">Create Event</button>
                            <button @click.prevent="clearForm" class="rounded-md bg-gray-100 px-4 py-2 text-gray-700 hover:bg-gray-200 dark:bg-gray-900 dark:text-gray-200">Reset</button>
                        </div>
                    </div>
                </section>

                <section class="xl:col-span-2 rounded-xl bg-white p-6 shadow-sm dark:bg-gray-800">
                    <FullCalendar
                        :plugins="calendarPlugins"
                        :initial-view="initialView"
                        :events="calendarEvents"
                        :header-toolbar="calendarHeaderToolbar"
                        height="auto"
                    />
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
