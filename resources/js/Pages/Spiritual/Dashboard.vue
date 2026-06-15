<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
// import { route } from '@/routes';

import axios from 'axios';

// Ziggy route helper (generated globally in the app); used to resolve web named routes
// eslint-disable-next-line @typescript-eslint/no-explicit-any

import {
  Users,
  BookOpen,
  Calendar,
  Bell,
  CheckCircle2,
  XCircle,
  AlertTriangle,
  RefreshCcw,
  ChevronDown,
  Filter,
} from '@lucide/vue';

// ziggy route helper may not include new pages; this component uses API only.



type DeptOption = { name: string; count: number };

const activeTab = ref<'devotionals' | 'wednesday' | 'sunday' | 'ministry'>('devotionals');

const devotionalDate = ref<string>(new Date().toISOString().slice(0, 10));
const prayerDate = ref<string>(new Date().toISOString().slice(0, 10));
const sundayDate = ref<string>(new Date().toISOString().slice(0, 10));

const selectedDepartment = ref<string>('');

const devotionalRecords = ref<any[]>([]);
const wednesdayRecords = ref<any[]>([]);
const sundayRecords = ref<any[]>([]);
const ministryReports = ref<any[]>([]);
const ministryStats = ref<any[]>([]);

const loading = ref(false);

const todaySummary = ref({
  devotional: { submitted: 0, total: 0, percent: 0 },
  wednesday_prayer: { attended: 0, total: 0 },
  sunday_service: { attended: 0, total: 0, date: '' },
});

const topMinistry = ref<{ ministry_type: string; count: number } | null>(null);

const percentageText = computed(() => {
  const pct = todaySummary.value?.devotional?.percent ?? 0;
  return `${pct}%`;
});

const hasDepartment = computed(() => selectedDepartment.value.trim().length > 0);

const fetchDashboard = async () => {
  loading.value = true;
  try {
    // Web routes (Inertia session/auth cookies) instead of API routes
    const res = await axios.get('/spiritual/dashboard');
    // API already returns {summary, top_ministry} shape in res.data
    todaySummary.value = res.data.summary ?? res.data;
    topMinistry.value = res.data.top_ministry ?? null;
  } finally {
    loading.value = false;
  }
};

const fetchDevotionals = async () => {
  loading.value = true;
  try {
    const res = await axios.get('/spiritual/devotional/records', {
      params: {
        date: devotionalDate.value,
        department: selectedDepartment.value || undefined,
      },
    });
    devotionalRecords.value = res.data;
  } finally {
    loading.value = false;
  }
};

const fetchWednesday = async () => {
  loading.value = true;
  try {
    const res = await axios.get('/spiritual/wednesday/records', {
      params: { date: prayerDate.value },
    });
    wednesdayRecords.value = res.data;
  } finally {
    loading.value = false;
  }
};

const fetchSunday = async () => {
  loading.value = true;
  try {
    const res = await axios.get('/spiritual/sunday/records', {
      params: { date: sundayDate.value },
    });
    sundayRecords.value = res.data;
  } finally {
    loading.value = false;
  }
};

const fetchMinistry = async () => {
  loading.value = true;
  try {
    const stats = await axios.get('/spiritual/ministry/stats');
    ministryStats.value = stats.data ?? stats.data ?? stats;

    const reports = await axios.get('/spiritual/ministry/reports');
    ministryReports.value = reports.data ?? reports.data ?? reports;
  } finally {
    loading.value = false;
  }
};

const statusLabel = (st: string) => {
  if (st === 'on_time') return 'On Time';
  if (st === 'late') return 'Late';
  if (st === 'none') return 'None';
  return st;
};

const submitDevotional = async (userId: string, payload: any) => {
  await axios.post(`/api/spiritual/devotional/${userId}`, {
    date: devotionalDate.value,
    status: payload.status,
    notes: payload.notes ?? null,
  });
  await fetchDevotionals();
};

const submitPrayer = async (userId: string, payload: any) => {
  await axios.post(`/api/spiritual/wednesday/${userId}`, {
    wednesday_date: prayerDate.value,
    attended: payload.attended,
    status: payload.status,
    absence_reason: payload.absence_reason ?? null,
  });
  await fetchWednesday();
};

const submitSunday = async (userId: string, payload: any) => {
  await axios.post(`/api/spiritual/sunday/${userId}`, {
    sunday_date: sundayDate.value,
    attended: payload.attended,
    status: payload.status,
    absence_reason: payload.absence_reason ?? null,
  });
  await fetchSunday();
};

const sendReminder = async () => {
  if (activeTab.value !== 'devotionals') return;
  if (!devotionalRecords.value.length) return;

  // Bulk reminder endpoint exists; dashboard UI sends bulk reminders.
  await axios.post('/api/spiritual/devotional/remind-all');
  await fetchDevotionals();
  await fetchDashboard();
};

watch(activeTab, async (t) => {
  if (t === 'devotionals') await fetchDevotionals();
  if (t === 'wednesday') await fetchWednesday();
  if (t === 'sunday') await fetchSunday();
  if (t === 'ministry') await fetchMinistry();
});

// Initial loads
fetchDashboard();
fetchDevotionals();
fetchWednesday();
fetchSunday();
fetchMinistry();
</script>

<template>
  <Head title="Spiritual Formation" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-foreground">Spiritual Formation</h2>
    </template>

    <div class="py-12">
      <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">
        <!-- Summary cards -->
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
          <div class="rounded-[28px] border border-border bg-card p-6 shadow-sm dark:border-zinc-800/80 dark:bg-zinc-900/50">
            <div class="flex items-center justify-between">
              <span class="text-sm font-semibold text-muted-foreground">Today's Devotional</span>
              <div class="rounded-xl bg-neutral-100 p-2 text-neutral-900 dark:bg-zinc-800 dark:text-neutral-100">
                <BookOpen class="size-4" />
              </div>
            </div>
            <div class="mt-4">
              <p class="text-3xl font-extrabold text-foreground">{{ todaySummary.devotional.submitted }}/{{ todaySummary.devotional.total }}</p>
              <p class="mt-1 text-xs text-muted-foreground">{{ percentageText }}</p>
            </div>
          </div>

          <div class="rounded-[28px] border border-border bg-card p-6 shadow-sm dark:border-zinc-800/80 dark:bg-zinc-900/50">
            <div class="flex items-center justify-between">
              <span class="text-sm font-semibold text-muted-foreground">Wednesday Prayer</span>
              <div class="rounded-xl bg-neutral-100 p-2 text-neutral-900 dark:bg-zinc-800 dark:text-neutral-100">
                <Calendar class="size-4" />
              </div>
            </div>
            <div class="mt-4">
              <p class="text-3xl font-extrabold text-foreground">{{ todaySummary.wednesday_prayer.attended }}/{{ todaySummary.wednesday_prayer.total }}</p>
              <p class="mt-1 text-xs text-muted-foreground">Attended this week</p>
            </div>
          </div>

          <div class="rounded-[28px] border border-border bg-card p-6 shadow-sm dark:border-zinc-800/80 dark:bg-zinc-900/50">
            <div class="flex items-center justify-between">
              <span class="text-sm font-semibold text-muted-foreground">Sunday Service</span>
              <div class="rounded-xl bg-neutral-100 p-2 text-neutral-900 dark:bg-zinc-800 dark:text-neutral-100">
                <Users class="size-4" />
              </div>
            </div>
            <div class="mt-4">
              <p class="text-3xl font-extrabold text-foreground">{{ todaySummary.sunday_service.attended }}/{{ todaySummary.sunday_service.total }}</p>
              <p class="mt-1 text-xs text-muted-foreground">Last Sunday</p>
            </div>
          </div>
        </div>

        <!-- Top Ministry -->
        <div class="rounded-[28px] border border-border bg-card p-6 shadow-sm">
          <div class="flex items-center justify-between gap-4 flex-wrap">
            <div>
              <p class="text-sm font-semibold uppercase tracking-[0.3em] text-muted-foreground">Ministry Focus</p>
              <h3 class="mt-2 text-xl font-bold text-foreground">Top Ministry This Week</h3>
              <p class="mt-2 text-sm text-muted-foreground">
                {{ topMinistry ? topMinistry.ministry_type : 'No data yet' }}
              </p>
            </div>
            <button
              class="inline-flex items-center gap-2 rounded-2xl bg-primary px-5 py-2.5 text-sm font-semibold text-primary-foreground shadow-md transition-all duration-300 hover:bg-primary/90"
              @click="sendReminder"
            >
              <Bell class="size-4" />
              Send Devotional Reminders
            </button>
          </div>
        </div>

        <!-- Tabs -->
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
          <div class="flex gap-2 flex-wrap">
            <button
              :class="activeTab === 'devotionals' ? 'bg-primary text-primary-foreground' : 'bg-muted text-foreground'"
              class="rounded-xl px-4 py-2 text-sm font-semibold transition"
              @click="activeTab = 'devotionals'"
            >Devotionals</button>
            <button
              :class="activeTab === 'wednesday' ? 'bg-primary text-primary-foreground' : 'bg-muted text-foreground'"
              class="rounded-xl px-4 py-2 text-sm font-semibold transition"
              @click="activeTab = 'wednesday'"
            >Wednesday Prayer</button>
            <button
              :class="activeTab === 'sunday' ? 'bg-primary text-primary-foreground' : 'bg-muted text-foreground'"
              class="rounded-xl px-4 py-2 text-sm font-semibold transition"
              @click="activeTab = 'sunday'"
            >Sunday Service</button>
            <button
              :class="activeTab === 'ministry' ? 'bg-primary text-primary-foreground' : 'bg-muted text-foreground'"
              class="rounded-xl px-4 py-2 text-sm font-semibold transition"
              @click="activeTab = 'ministry'"
            >Ministry Reports</button>
          </div>

          <div v-if="activeTab === 'devotionals'" class="flex gap-2 items-center">
            <input type="date" v-model="devotionalDate" class="rounded-xl border px-3 py-2 text-sm bg-card" />
            <input type="text" v-model="selectedDepartment" placeholder="Department (optional)" class="rounded-xl border px-3 py-2 text-sm bg-card" />
            <button class="rounded-xl border px-3 py-2 text-sm font-semibold" @click="fetchDevotionals">Refresh</button>
          </div>

          <div v-else-if="activeTab === 'wednesday'" class="flex gap-2 items-center">
            <input type="date" v-model="prayerDate" class="rounded-xl border px-3 py-2 text-sm bg-card" />
            <button class="rounded-xl border px-3 py-2 text-sm font-semibold" @click="fetchWednesday">Refresh</button>
          </div>

          <div v-else-if="activeTab === 'sunday'" class="flex gap-2 items-center">
            <input type="date" v-model="sundayDate" class="rounded-xl border px-3 py-2 text-sm bg-card" />
            <button class="rounded-xl border px-3 py-2 text-sm font-semibold" @click="fetchSunday">Refresh</button>
          </div>
        </div>

        <!-- Devotionals tab -->
        <div v-if="activeTab === 'devotionals'" class="rounded-[28px] border border-border bg-card p-6 shadow-sm">
          <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
              <thead>
                <tr class="text-left">
                  <th class="py-2">Employee</th>
                  <th class="py-2">Department</th>
                  <th class="py-2">Cell Group</th>
                  <th class="py-2">Status</th>
                  <th class="py-2">Submitted At</th>
                  <th class="py-2">Action</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="r in devotionalRecords" :key="r.user_id" class="border-t border-border">
                  <td class="py-3 font-semibold">{{ r.user?.name ?? r.user_id }}</td>
                  <td class="py-3">{{ r.user?.department ?? '' }}</td>
                  <td class="py-3">{{ r.user?.cell_group_name ?? '' }}</td>
                  <td class="py-3">
                    <span class="inline-flex rounded-full bg-muted px-3 py-1 text-xs font-semibold">
                      {{ statusLabel(r.status) }}
                    </span>
                  </td>
                  <td class="py-3 text-muted-foreground">{{ r.monitored_at ?? '-' }}</td>
                  <td class="py-3">
                    <select
                      class="rounded-xl border px-3 py-2 text-sm bg-card"
                      v-model="r._newStatus"
                    >
                      <option value="on_time">On Time</option>
                      <option value="late">Late</option>
                      <option value="none">None</option>
                    </select>
                    <input
                      class="ml-2 rounded-xl border px-3 py-2 text-sm bg-card"
                      v-model="r._newNotes"
                      placeholder="Notes (optional)"
                    />
                    <button
                      class="ml-2 rounded-xl bg-primary px-3 py-2 text-sm font-semibold text-primary-foreground"
                      @click="submitDevotional(r.user_id, { status: r._newStatus ?? r.status, notes: r._newNotes })"
                    >Update</button>
                  </td>
                </tr>
                <tr v-if="!devotionalRecords.length">
                  <td colspan="6" class="py-10 text-center text-muted-foreground">No devotional records found.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Wednesday tab -->
        <div v-if="activeTab === 'wednesday'" class="rounded-[28px] border border-border bg-card p-6 shadow-sm">
          <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
              <thead>
                <tr class="text-left">
                  <th class="py-2">Employee</th>
                  <th class="py-2">Attended</th>
                  <th class="py-2">Status</th>
                  <th class="py-2">Absence Reason</th>
                  <th class="py-2">Action</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="r in wednesdayRecords" :key="r.user_id" class="border-t border-border">
                  <td class="py-3 font-semibold">{{ r.user?.name ?? r.user_id }}</td>
                  <td class="py-3">
                    <input type="checkbox" v-model="r._newAttended" />
                  </td>
                  <td class="py-3">
                    <select class="rounded-xl border px-3 py-2 text-sm bg-card" v-model="r._newStatus">
                      <option value="attended">attended</option>
                      <option value="absent">absent</option>
                      <option value="excused">excused</option>
                    </select>
                  </td>
                  <td class="py-3">
                    <input class="rounded-xl border px-3 py-2 text-sm bg-card w-full" v-model="r._newReason" placeholder="Reason" />
                  </td>
                  <td class="py-3">
                    <button
                      class="rounded-xl bg-primary px-3 py-2 text-sm font-semibold text-primary-foreground"
                      @click="submitPrayer(r.user_id, { attended: r._newAttended ?? r.attended, status: r._newStatus ?? r.status, absence_reason: r._newReason })"
                    >Update</button>
                  </td>
                </tr>
                <tr v-if="!wednesdayRecords.length">
                  <td colspan="5" class="py-10 text-center text-muted-foreground">No Wednesday records found.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Sunday tab -->
        <div v-if="activeTab === 'sunday'" class="rounded-[28px] border border-border bg-card p-6 shadow-sm">
          <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
              <thead>
                <tr class="text-left">
                  <th class="py-2">Employee</th>
                  <th class="py-2">Attended</th>
                  <th class="py-2">Status</th>
                  <th class="py-2">Absence Reason</th>
                  <th class="py-2">Action</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="r in sundayRecords" :key="r.user_id" class="border-t border-border">
                  <td class="py-3 font-semibold">{{ r.user?.name ?? r.user_id }}</td>
                  <td class="py-3">
                    <input type="checkbox" v-model="r._newAttended" />
                  </td>
                  <td class="py-3">
                    <select class="rounded-xl border px-3 py-2 text-sm bg-card" v-model="r._newStatus">
                      <option value="attended">attended</option>
                      <option value="absent">absent</option>
                      <option value="excused">excused</option>
                    </select>
                  </td>
                  <td class="py-3">
                    <input class="rounded-xl border px-3 py-2 text-sm bg-card w-full" v-model="r._newReason" placeholder="Reason" />
                  </td>
                  <td class="py-3">
                    <button
                      class="rounded-xl bg-primary px-3 py-2 text-sm font-semibold text-primary-foreground"
                      @click="submitSunday(r.user_id, { attended: r._newAttended ?? r.attended, status: r._newStatus ?? r.status, absence_reason: r._newReason })"
                    >Update</button>
                  </td>
                </tr>
                <tr v-if="!sundayRecords.length">
                  <td colspan="5" class="py-10 text-center text-muted-foreground">No Sunday records found.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Ministry tab -->
        <div v-if="activeTab === 'ministry'" class="rounded-[28px] border border-border bg-card p-6 shadow-sm">
          <div class="grid gap-6 lg:grid-cols-2">
            <div>
              <h4 class="font-semibold text-foreground">Summary</h4>
              <div class="mt-3 space-y-2">
                <div
                  v-for="row in ministryStats"
                  :key="row.ministry_type"
                  class="flex items-center justify-between rounded-xl border border-border bg-muted/30 px-4 py-3"
                >
                  <span class="text-sm font-semibold text-foreground">{{ row.ministry_type }}</span>
                  <span class="text-sm font-bold">{{ row.cnt }}</span>
                </div>
                <div v-if="!ministryStats.length" class="text-muted-foreground text-sm">No ministry stats.</div>
              </div>
            </div>

            <div>
              <h4 class="font-semibold text-foreground">Recent Reports</h4>
              <div class="mt-3 overflow-x-auto">
                <table class="min-w-full text-sm">
                  <thead>
                    <tr class="text-left">
                      <th class="py-2">Date</th>
                      <th class="py-2">Employee</th>
                      <th class="py-2">Ministry</th>
                      <th class="py-2">Custom</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="rep in ministryReports" :key="rep.id" class="border-t border-border">
                      <td class="py-3">{{ rep.eod_date }}</td>
                      <td class="py-3 font-semibold">{{ rep.user?.name ?? '' }}</td>
                      <td class="py-3">{{ rep.ministry_type }}</td>
                      <td class="py-3 text-muted-foreground">{{ rep.custom_description ?? '-' }}</td>
                    </tr>
                    <tr v-if="!ministryReports.length">
                      <td colspan="4" class="py-10 text-center text-muted-foreground">No ministry reports.</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

