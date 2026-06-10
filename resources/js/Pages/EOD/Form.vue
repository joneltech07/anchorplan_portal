<script setup>
import { Head, useForm } from '@inertiajs/vue3';



// Use Inertia session auth cookies for sanctum
// axios instance comes from global bootstrap.js
axios.defaults.withCredentials = true;
axios.defaults.headers.common['Accept'] = 'application/json';



import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import MinistrySection from '@/pages/EOD/MinistrySection.vue';


const form = useForm({
    report_date: new Date().toISOString().slice(0,10),
    accomplishments: '',
    tomorrow_plan: '',
    blockers: '',
    hours_logged: '',
    task_ids_completed: [],
    mood_rating: 3,

    // Spiritual / Ministry involvement
ministry_types: ['none'],
other_description: '',
});

const submit = async () => {
    // Existing EOD submission
    const resp = await form.post(route('eod.store'));

    // Persist ministry involvement via API (Pastoral Lead module requirement)
    // Note: if your backend EOD store already returns a report id, we can include it; otherwise API uses user_id+date.
    // Use non-API (web/auth) axios endpoint instead of /api/v1 to avoid unauth issues
    // (Laravel will use the authenticated session)
    try {
        await axios.post(route('eod.ministry.store'), {








            // If your server is not mounted under /api, also consider using route('...')

            report_date: form.report_date,
            ministry_types: form.ministry_types,
            other_description: form.other_description,
        });
    } catch (e) {
        // non-blocking; EOD already saved
        console.error('Failed to save ministry involvement', e);
    }

    return resp;
};
</script>

<template>
    <Head title="Create EOD" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-foreground">Submit End of Day</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <section class="rounded-[28px] border border-border bg-card p-6 shadow-sm dark:border-sidebar-border dark:bg-zinc-900">
                    <div class="space-y-4">
                        <textarea v-model="form.accomplishments" placeholder="Accomplishments" class="w-full rounded-2xl border border-border bg-muted px-4 py-3 text-sm text-foreground"></textarea>
                        <textarea v-model="form.tomorrow_plan" placeholder="Plan for tomorrow" class="w-full rounded-2xl border border-border bg-muted px-4 py-3 text-sm text-foreground"></textarea>
                        <textarea v-model="form.blockers" placeholder="Blockers" class="w-full rounded-2xl border border-border bg-muted px-4 py-3 text-sm text-foreground"></textarea>

                        <div class="pt-6">
                            <div class="text-sm font-semibold uppercase tracking-[0.3em] text-muted-foreground">Spiritual Formation</div>
                            <div class="mt-3">
                                <MinistrySection v-model="form.ministry_types" :other-description="form.other_description" @update:otherDescription="(v) => form.other_description = v" />
                            </div>
                        </div>

                        <div class="flex gap-3">
                            <button @click.prevent="submit" class="rounded-2xl bg-primary px-4 py-2 text-sm font-semibold text-primary-foreground">Submit</button>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
