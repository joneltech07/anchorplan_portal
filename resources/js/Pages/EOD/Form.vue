<script setup>
import { Head, useForm } from '@inertiajs/vue3';



// Use Inertia session auth cookies for sanctum
// axios instance comes from global bootstrap.js
axios.defaults.withCredentials = true;
axios.defaults.headers.common['Accept'] = 'application/json';



import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import MinistrySection from '@/pages/EOD/MinistrySection.vue';


const props = defineProps({
    report: Object,
});

const report = props.report ?? null;

const form = useForm({
    // For edit prefill: backend sends report.ministryInvolvements including ministry_type
    // MinistrySection 'none' should only be selected when the saved selections truly are none.

    report_date: props.report?.report_date ? new Date(props.report.report_date).toISOString().slice(0, 10) : new Date().toISOString().slice(0, 10),
    accomplishments: props.report?.accomplishments ?? '',
    tomorrow_plan: props.report?.tomorrow_plan ?? '',
    blockers: props.report?.blockers ?? '',
    hours_logged: props.report?.hours_logged ?? '',
    task_ids_completed: props.report?.task_ids_completed ?? [],
    mood_rating: props.report?.mood_rating ?? 3,

    // Spiritual / Ministry involvement
    ministry_types: (() => {
        const relation = props.report?.ministry_involvements ?? props.report?.ministryInvolvements;
        const ministries = relation?.length
            ? relation.map((m) => m.ministry_type)
            : [];

        // The MinistrySection treats 'none' as a special case. If the saved data
        // contains both 'none' and real options, prefer the real options.
        if (ministries.includes('none')) {
            const real = ministries.filter((x) => x !== 'none');
            if (real.length) return real;
            return ['none'];
        }

        return ministries.length ? ministries : ['none'];
    })(),
    other_description: (() => {
        const relation = props.report?.ministry_involvements ?? props.report?.ministryInvolvements;
        if (!relation) return '';
        const otherMin = relation.find(m => m.ministry_type === 'other');
        return otherMin ? (otherMin.custom_description ?? '') : '';
    })(),
});

const submit = () => {
    if (props.report?.id) {
        form.put(route('eod.update', props.report.id));
    } else {
        form.post(route('eod.store'));
    }
};
</script>

<template>
    <Head :title="props.report?.id ? 'Edit EOD' : 'Create EOD'" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-foreground">{{ props.report?.id ? 'Edit End of Day' : 'Submit End of Day' }}</h2>
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
