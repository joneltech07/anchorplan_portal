<script setup>
import GuestLayout from '@/layouts/GuestLayout.vue';
import InputError from '@/components/InputError.vue';
import InputLabel from '@/components/InputLabel.vue';
import PrimaryButton from '@/components/PrimaryButton.vue';
import TextInput from '@/components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    last_name: '',
    first_name: '',
    middle_name: '',
    sex: 'Male',
    birth_date: '',
    birth_place: '',
    nationality: '',
    civil_status: 'Single',
    current_address: '',
    provincial_address: '',
    contact_number: '',
    father_full_name: '',
    father_occupation: '',
    mother_full_name: '',
    mother_occupation: '',
    guardian: '',
    guardian_contact_number: '',
    date_employed: '',
    employee_status: 'Trainee',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout :wide="true">
        <Head title="Register" />

        <section class="rounded-[28px] border border-border bg-card p-8 shadow-sm dark:border-sidebar-border dark:bg-zinc-900">
            <div class="mb-8">
                <h1 class="text-2xl font-semibold text-white">Create your account</h1>
                <p class="mt-2 text-sm text-muted-foreground">
                    Complete employee onboarding with secure login, profile data, and employment details.
                </p>
            </div>

            <form @submit.prevent="submit">
                <div class="space-y-8">
                    <section class="rounded-3xl border border-border bg-muted p-6 shadow-sm dark:border-sidebar-border dark:bg-zinc-950">
                        <h2 class="text-lg font-semibold text-white">Personal information</h2>
                        <p class="mt-1 text-sm text-muted-foreground">Enter your full legal name and profile details.</p>

                        <div class="mt-6 grid gap-4 md:grid-cols-3">
                            <div>
                                <InputLabel for="last_name" value="Last Name" />
                                <TextInput
                                    id="last_name"
                                    type="text"
                                    class="mt-1"
                                    v-model="form.last_name"
                                    required
                                    autocomplete="family-name"
                                />
                                <InputError class="mt-2" :message="form.errors.last_name" />
                            </div>

                            <div>
                                <InputLabel for="first_name" value="First Name" />
                                <TextInput
                                    id="first_name"
                                    type="text"
                                    class="mt-1"
                                    v-model="form.first_name"
                                    required
                                    autocomplete="given-name"
                                />
                                <InputError class="mt-2" :message="form.errors.first_name" />
                            </div>

                            <div>
                                <InputLabel for="middle_name" value="Middle Name" />
                                <TextInput
                                    id="middle_name"
                                    type="text"
                                    class="mt-1"
                                    v-model="form.middle_name"
                                    autocomplete="additional-name"
                                />
                                <InputError class="mt-2" :message="form.errors.middle_name" />
                            </div>
                        </div>
                    </section>

                    <section class="rounded-3xl border border-border bg-muted p-6 shadow-sm dark:border-sidebar-border dark:bg-zinc-950">
                        <h2 class="text-lg font-semibold text-white">Contact & address</h2>
                        <p class="mt-1 text-sm text-muted-foreground">Where we can reach you and your address information.</p>

                        <div class="mt-6 grid gap-4 md:grid-cols-3">
                            <div>
                                <InputLabel for="sex" value="Sex" />
                                <select
                                    id="sex"
                                    class="mt-1 block w-full rounded-2xl border border-border bg-muted px-3 py-2 text-sm text-foreground shadow-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-sidebar-border dark:bg-zinc-950 dark:text-foreground"
                                    v-model="form.sex"
                                    required
                                >
                                    <option>Male</option>
                                    <option>Female</option>
                                    <option>Other</option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.sex" />
                            </div>

                            <div>
                                <InputLabel for="birth_date" value="Birth Date" />
                                <TextInput
                                    id="birth_date"
                                    type="date"
                                    class="mt-1"
                                    v-model="form.birth_date"
                                    required
                                />
                                <InputError class="mt-2" :message="form.errors.birth_date" />
                            </div>

                            <div>
                                <InputLabel for="birth_place" value="Birth Place" />
                                <TextInput
                                    id="birth_place"
                                    type="text"
                                    class="mt-1"
                                    v-model="form.birth_place"
                                    required
                                />
                                <InputError class="mt-2" :message="form.errors.birth_place" />
                            </div>
                        </div>

                        <div class="mt-4 grid gap-4 md:grid-cols-3">
                            <div>
                                <InputLabel for="nationality" value="Nationality" />
                                <TextInput
                                    id="nationality"
                                    type="text"
                                    class="mt-1"
                                    v-model="form.nationality"
                                    required
                                />
                                <InputError class="mt-2" :message="form.errors.nationality" />
                            </div>

                            <div>
                                <InputLabel for="civil_status" value="Civil Status" />
                                <select
                                    id="civil_status"
                                    class="mt-1 block w-full rounded-2xl border border-border bg-muted px-3 py-2 text-sm text-foreground shadow-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-sidebar-border dark:bg-zinc-950 dark:text-foreground"
                                    v-model="form.civil_status"
                                    required
                                >
                                    <option>Single</option>
                                    <option>Married</option>
                                    <option>Widowed</option>
                                    <option>Separated</option>
                                    <option>Divorced</option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.civil_status" />
                            </div>

                            <div>
                                <InputLabel for="contact_number" value="Contact Number" />
                                <TextInput
                                    id="contact_number"
                                    type="text"
                                    class="mt-1"
                                    v-model="form.contact_number"
                                    required
                                />
                                <InputError class="mt-2" :message="form.errors.contact_number" />
                            </div>
                        </div>

                        <div class="mt-4">
                            <InputLabel for="current_address" value="Current Address" />
                            <TextInput
                                id="current_address"
                                type="text"
                                class="mt-1"
                                v-model="form.current_address"
                                required
                            />
                            <InputError class="mt-2" :message="form.errors.current_address" />
                        </div>

                        <div class="mt-4">
                            <InputLabel for="provincial_address" value="Provincial Address" />
                            <TextInput
                                id="provincial_address"
                                type="text"
                                class="mt-1"
                                v-model="form.provincial_address"
                            />
                            <InputError class="mt-2" :message="form.errors.provincial_address" />
                        </div>
                    </section>

                    <section class="rounded-3xl border border-border bg-muted p-6 shadow-sm dark:border-sidebar-border dark:bg-zinc-950">
                        <h2 class="text-lg font-semibold text-white">Family details</h2>
                        <p class="mt-1 text-sm text-muted-foreground">Provide emergency contact and next of kin information.</p>

                        <div class="mt-6 grid gap-4 md:grid-cols-2">
                            <div>
                                <InputLabel for="father_full_name" value="Father's Full Name" />
                                <TextInput
                                    id="father_full_name"
                                    type="text"
                                    class="mt-1"
                                    v-model="form.father_full_name"
                                    required
                                />
                                <InputError class="mt-2" :message="form.errors.father_full_name" />
                            </div>

                            <div>
                                <InputLabel for="father_occupation" value="Father's Occupation" />
                                <TextInput
                                    id="father_occupation"
                                    type="text"
                                    class="mt-1"
                                    v-model="form.father_occupation"
                                    required
                                />
                                <InputError class="mt-2" :message="form.errors.father_occupation" />
                            </div>
                        </div>

                        <div class="mt-4 grid gap-4 md:grid-cols-2">
                            <div>
                                <InputLabel for="mother_full_name" value="Mother's Full Name" />
                                <TextInput
                                    id="mother_full_name"
                                    type="text"
                                    class="mt-1"
                                    v-model="form.mother_full_name"
                                    required
                                />
                                <InputError class="mt-2" :message="form.errors.mother_full_name" />
                            </div>

                            <div>
                                <InputLabel for="mother_occupation" value="Mother's Occupation" />
                                <TextInput
                                    id="mother_occupation"
                                    type="text"
                                    class="mt-1"
                                    v-model="form.mother_occupation"
                                    required
                                />
                                <InputError class="mt-2" :message="form.errors.mother_occupation" />
                            </div>
                        </div>

                        <div class="mt-4 grid gap-4 md:grid-cols-2">
                            <div>
                                <InputLabel for="guardian" value="Guardian" />
                                <TextInput
                                    id="guardian"
                                    type="text"
                                    class="mt-1"
                                    v-model="form.guardian"
                                    required
                                />
                                <InputError class="mt-2" :message="form.errors.guardian" />
                            </div>

                            <div>
                                <InputLabel for="guardian_contact_number" value="Guardian's Contact Number" />
                                <TextInput
                                    id="guardian_contact_number"
                                    type="text"
                                    class="mt-1"
                                    v-model="form.guardian_contact_number"
                                    required
                                />
                                <InputError class="mt-2" :message="form.errors.guardian_contact_number" />
                            </div>
                        </div>
                    </section>

                    <section class="rounded-3xl border border-border bg-muted p-6 shadow-sm dark:border-sidebar-border dark:bg-zinc-950">
                        <h2 class="text-lg font-semibold text-white">Employment details</h2>
                        <p class="mt-1 text-sm text-muted-foreground">Track your start date and employment status.</p>

                        <div class="mt-6 grid gap-4 md:grid-cols-2">
                            <div>
                                <InputLabel for="date_employed" value="Date Employed" />
                                <TextInput
                                    id="date_employed"
                                    type="date"
                                    class="mt-1"
                                    v-model="form.date_employed"
                                    required
                                />
                                <InputError class="mt-2" :message="form.errors.date_employed" />
                            </div>

                            <div>
                                <InputLabel for="employee_status" value="Employee Status" />
                                <select
                                    id="employee_status"
                                    class="mt-1 block w-full rounded-2xl border border-border bg-muted px-3 py-2 text-sm text-foreground shadow-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-sidebar-border dark:bg-zinc-950 dark:text-foreground"
                                    v-model="form.employee_status"
                                    required
                                >
                                    <option>Trainee</option>
                                    <option>Full Time</option>
                                    <option>Part Time</option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.employee_status" />
                            </div>
                        </div>
                    </section>

                    <section class="rounded-3xl border border-border bg-muted p-6 shadow-sm dark:border-sidebar-border dark:bg-zinc-950">
                        <h2 class="text-lg font-semibold text-white">Account security</h2>
                        <p class="mt-1 text-sm text-muted-foreground">Set your email and password to complete registration.</p>

                        <div class="mt-6 space-y-4">
                            <div>
                                <InputLabel for="email" value="Email Address" />
                                <TextInput
                                    id="email"
                                    type="email"
                                    class="mt-1"
                                    v-model="form.email"
                                    required
                                    autocomplete="username"
                                />
                                <InputError class="mt-2" :message="form.errors.email" />
                            </div>

                            <div>
                                <InputLabel for="password" value="Password" />
                                <TextInput
                                    id="password"
                                    type="password"
                                    class="mt-1"
                                    v-model="form.password"
                                    required
                                    autocomplete="new-password"
                                />
                                <InputError class="mt-2" :message="form.errors.password" />
                            </div>

                            <div>
                                <InputLabel for="password_confirmation" value="Confirm Password" />
                                <TextInput
                                    id="password_confirmation"
                                    type="password"
                                    class="mt-1"
                                    v-model="form.password_confirmation"
                                    required
                                    autocomplete="new-password"
                                />
                                <InputError class="mt-2" :message="form.errors.password_confirmation" />
                            </div>
                        </div>

                        <div class="mt-6 flex flex-col items-start gap-4 sm:flex-row sm:justify-between">
                            <Link
                                :href="route('login')"
                                class="rounded-md text-sm text-muted-foreground underline hover:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:text-muted-foreground dark:hover:text-white dark:focus:ring-offset-zinc-900"
                            >
                                Already registered?
                            </Link>

                            <PrimaryButton
                                class="ms-0 sm:ms-4"
                                :class="{ 'opacity-25': form.processing }"
                                :disabled="form.processing"
                            >
                                Register
                            </PrimaryButton>
                        </div>
                    </section>
                </div>
            </form>
        </section>
    </GuestLayout>
</template>
