<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';

const props = defineProps({ report: Object });

</script>

<template>
    <Head title="EOD Report" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-xl font-semibold leading-tight text-foreground">EOD Report</h2>
                <div>
                    <!-- Edit is handled by backend authorization (draft or owner) -->
                    <Link
                        v-if="props.report?.status === 'draft' || props.report?.user_id === $page.props.auth.user.id"
                        :href="route('eod.edit', props.report.id)"
                        class="inline-flex items-center rounded-2xl bg-primary px-4 py-2 text-sm font-semibold text-primary-foreground shadow-xs transition hover:bg-primary/95"
                    >
                        Edit
                    </Link>
                </div>
            </div>
        </template>


        <div class="py-12">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <section class="rounded-[28px] border border-border bg-card p-6 shadow-sm dark:border-sidebar-border dark:bg-zinc-900">
                    <h3 class="text-lg font-semibold">{{ props.report.user?.name ?? 'User' }} — {{ props.report.report_date }}</h3>
                    <div class="mt-4 space-y-3">
                        <div>
                            <h4 class="font-medium">Accomplishments</h4>
                            <p class="text-sm text-muted-foreground">{{ props.report.accomplishments }}</p>
                        </div>
                        <div>
                            <h4 class="font-medium">Tomorrow Plan</h4>
                            <p class="text-sm text-muted-foreground">{{ props.report.tomorrow_plan }}</p>
                        </div>
                        <div>
                            <h4 class="font-medium">Blockers</h4>
                            <p class="text-sm text-muted-foreground">{{ props.report.blockers }}</p>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
