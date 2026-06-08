<script setup>
import { ref, computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';

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

const calendarOptions = computed(() => ({
    plugins: calendarPlugins,
    initialView,
    events: calendarEvents.value,
    headerToolbar: calendarHeaderToolbar,
    height: 'auto',
}));

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
            <h2 class="text-xl font-semibold leading-tight text-foreground">
                Calendar Events
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 grid gap-6 xl:grid-cols-3">
                <section class="rounded-[28px] border border-border bg-card p-6 shadow-sm xl:col-span-1 dark:border-sidebar-border dark:bg-zinc-900">
                    <h3 class="text-sm font-semibold uppercase tracking-[0.16em] text-muted-foreground">Create Event</h3>
                    <div class="mt-5 space-y-4">
                        <input v-model="form.title" placeholder="Event title" class="w-full rounded-2xl border border-border bg-muted px-4 py-3 text-sm text-foreground shadow-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-sidebar-border dark:bg-zinc-950 dark:text-foreground" />
                        <textarea v-model="form.description" placeholder="Description" class="w-full rounded-2xl border border-border bg-muted px-4 py-3 text-sm text-foreground shadow-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-sidebar-border dark:bg-zinc-950 dark:text-foreground"></textarea>
                        <input v-model="form.start_time" type="datetime-local" class="w-full rounded-2xl border border-border bg-muted px-4 py-3 text-sm text-foreground shadow-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-sidebar-border dark:bg-zinc-950 dark:text-foreground" />
                        <input v-model="form.end_time" type="datetime-local" class="w-full rounded-2xl border border-border bg-muted px-4 py-3 text-sm text-foreground shadow-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-sidebar-border dark:bg-zinc-950 dark:text-foreground" />
                        <select v-model="form.type" class="w-full rounded-2xl border border-border bg-muted px-4 py-3 text-sm text-foreground shadow-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-sidebar-border dark:bg-zinc-950 dark:text-foreground">
                            <option value="meeting">Meeting</option>
                            <option value="holiday">Holiday</option>
                            <option value="leave">Leave</option>
                            <option value="other">Other</option>
                        </select>
                        <div>
                            <label class="block text-sm font-medium text-foreground">Invite Attendees</label>
                            <select v-model="form.attendees" multiple class="mt-2 w-full rounded-2xl border border-border bg-muted px-4 py-3 text-sm text-foreground shadow-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-sidebar-border dark:bg-zinc-950 dark:text-foreground">
                                <option v-for="user in props.users" :key="user.id" :value="user.id">{{ user.name }}</option>
                            </select>
                        </div>
                        <div class="flex flex-wrap gap-3">
                            <button @click.prevent="submitEvent" class="rounded-2xl bg-primary px-4 py-2 text-sm font-semibold text-primary-foreground transition hover:bg-primary/90">Create Event</button>
                            <button @click.prevent="clearForm" class="rounded-2xl bg-muted px-4 py-2 text-sm font-semibold text-foreground transition hover:bg-muted/80">Reset</button>
                        </div>
                    </div>
                </section>

                <section class="xl:col-span-2 rounded-[28px] border border-border bg-card p-6 shadow-sm dark:border-sidebar-border dark:bg-zinc-900">
                    <div class="min-h-[680px] rounded-[24px] border border-border bg-muted p-4 dark:border-sidebar-border dark:bg-zinc-950">
                        <FullCalendar :options="calendarOptions" />
                    </div>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
