<script setup lang="ts">
import { computed, reactive, ref, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import axios from 'axios';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';

type Row = {
  id: string;
  employee_name: string;
  department: string;
  position: string;
  date: string;
  accomplishments: string;
  tomorrow_plan: string;
  blockers: string;
  ministries: string;
  status: string;
  submitted_at: string;
  hours_logged: number | string;
  mood_rating: number | string;
};

const filters = reactive({
  date_from: '',
  date_to: '',
  department: '',
  employee_id: '',
  status: '',
});

const departments = ref<string[]>([]);
const employees = ref<Array<{ id: string; name: string; department: string }>>([]);

const rows = ref<Row[]>([]);
const meta = reactive({ total: 0, per_page: 50, page: 1 });

const loading = ref(false);
const error = ref<string | null>(null);

const canExport = computed(() => !loading.value);

const fetchData = async () => {
  loading.value = true;
  error.value = null;

  try {
    const res = await axios.get('/eod/employee/data', {
      params: {
        date_from: filters.date_from || undefined,
        date_to: filters.date_to || undefined,
        department: filters.department || undefined,
        employee_id: filters.employee_id || undefined,
        status: filters.status || undefined,
        per_page: meta.per_page,
        page: meta.page,
      },
    });

    rows.value = res.data.data;
    meta.total = res.data.total;
    meta.page = res.data.current_page;
    meta.per_page = res.data.per_page;
  } catch (e: any) {
    error.value = e?.response?.data?.message ?? 'Failed to load EOD data';
  } finally {
    loading.value = false;
  }
};

const exportExcel = async () => {
  if (!canExport.value) return;

  // create a form POST/GET to download
  const params = new URLSearchParams();
  if (filters.date_from) params.append('date_from', filters.date_from);
  if (filters.date_to) params.append('date_to', filters.date_to);
  if (filters.department) params.append('department', filters.department);
  if (filters.employee_id) params.append('employee_id', filters.employee_id);
  if (filters.status) params.append('status', filters.status);

  window.location.href = `/eod/employee/export?${params.toString()}`;
};

watch(
  () => [filters.date_from, filters.date_to, filters.department, filters.employee_id, filters.status],
  () => {
    // debounce-ish: simple direct fetch for now
    fetchData();
  }
);

// initial load: filters empty -> server applies defaults
fetchData();

// Note: Inertia server-side props are not wired for this component yet.
// The controller can pass departments/employees later, but the page still works without them.
</script>

<template>
  <Head title="Employee EOD" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-foreground">Employee EOD View & Excel Export</h2>
    </template>

    <div class="py-10">
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6">
        <div class="rounded-[28px] border border-border bg-card p-5 shadow-sm">
          <div class="flex flex-col md:flex-row md:items-end gap-4 justify-between">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 w-full">
              <div>
                <label class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">Date From</label>
                <input type="date" v-model="filters.date_from" class="mt-1 w-full rounded-2xl border border-border bg-muted px-4 py-2 text-sm" />
              </div>

              <div>
                <label class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">Date To</label>
                <input type="date" v-model="filters.date_to" class="mt-1 w-full rounded-2xl border border-border bg-muted px-4 py-2 text-sm" />
              </div>

              <div>
                <label class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">Department</label>
                <input type="text" v-model="filters.department" placeholder="e.g. Engineering" class="mt-1 w-full rounded-2xl border border-border bg-muted px-4 py-2 text-sm" />
              </div>

              <div>
                <label class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">Employee ID</label>
                <input type="text" v-model="filters.employee_id" placeholder="UUID or leave blank" class="mt-1 w-full rounded-2xl border border-border bg-muted px-4 py-2 text-sm" />
              </div>

              <div class="sm:col-span-2">
                <label class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">Status</label>
                <select v-model="filters.status" class="mt-1 w-full rounded-2xl border border-border bg-muted px-4 py-2 text-sm">
                  <option value="">All</option>
                  <option value="draft">draft</option>
                  <option value="submitted">submitted</option>
                  <option value="reviewed">reviewed</option>
                  <option value="late">late</option>
                </select>
              </div>
            </div>

            <div class="flex items-center gap-3">
              <button
                class="rounded-2xl border border-border bg-card px-4 py-2 text-sm font-semibold text-foreground"
                @click="fetchData"
                :disabled="loading"
              >
                Refresh
              </button>

              <button
                class="rounded-2xl bg-primary px-4 py-2 text-sm font-semibold text-primary-foreground"
                @click="exportExcel"
                :disabled="loading || !canExport"
              >
                Export Excel
              </button>
            </div>
          </div>

          <div v-if="error" class="mt-3 text-sm text-red-600">{{ error }}</div>
          <div class="mt-3 text-sm text-muted-foreground">Total: {{ meta.total }}</div>
        </div>

        <div class="overflow-x-auto rounded-[28px] border border-border bg-card p-5 shadow-sm">
          <table class="min-w-full text-sm">
            <thead>
              <tr class="text-left text-muted-foreground">
                <th class="py-2 pr-3">Employee</th>
                <th class="py-2 pr-3">Department</th>
                <th class="py-2 pr-3">Position</th>
                <th class="py-2 pr-3">Date</th>
                <th class="py-2 pr-3">Status</th>
                <th class="py-2 pr-3">Submitted At</th>
                <th class="py-2 pr-3">Hours</th>
                <th class="py-2 pr-3">Mood</th>
              </tr>
            </thead>

            <tbody>
              <tr v-for="r in rows" :key="r.id" class="border-t border-border">
                <td class="py-3 font-semibold">{{ r.employee_name }}</td>
                <td class="py-3">{{ r.department }}</td>
                <td class="py-3">{{ r.position }}</td>
                <td class="py-3">{{ r.date }}</td>
                <td class="py-3">{{ r.status }}</td>
                <td class="py-3">{{ r.submitted_at }}</td>
                <td class="py-3">{{ r.hours_logged }}</td>
                <td class="py-3">{{ r.mood_rating }}</td>
              </tr>
              <tr v-if="!loading && rows.length === 0">
                <td colspan="8" class="py-10 text-center text-muted-foreground">No results for selected filters.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

