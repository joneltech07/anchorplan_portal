<script setup lang="ts">
import { ref, computed, onMounted, nextTick } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import axios from 'axios';
import { 
    Plus, 
    Edit, 
    Trash2, 
    Calendar, 
    Clock, 
    User, 
    X, 
    Filter, 
    Check, 
    Briefcase,
    DollarSign,
    Info,
    CalendarDays,
    Coffee
} from '@lucide/vue';
import { toast } from 'vue-sonner';

interface UserItem {
    id: string;
    name: string;
    email: string;
    role: string;
    department: string;
}

interface ShiftType {
    id: string;
    name: string;
    code: string;
    start_time: string;
    end_time: string;
    break_hours: number;
    hourly_rate: number;
    night_differential_rate: number | null;
    description: string | null;
    color: string;
    is_active: boolean;
}

interface WeeklySchedule {
    id: string;
    name: string;
    description: string | null;
    monday: { shift_type_id: string | null; break_hours: number } | null;
    tuesday: { shift_type_id: string | null; break_hours: number } | null;
    wednesday: { shift_type_id: string | null; break_hours: number } | null;
    thursday: { shift_type_id: string | null; break_hours: number } | null;
    friday: { shift_type_id: string | null; break_hours: number } | null;
    saturday: { shift_type_id: string | null; break_hours: number } | null;
    sunday: { shift_type_id: string | null; break_hours: number } | null;
    is_active: boolean;
}

interface RegularAssignment {
    id: string;
    user_id: string;
    user: { id: string; name: string; email: string };
    shift_type_id: string;
    shift_type: ShiftType;
    start_date: string;
    end_date: string | null;
    status: string;
    notes: string | null;
    assigner: { id: string; name: string };
}

interface ScheduleException {
    id: string;
    user_id: string;
    user: { id: string; name: string; email: string };
    exception_date: string;
    shift_type_id: string | null;
    shift_type: ShiftType | null;
    type: 'custom_shift' | 'day_off' | 'holiday' | 'half_day';
    break_hours: number | null;
    reason: string | null;
}

const props = defineProps<{
    shiftTypes: ShiftType[];
    weeklySchedules: WeeklySchedule[];
    users: UserItem[];
    regularAssignments: RegularAssignment[];
    exceptions: ScheduleException[];
    isAdminOrHr: boolean;
}>();

// Navigation Tabs
const activeTab = ref('calendar');

// Interactive Filters for Calendar
const filterUserId = ref('');
const filterDepartment = ref('');

const filteredUsers = computed(() => {
    if (!filterDepartment.value) return props.users;
    return props.users.filter(u => u.department === filterDepartment.value);
});

// Departments list
const departments = computed(() => {
    const deps = new Set(props.users.map(u => u.department).filter(Boolean));
    return Array.from(deps);
});

// FullCalendar Setup
const calendarRef = ref<any>(null);
const selectedEvent = ref<any>(null);
const showEventDetailModal = ref(false);

const calendarEvents = (info: any, successCallback: any, failureCallback: any) => {
    axios.get(route('shifts.events'), {
        params: {
            start: info.startStr,
            end: info.endStr,
            user_id: filterUserId.value || undefined,
        }
    })
    .then(response => {
        successCallback(response.data);
    })
    .catch(error => {
        console.error(error);
        toast.error('Failed to load shifts for the selected range.');
        failureCallback(error);
    });
};

const refetchEvents = () => {
    nextTick(() => {
        if (calendarRef.value) {
            const calendarApi = calendarRef.value.getApi();
            calendarApi.refetchEvents();
        }
    });
};

const calendarOptions = computed(() => ({
    plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
    initialView: 'dayGridMonth',
    headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek'
    },
    events: calendarEvents,
    height: 'auto',
    selectable: props.isAdminOrHr,
    select: (info: any) => {
        if (props.isAdminOrHr) {
            openAssignModal(info.startStr);
        }
    },
    eventClick: (info: any) => {
        selectedEvent.value = info.event;
        showEventDetailModal.value = true;
    }
}));

// Modals State
const showShiftTypeModal = ref(false);
const editingShiftType = ref<ShiftType | null>(null);

const showAssignModal = ref(false);
const assignDate = ref('');

const showWeeklyTemplateModal = ref(false);
const editingWeeklyTemplate = ref<WeeklySchedule | null>(null);

// Forms
const shiftTypeForm = useForm({
    name: '',
    code: '',
    start_time: '09:00',
    end_time: '18:00',
    break_hours: 1.0,
    hourly_rate: 150.00,
    night_differential_rate: 1.0,
    description: '',
    color: '#3B82F6',
    is_active: true
});

const assignForm = useForm({
    assignment_type: 'regular', // regular, exception, weekly_template
    user_id: '',
    shift_type_id: '',
    start_date: '',
    end_date: '',
    notes: '',
    // exception specifics
    exception_date: '',
    type: 'custom_shift', // custom_shift, day_off, holiday, half_day
    break_hours: 1.0,
    reason: '',
    // weekly template specifics
    weekly_schedule_id: ''
});

const weeklyTemplateForm = useForm({
    id: '',
    name: '',
    description: '',
    monday: { shift_type_id: '', break_hours: 1.0 },
    tuesday: { shift_type_id: '', break_hours: 1.0 },
    wednesday: { shift_type_id: '', break_hours: 1.0 },
    thursday: { shift_type_id: '', break_hours: 1.0 },
    friday: { shift_type_id: '', break_hours: 1.0 },
    saturday: { shift_type_id: '', break_hours: 1.0 },
    sunday: { shift_type_id: '', break_hours: 1.0 },
    is_active: true
});

// Action Handlers
const openShiftTypeModal = (type: ShiftType | null = null) => {
    editingShiftType.value = type;
    if (type) {
        shiftTypeForm.name = type.name;
        shiftTypeForm.code = type.code;
        shiftTypeForm.start_time = type.start_time.substring(0, 5);
        shiftTypeForm.end_time = type.end_time.substring(0, 5);
        shiftTypeForm.break_hours = Number(type.break_hours);
        shiftTypeForm.hourly_rate = Number(type.hourly_rate);
        shiftTypeForm.night_differential_rate = type.night_differential_rate ? Number(type.night_differential_rate) : 1.0;
        shiftTypeForm.description = type.description || '';
        shiftTypeForm.color = type.color;
        shiftTypeForm.is_active = !!type.is_active;
    } else {
        shiftTypeForm.reset();
    }
    showShiftTypeModal.value = true;
};

const submitShiftType = () => {
    if (editingShiftType.value) {
        shiftTypeForm.put(route('shifts.types.update', editingShiftType.value.id), {
            onSuccess: () => {
                showShiftTypeModal.value = false;
                toast.success('Shift type updated successfully.');
                refetchEvents();
            },
            onError: (err) => {
                const msg = Object.values(err).flat().join(' ');
                toast.error(msg || 'Failed to update shift type.');
            }
        });
    } else {
        shiftTypeForm.post(route('shifts.types.store'), {
            onSuccess: () => {
                showShiftTypeModal.value = false;
                toast.success('Shift type created successfully.');
                refetchEvents();
            },
            onError: (err) => {
                const msg = Object.values(err).flat().join(' ');
                toast.error(msg || 'Failed to create shift type.');
            }
        });
    }
};

const deleteShiftType = (type: ShiftType) => {
    if (confirm(`Are you sure you want to delete the shift type: "${type.name}"?`)) {
        router.delete(route('shifts.types.destroy', type.id), {
            onSuccess: () => {
                toast.success('Shift type deleted.');
                refetchEvents();
            },
            onError: () => {
                toast.error('Could not delete shift type.');
            }
        });
    }
};

const openAssignModal = (dateStr: string = '') => {
    assignForm.reset();
    assignDate.value = dateStr;
    assignForm.start_date = dateStr;
    assignForm.exception_date = dateStr;
    showAssignModal.value = true;
};

const submitAssignment = () => {
    assignForm.post(route('shifts.assign'), {
        onSuccess: () => {
            showAssignModal.value = false;
            toast.success('Schedule updated successfully.');
            refetchEvents();
        },
        onError: (err) => {
            const msg = Object.values(err).flat().join(' ');
            toast.error(msg || 'Failed to apply schedule.');
        }
    });
};

const deleteAssignmentItem = (assignment: RegularAssignment) => {
    if (confirm(`Remove standard shift assignment for ${assignment.user.name}?`)) {
        router.delete(route('shifts.assign.destroy', assignment.id), {
            onSuccess: () => {
                toast.success('Assignment deleted.');
                refetchEvents();
            }
        });
    }
};

const deleteExceptionItem = (exception: ScheduleException) => {
    if (confirm(`Delete override exception for ${exception.user.name} on ${exception.exception_date}?`)) {
        router.delete(route('shifts.exceptions.destroy', exception.id), {
            onSuccess: () => {
                toast.success('Exception deleted.');
                refetchEvents();
            }
        });
    }
};

const openWeeklyTemplateModal = (template: WeeklySchedule | null = null) => {
    editingWeeklyTemplate.value = template;
    if (template) {
        weeklyTemplateForm.id = template.id;
        weeklyTemplateForm.name = template.name;
        weeklyTemplateForm.description = template.description || '';
        
        const days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as const;
        days.forEach(day => {
            const dayVal = template[day];
            weeklyTemplateForm[day] = {
                shift_type_id: dayVal?.shift_type_id || '',
                break_hours: dayVal?.break_hours ? Number(dayVal.break_hours) : 1.0
            };
        });
        
        weeklyTemplateForm.is_active = !!template.is_active;
    } else {
        weeklyTemplateForm.reset();
    }
    showWeeklyTemplateModal.value = true;
};

const submitWeeklyTemplate = () => {
    weeklyTemplateForm.post(route('shifts.weekly-schedules.store'), {
        onSuccess: () => {
            showWeeklyTemplateModal.value = false;
            toast.success('Weekly schedule template saved.');
        },
        onError: (err) => {
            const msg = Object.values(err).flat().join(' ');
            toast.error(msg || 'Failed to save template.');
        }
    });
};

const deleteWeeklyTemplate = (template: WeeklySchedule) => {
    if (confirm(`Are you sure you want to delete template: "${template.name}"?`)) {
        router.delete(route('shifts.weekly-schedules.destroy', template.id), {
            onSuccess: () => {
                toast.success('Template deleted.');
            }
        });
    }
};

const deleteCurrentCalendarEvent = () => {
    const ext = selectedEvent.value.extendedProps;
    if (ext.type === 'exception') {
        if (confirm(`Delete exception override?`)) {
            router.delete(route('shifts.exceptions.destroy', ext.exception_id), {
                onSuccess: () => {
                    showEventDetailModal.value = false;
                    toast.success('Exception override removed.');
                    refetchEvents();
                }
            });
        }
    } else if (ext.type === 'regular') {
        if (confirm(`Delete baseline shift assignment? This removes the entire date range assignment.`)) {
            router.delete(route('shifts.assign.destroy', ext.shift_assignment_id), {
                onSuccess: () => {
                    showEventDetailModal.value = false;
                    toast.success('Baseline assignment removed.');
                    refetchEvents();
                }
            });
        }
    }
};

onMounted(() => {
    refetchEvents();
});
</script>

<template>
    <Head title="Shifts & Scheduling" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-1">
                <p class="text-sm font-medium uppercase tracking-[0.3em] text-muted-foreground">Workforce Management</p>
                <h2 class="text-2xl font-semibold leading-tight text-foreground">Shifts & Scheduling</h2>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                <!-- Navigation Tabs (Admin/HR gets multiple, Employees only get Calendar view) -->
                <div class="flex border-b border-border dark:border-zinc-800">
                    <button 
                        @click="activeTab = 'calendar'" 
                        :class="['px-6 py-3 text-sm font-medium border-b-2 -mb-px transition-colors duration-250', 
                                activeTab === 'calendar' ? 'border-primary text-foreground' : 'border-transparent text-muted-foreground hover:text-foreground']"
                    >
                        Shift Board
                    </button>
                    
                    <template v-if="props.isAdminOrHr">
                        <button 
                            @click="activeTab = 'types'" 
                            :class="['px-6 py-3 text-sm font-medium border-b-2 -mb-px transition-colors duration-250', 
                                    activeTab === 'types' ? 'border-primary text-foreground' : 'border-transparent text-muted-foreground hover:text-foreground']"
                        >
                            Shift Types
                        </button>
                        
                        <button 
                            @click="activeTab = 'templates'" 
                            :class="['px-6 py-3 text-sm font-medium border-b-2 -mb-px transition-colors duration-250', 
                                    activeTab === 'templates' ? 'border-primary text-foreground' : 'border-transparent text-muted-foreground hover:text-foreground']"
                        >
                            Weekly Templates
                        </button>

                        <button 
                            @click="activeTab = 'assignments'" 
                            :class="['px-6 py-3 text-sm font-medium border-b-2 -mb-px transition-colors duration-250', 
                                    activeTab === 'assignments' ? 'border-primary text-foreground' : 'border-transparent text-muted-foreground hover:text-foreground']"
                        >
                            Manage Assignments
                        </button>
                    </template>
                </div>

                <!-- TAB 1: SHIFT BOARD (CALENDAR) -->
                <div v-show="activeTab === 'calendar'" class="space-y-6">
                    <div class="rounded-[28px] border border-border bg-card p-6 shadow-sm dark:border-sidebar-border dark:bg-zinc-900/40">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
                            <div>
                                <h3 class="text-lg font-semibold text-foreground">Shift Board</h3>
                                <p class="text-sm text-muted-foreground">View and manage daily scheduled shifts and overrides.</p>
                            </div>
                            
                            <!-- Calendar Filters (Admin/HR Only) -->
                            <div v-if="props.isAdminOrHr" class="flex flex-wrap items-center gap-3">
                                <div class="flex items-center gap-1 text-xs text-muted-foreground">
                                    <Filter class="w-3.5 h-3.5" />
                                    <span>Filter:</span>
                                </div>
                                <select 
                                    v-model="filterDepartment" 
                                    @change="filterUserId = ''; refetchEvents()" 
                                    class="rounded-xl border border-border bg-muted/50 px-3 py-1.5 text-xs text-foreground focus:outline-none focus:ring-1 focus:ring-primary dark:border-zinc-800 dark:bg-zinc-950"
                                >
                                    <option value="">All Departments</option>
                                    <option v-for="dep in departments" :key="dep" :value="dep">{{ dep }}</option>
                                </select>
                                
                                <select 
                                    v-model="filterUserId" 
                                    @change="refetchEvents" 
                                    class="rounded-xl border border-border bg-muted/50 px-3 py-1.5 text-xs text-foreground focus:outline-none focus:ring-1 focus:ring-primary dark:border-zinc-800 dark:bg-zinc-950"
                                >
                                    <option value="">All Employees</option>
                                    <option v-for="user in filteredUsers" :key="user.id" :value="user.id">{{ user.name }}</option>
                                </select>

                                <button 
                                    @click="openAssignModal('')" 
                                    class="inline-flex items-center gap-1.5 rounded-xl bg-primary px-3.5 py-1.5 text-xs font-semibold text-primary-foreground hover:bg-primary/95 transition-colors"
                                >
                                    <Plus class="w-3.5 h-3.5" />
                                    <span>Schedule Shift</span>
                                </button>
                            </div>
                        </div>

                        <!-- Calendar Grid -->
                        <div class="p-4 rounded-2xl border border-border bg-muted/30 dark:border-zinc-800 dark:bg-zinc-950/20">
                            <FullCalendar ref="calendarRef" :options="calendarOptions" />
                        </div>
                    </div>
                </div>

                <!-- TAB 2: SHIFT TYPES -->
                <div v-show="activeTab === 'types'" class="space-y-6">
                    <div class="rounded-[28px] border border-border bg-card p-6 shadow-sm dark:border-sidebar-border dark:bg-zinc-900/40">
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h3 class="text-lg font-semibold text-foreground">Shift Templates</h3>
                                <p class="text-sm text-muted-foreground">Standard shifts defined for the organization.</p>
                            </div>
                            <button 
                                @click="openShiftTypeModal(null)" 
                                class="inline-flex items-center gap-1.5 rounded-xl bg-primary px-4 py-2 text-sm font-semibold text-primary-foreground hover:bg-primary/95 transition-colors"
                            >
                                <Plus class="w-4 h-4" />
                                <span>Add Shift Type</span>
                            </button>
                        </div>

                        <!-- Shift Type Card Grid -->
                        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                            <div 
                                v-for="type in props.shiftTypes" 
                                :key="type.id" 
                                class="relative rounded-2xl border border-border bg-card p-5 shadow-sm hover:shadow-md transition-shadow dark:border-zinc-800 dark:bg-zinc-950/40"
                            >
                                <div class="flex items-start justify-between">
                                    <div class="space-y-1">
                                        <div class="flex items-center gap-2">
                                            <span 
                                                class="w-3 h-3 rounded-full shrink-0" 
                                                :style="{ backgroundColor: type.color }"
                                            ></span>
                                            <h4 class="font-bold text-foreground">{{ type.name }}</h4>
                                        </div>
                                        <span class="inline-block bg-muted text-muted-foreground text-xs font-semibold px-2 py-0.5 rounded-md uppercase">
                                            {{ type.code }}
                                        </span>
                                    </div>
                                    <div class="flex gap-2">
                                        <button 
                                            @click="openShiftTypeModal(type)" 
                                            class="p-1.5 text-muted-foreground hover:text-foreground rounded-lg hover:bg-muted"
                                            title="Edit"
                                        >
                                            <Edit class="w-4 h-4" />
                                        </button>
                                        <button 
                                            @click="deleteShiftType(type)" 
                                            class="p-1.5 text-red-500 hover:text-red-600 rounded-lg hover:bg-red-500/10"
                                            title="Delete"
                                        >
                                            <Trash2 class="w-4 h-4" />
                                        </button>
                                    </div>
                                </div>

                                <div class="mt-4 pt-4 border-t border-border grid grid-cols-2 gap-3 text-xs dark:border-zinc-800">
                                    <div class="flex items-center gap-1.5 text-muted-foreground">
                                        <Clock class="w-3.5 h-3.5" />
                                        <span>{{ type.start_time.substring(0, 5) }} - {{ type.end_time.substring(0, 5) }}</span>
                                    </div>
                                    <div class="flex items-center gap-1.5 text-muted-foreground">
                                        <Coffee class="w-3.5 h-3.5" />
                                        <span>{{ type.break_hours }}h Break</span>
                                    </div>
                                    <div class="flex items-center gap-1.5 text-muted-foreground">
                                        <DollarSign class="w-3.5 h-3.5" />
                                        <span>₱{{ Number(type.hourly_rate).toFixed(2) }}/hr</span>
                                    </div>
                                    <div class="flex items-center gap-1.5 text-muted-foreground" v-if="type.night_differential_rate">
                                        <Info class="w-3.5 h-3.5" />
                                        <span>ND: {{ type.night_differential_rate }}x</span>
                                    </div>
                                </div>
                                
                                <p class="text-xs text-muted-foreground mt-4 line-clamp-2" v-if="type.description">
                                    {{ type.description }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 3: WEEKLY TEMPLATES -->
                <div v-show="activeTab === 'templates'" class="space-y-6">
                    <div class="rounded-[28px] border border-border bg-card p-6 shadow-sm dark:border-sidebar-border dark:bg-zinc-900/40">
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h3 class="text-lg font-semibold text-foreground">Weekly Recurring Templates</h3>
                                <p class="text-sm text-muted-foreground">Define and construct recurring work week configurations.</p>
                            </div>
                            <button 
                                @click="openWeeklyTemplateModal(null)" 
                                class="inline-flex items-center gap-1.5 rounded-xl bg-primary px-4 py-2 text-sm font-semibold text-primary-foreground hover:bg-primary/95 transition-colors"
                            >
                                <Plus class="w-4 h-4" />
                                <span>Create Template</span>
                            </button>
                        </div>

                        <!-- Templates Table -->
                        <div class="overflow-x-auto rounded-2xl border border-border dark:border-zinc-800">
                            <table class="w-full text-left border-collapse text-sm">
                                <thead>
                                    <tr class="bg-muted/50 text-muted-foreground text-xs uppercase tracking-wider font-semibold border-b border-border dark:border-zinc-800">
                                        <th class="p-4">Name</th>
                                        <th class="p-4">Monday</th>
                                        <th class="p-4">Tuesday</th>
                                        <th class="p-4">Wednesday</th>
                                        <th class="p-4">Thursday</th>
                                        <th class="p-4">Friday</th>
                                        <th class="p-4">Saturday</th>
                                        <th class="p-4">Sunday</th>
                                        <th class="p-4 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr 
                                        v-for="tpl in props.weeklySchedules" 
                                        :key="tpl.id" 
                                        class="hover:bg-muted/20 border-b border-border dark:border-zinc-800 last:border-0"
                                    >
                                        <td class="p-4 font-medium text-foreground">
                                            <div>{{ tpl.name }}</div>
                                            <div class="text-xs text-muted-foreground font-normal" v-if="tpl.description">{{ tpl.description }}</div>
                                        </td>
                                        
                                        <td v-for="day in ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as const" :key="day" class="p-4 text-xs">
                                            <span v-if="tpl[day]?.shift_type_id" class="px-2 py-1 rounded bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300 font-medium">
                                                {{ props.shiftTypes.find(t => t.id === tpl[day]?.shift_type_id)?.code || 'Shift' }}
                                            </span>
                                            <span v-else class="text-muted-foreground italic">Off</span>
                                        </td>

                                        <td class="p-4 text-right">
                                            <div class="flex justify-end gap-2">
                                                <button 
                                                    @click="openWeeklyTemplateModal(tpl)" 
                                                    class="p-1 text-muted-foreground hover:text-foreground"
                                                    title="Edit"
                                                >
                                                    <Edit class="w-4 h-4" />
                                                </button>
                                                <button 
                                                    @click="deleteWeeklyTemplate(tpl)" 
                                                    class="p-1 text-red-500 hover:text-red-600"
                                                    title="Delete"
                                                >
                                                    <Trash2 class="w-4 h-4" />
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="props.weeklySchedules.length === 0">
                                        <td colspan="9" class="p-8 text-center text-muted-foreground">
                                            No templates configured yet. Click "Create Template" to get started.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- TAB 4: MANAGE ASSIGNMENTS -->
                <div v-show="activeTab === 'assignments'" class="space-y-6">
                    <!-- Regular Baseline Shifts -->
                    <div class="rounded-[28px] border border-border bg-card p-6 shadow-sm dark:border-sidebar-border dark:bg-zinc-900/40">
                        <h3 class="text-lg font-semibold text-foreground mb-4">Baseline Shift Assignments</h3>
                        <div class="overflow-x-auto rounded-2xl border border-border dark:border-zinc-800">
                            <table class="w-full text-left border-collapse text-sm">
                                <thead>
                                    <tr class="bg-muted/50 text-muted-foreground text-xs uppercase tracking-wider font-semibold border-b border-border dark:border-zinc-800">
                                        <th class="p-4">Employee</th>
                                        <th class="p-4">Assigned Shift</th>
                                        <th class="p-4">Start Date</th>
                                        <th class="p-4">End Date</th>
                                        <th class="p-4">Notes</th>
                                        <th class="p-4">Assigned By</th>
                                        <th class="p-4 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr 
                                        v-for="item in props.regularAssignments" 
                                        :key="item.id" 
                                        class="hover:bg-muted/20 border-b border-border dark:border-zinc-800 last:border-0"
                                    >
                                        <td class="p-4 font-medium text-foreground">{{ item.user.name }}</td>
                                        <td class="p-4">
                                            <div class="flex items-center gap-1.5">
                                                <span class="w-2.5 h-2.5 rounded-full shrink-0" :style="{ backgroundColor: item.shift_type.color }"></span>
                                                <span>{{ item.shift_type.name }}</span>
                                            </div>
                                        </td>
                                        <td class="p-4 text-muted-foreground">{{ item.start_date }}</td>
                                        <td class="p-4 text-muted-foreground">
                                            {{ item.end_date ? item.end_date : 'Ongoing / Permanent' }}
                                        </td>
                                        <td class="p-4 max-w-xs truncate text-muted-foreground">{{ item.notes || '—' }}</td>
                                        <td class="p-4 text-muted-foreground">{{ item.assigner?.name || 'Admin' }}</td>
                                        <td class="p-4 text-right">
                                            <button 
                                                @click="deleteAssignmentItem(item)" 
                                                class="text-red-500 hover:text-red-600 p-1"
                                                title="Delete Assignment"
                                            >
                                                <Trash2 class="w-4 h-4" />
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="props.regularAssignments.length === 0">
                                        <td colspan="7" class="p-8 text-center text-muted-foreground">
                                            No regular shifts assigned yet.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Exceptions Overrides -->
                    <div class="rounded-[28px] border border-border bg-card p-6 shadow-sm dark:border-sidebar-border dark:bg-zinc-900/40">
                        <h3 class="text-lg font-semibold text-foreground mb-4">Date-Specific Exception Overrides</h3>
                        <div class="overflow-x-auto rounded-2xl border border-border dark:border-zinc-800">
                            <table class="w-full text-left border-collapse text-sm">
                                <thead>
                                    <tr class="bg-muted/50 text-muted-foreground text-xs uppercase tracking-wider font-semibold border-b border-border dark:border-zinc-800">
                                        <th class="p-4">Employee</th>
                                        <th class="p-4">Exception Date</th>
                                        <th class="p-4">Override Type</th>
                                        <th class="p-4">Shift Type</th>
                                        <th class="p-4">Break Hours</th>
                                        <th class="p-4">Reason / Notes</th>
                                        <th class="p-4 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr 
                                        v-for="item in props.exceptions" 
                                        :key="item.id" 
                                        class="hover:bg-muted/20 border-b border-border dark:border-zinc-800 last:border-0"
                                    >
                                        <td class="p-4 font-medium text-foreground">{{ item.user.name }}</td>
                                        <td class="p-4 text-muted-foreground">{{ item.exception_date }}</td>
                                        <td class="p-4 font-semibold uppercase text-xs">
                                            <span 
                                                v-if="item.type === 'custom_shift'" 
                                                class="px-2 py-0.5 rounded bg-amber-100 text-amber-800 dark:bg-amber-900/20 dark:text-amber-400"
                                            >
                                                Custom Shift
                                            </span>
                                            <span 
                                                v-else-if="item.type === 'day_off'" 
                                                class="px-2 py-0.5 rounded bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400"
                                            >
                                                Day Off
                                            </span>
                                            <span 
                                                v-else-if="item.type === 'holiday'" 
                                                class="px-2 py-0.5 rounded bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-400"
                                            >
                                                Holiday
                                            </span>
                                            <span 
                                                v-else 
                                                class="px-2 py-0.5 rounded bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400"
                                            >
                                                Half Day
                                            </span>
                                        </td>
                                        <td class="p-4">
                                            {{ item.shift_type ? item.shift_type.name : '—' }}
                                        </td>
                                        <td class="p-4">{{ item.break_hours !== null ? item.break_hours + 'h' : '—' }}</td>
                                        <td class="p-4 text-muted-foreground">{{ item.reason || '—' }}</td>
                                        <td class="p-4 text-right">
                                            <button 
                                                @click="deleteExceptionItem(item)" 
                                                class="text-red-500 hover:text-red-600 p-1"
                                                title="Delete Override"
                                            >
                                                <Trash2 class="w-4 h-4" />
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="props.exceptions.length === 0">
                                        <td colspan="7" class="p-8 text-center text-muted-foreground">
                                            No exceptions or date overrides registered.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL: ADD/EDIT SHIFT TYPE -->
        <Transition name="fade">
            <div v-if="showShiftTypeModal" class="fixed inset-0 z-50 flex items-center justify-center bg-zinc-950/65 backdrop-blur-sm p-4">
                <div class="w-full max-w-xl rounded-3xl border border-border bg-card p-6 shadow-xl dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex items-center justify-between border-b border-border pb-4 mb-5 dark:border-zinc-800">
                        <h3 class="text-lg font-semibold text-foreground">
                            {{ editingShiftType ? 'Edit Shift Type' : 'Add Shift Type' }}
                        </h3>
                        <button @click="showShiftTypeModal = false" class="text-muted-foreground hover:text-foreground">
                            <X class="w-5 h-5" />
                        </button>
                    </div>

                    <form @submit.prevent="submitShiftType" class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <label class="text-sm font-medium text-foreground">Shift Name</label>
                                <input v-model="shiftTypeForm.name" placeholder="e.g. Night Shift US" required class="w-full rounded-2xl border border-border bg-muted/30 px-4 py-2.5 text-sm text-foreground focus:outline-none focus:ring-1 focus:ring-primary dark:border-zinc-800 dark:bg-zinc-950" />
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-sm font-medium text-foreground">Code</label>
                                <input v-model="shiftTypeForm.code" placeholder="e.g. NIGHT-US" required class="w-full rounded-2xl border border-border bg-muted/30 px-4 py-2.5 text-sm text-foreground uppercase focus:outline-none focus:ring-1 focus:ring-primary dark:border-zinc-800 dark:bg-zinc-950" />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <label class="text-sm font-medium text-foreground">Start Time</label>
                                <input v-model="shiftTypeForm.start_time" type="time" required class="w-full rounded-2xl border border-border bg-muted/30 px-4 py-2.5 text-sm text-foreground focus:outline-none focus:ring-1 focus:ring-primary dark:border-zinc-800 dark:bg-zinc-950" />
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-sm font-medium text-foreground">End Time</label>
                                <input v-model="shiftTypeForm.end_time" type="time" required class="w-full rounded-2xl border border-border bg-muted/30 px-4 py-2.5 text-sm text-foreground focus:outline-none focus:ring-1 focus:ring-primary dark:border-zinc-800 dark:bg-zinc-950" />
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-4">
                            <div class="space-y-1.5">
                                <label class="text-sm font-medium text-foreground">Break Hours</label>
                                <select v-model="shiftTypeForm.break_hours" class="w-full rounded-2xl border border-border bg-muted/30 px-4 py-2.5 text-sm text-foreground focus:outline-none focus:ring-1 focus:ring-primary dark:border-zinc-800 dark:bg-zinc-950">
                                    <option :value="0">None</option>
                                    <option :value="0.5">0.5 hr</option>
                                    <option :value="1.0">1.0 hr</option>
                                    <option :value="1.5">1.5 hr</option>
                                    <option :value="2.0">2.0 hr</option>
                                </select>
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-sm font-medium text-foreground">Hourly Rate (PHP)</label>
                                <input v-model="shiftTypeForm.hourly_rate" type="number" step="any" required class="w-full rounded-2xl border border-border bg-muted/30 px-4 py-2.5 text-sm text-foreground focus:outline-none focus:ring-1 focus:ring-primary dark:border-zinc-800 dark:bg-zinc-950" />
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-sm font-medium text-foreground">Night Diff. (e.g. 1.1)</label>
                                <input v-model="shiftTypeForm.night_differential_rate" type="number" step="0.01" class="w-full rounded-2xl border border-border bg-muted/30 px-4 py-2.5 text-sm text-foreground focus:outline-none focus:ring-1 focus:ring-primary dark:border-zinc-800 dark:bg-zinc-950" />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <label class="text-sm font-medium text-foreground">Color Hex</label>
                                <div class="flex items-center gap-2">
                                    <input v-model="shiftTypeForm.color" type="color" class="w-10 h-10 border-0 rounded bg-transparent" />
                                    <input v-model="shiftTypeForm.color" pattern="^#([A-Fa-f0-9]{6})$" required class="w-full rounded-2xl border border-border bg-muted/30 px-4 py-2.5 text-sm text-foreground focus:outline-none focus:ring-1 focus:ring-primary dark:border-zinc-800 dark:bg-zinc-950" />
                                </div>
                            </div>
                            <div class="flex items-center pt-6 space-x-3">
                                <input type="checkbox" id="is_active" v-model="shiftTypeForm.is_active" class="h-4.5 w-4.5 rounded border-border text-primary focus:ring-primary" />
                                <label for="is_active" class="text-sm font-medium text-foreground select-none">Active / Available</label>
                            </div>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-sm font-medium text-foreground">Description</label>
                            <textarea v-model="shiftTypeForm.description" rows="3" class="w-full rounded-2xl border border-border bg-muted/30 px-4 py-2.5 text-sm text-foreground focus:outline-none focus:ring-1 focus:ring-primary dark:border-zinc-800 dark:bg-zinc-950"></textarea>
                        </div>

                        <div class="flex justify-end gap-3 pt-4 border-t border-border dark:border-zinc-800">
                            <button type="button" @click="showShiftTypeModal = false" class="rounded-xl border border-border px-4 py-2 text-sm font-semibold hover:bg-muted dark:border-zinc-800">
                                Cancel
                            </button>
                            <button type="submit" :disabled="shiftTypeForm.processing" class="rounded-xl bg-primary px-4 py-2 text-sm font-semibold text-primary-foreground hover:bg-primary/95 transition-colors">
                                Save
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Transition>

        <!-- MODAL: SCHEDULE SHIFT / ASSIGNMENT / EXCEPTION -->
        <Transition name="fade">
            <div v-if="showAssignModal" class="fixed inset-0 z-50 flex items-center justify-center bg-zinc-950/65 backdrop-blur-sm p-4">
                <div class="w-full max-w-xl rounded-3xl border border-border bg-card p-6 shadow-xl dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex items-center justify-between border-b border-border pb-4 mb-5 dark:border-zinc-800">
                        <h3 class="text-lg font-semibold text-foreground">
                            Schedule Work Shift
                        </h3>
                        <button @click="showAssignModal = false" class="text-muted-foreground hover:text-foreground">
                            <X class="w-5 h-5" />
                        </button>
                    </div>

                    <form @submit.prevent="submitAssignment" class="space-y-4">
                        <div class="space-y-1.5">
                            <label class="text-sm font-medium text-foreground">Employee</label>
                            <select v-model="assignForm.user_id" required class="w-full rounded-2xl border border-border bg-muted/30 px-4 py-2.5 text-sm text-foreground focus:outline-none focus:ring-1 focus:ring-primary dark:border-zinc-800 dark:bg-zinc-950">
                                <option value="" disabled>Select Employee</option>
                                <option v-for="user in props.users" :key="user.id" :value="user.id">
                                    {{ user.name }} ({{ user.department }})
                                </option>
                            </select>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-sm font-medium text-foreground">Scheduling Method</label>
                            <div class="grid grid-cols-3 gap-2">
                                <button 
                                    type="button" 
                                    @click="assignForm.assignment_type = 'regular'"
                                    :class="['px-3 py-2 rounded-xl text-xs font-semibold border transition-all duration-200', 
                                            assignForm.assignment_type === 'regular' ? 'bg-primary text-primary-foreground border-primary' : 'bg-muted/35 text-foreground border-border hover:bg-muted dark:border-zinc-800']"
                                >
                                    Baseline (Range)
                                </button>
                                <button 
                                    type="button" 
                                    @click="assignForm.assignment_type = 'exception'"
                                    :class="['px-3 py-2 rounded-xl text-xs font-semibold border transition-all duration-200', 
                                            assignForm.assignment_type === 'exception' ? 'bg-primary text-primary-foreground border-primary' : 'bg-muted/35 text-foreground border-border hover:bg-muted dark:border-zinc-800']"
                                >
                                    Date Override
                                </button>
                                <button 
                                    type="button" 
                                    @click="assignForm.assignment_type = 'weekly_template'"
                                    :class="['px-3 py-2 rounded-xl text-xs font-semibold border transition-all duration-200', 
                                            assignForm.assignment_type === 'weekly_template' ? 'bg-primary text-primary-foreground border-primary' : 'bg-muted/35 text-foreground border-border hover:bg-muted dark:border-zinc-800']"
                                >
                                    Apply Template
                                </button>
                            </div>
                        </div>

                        <!-- SUB-FORM 1: BASELINE REGULAR SHIFT -->
                        <template v-if="assignForm.assignment_type === 'regular'">
                            <div class="space-y-1.5">
                                <label class="text-sm font-medium text-foreground">Select Baseline Shift</label>
                                <select v-model="assignForm.shift_type_id" required class="w-full rounded-2xl border border-border bg-muted/30 px-4 py-2.5 text-sm text-foreground focus:outline-none focus:ring-1 focus:ring-primary dark:border-zinc-800 dark:bg-zinc-950">
                                    <option value="" disabled>Select Shift</option>
                                    <option v-for="t in props.shiftTypes.filter(st => st.is_active)" :key="t.id" :value="t.id">
                                        {{ t.name }} ({{ t.code }}): {{ t.start_time.substring(0, 5) }} - {{ t.end_time.substring(0, 5) }}
                                    </option>
                                </select>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-1.5">
                                    <label class="text-sm font-medium text-foreground">Start Date</label>
                                    <input v-model="assignForm.start_date" type="date" required class="w-full rounded-2xl border border-border bg-muted/30 px-4 py-2.5 text-sm text-foreground focus:outline-none focus:ring-1 focus:ring-primary dark:border-zinc-800 dark:bg-zinc-950" />
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-sm font-medium text-foreground">End Date (Optional)</label>
                                    <input v-model="assignForm.end_date" type="date" class="w-full rounded-2xl border border-border bg-muted/30 px-4 py-2.5 text-sm text-foreground focus:outline-none focus:ring-1 focus:ring-primary dark:border-zinc-800 dark:bg-zinc-950" placeholder="Ongoing" />
                                </div>
                            </div>

                            <div class="space-y-1.5">
                                <label class="text-sm font-medium text-foreground">Scheduling Notes</label>
                                <textarea v-model="assignForm.notes" rows="2" class="w-full rounded-2xl border border-border bg-muted/30 px-4 py-2.5 text-sm text-foreground focus:outline-none focus:ring-1 focus:ring-primary dark:border-zinc-800 dark:bg-zinc-950" placeholder="e.g. Assigned to US Support queue"></textarea>
                            </div>
                        </template>

                        <!-- SUB-FORM 2: DATE OVERRIDE / EXCEPTION -->
                        <template v-if="assignForm.assignment_type === 'exception'">
                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-1.5">
                                    <label class="text-sm font-medium text-foreground">Exception Date</label>
                                    <input v-model="assignForm.exception_date" type="date" required class="w-full rounded-2xl border border-border bg-muted/30 px-4 py-2.5 text-sm text-foreground focus:outline-none focus:ring-1 focus:ring-primary dark:border-zinc-800 dark:bg-zinc-950" />
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-sm font-medium text-foreground">Override Type</label>
                                    <select v-model="assignForm.type" required class="w-full rounded-2xl border border-border bg-muted/30 px-4 py-2.5 text-sm text-foreground focus:outline-none focus:ring-1 focus:ring-primary dark:border-zinc-800 dark:bg-zinc-950">
                                        <option value="custom_shift">Custom Shift Override</option>
                                        <option value="day_off">Day Off</option>
                                        <option value="holiday">Holiday Override</option>
                                        <option value="half_day">Half Day</option>
                                    </select>
                                </div>
                            </div>

                            <template v-if="['custom_shift', 'half_day'].includes(assignForm.type)">
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="space-y-1.5">
                                        <label class="text-sm font-medium text-foreground">Select Shift Type</label>
                                        <select v-model="assignForm.shift_type_id" required class="w-full rounded-2xl border border-border bg-muted/30 px-4 py-2.5 text-sm text-foreground focus:outline-none focus:ring-1 focus:ring-primary dark:border-zinc-800 dark:bg-zinc-950">
                                            <option value="" disabled>Select Shift</option>
                                            <option v-for="t in props.shiftTypes.filter(st => st.is_active)" :key="t.id" :value="t.id">
                                                {{ t.name }} ({{ t.code }})
                                            </option>
                                        </select>
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="text-sm font-medium text-foreground">Override Break Hours</label>
                                        <select v-model="assignForm.break_hours" class="w-full rounded-2xl border border-border bg-muted/30 px-4 py-2.5 text-sm text-foreground focus:outline-none focus:ring-1 focus:ring-primary dark:border-zinc-800 dark:bg-zinc-950">
                                            <option :value="0">None</option>
                                            <option :value="0.5">0.5 hr</option>
                                            <option :value="1.0">1.0 hr</option>
                                            <option :value="1.5">1.5 hr</option>
                                            <option :value="2.0">2.0 hr</option>
                                        </select>
                                    </div>
                                </div>
                            </template>

                            <div class="space-y-1.5">
                                <label class="text-sm font-medium text-foreground">Override Reason</label>
                                <textarea v-model="assignForm.reason" rows="2" required class="w-full rounded-2xl border border-border bg-muted/30 px-4 py-2.5 text-sm text-foreground focus:outline-none focus:ring-1 focus:ring-primary dark:border-zinc-800 dark:bg-zinc-950" placeholder="e.g. Covering shift for a sick coworker / Holiday swap"></textarea>
                            </div>
                        </template>

                        <!-- SUB-FORM 3: APPLY WEEKLY recurring TEMPLATE -->
                        <template v-if="assignForm.assignment_type === 'weekly_template'">
                            <div class="space-y-1.5">
                                <label class="text-sm font-medium text-foreground">Weekly Template</label>
                                <select v-model="assignForm.weekly_schedule_id" required class="w-full rounded-2xl border border-border bg-muted/30 px-4 py-2.5 text-sm text-foreground focus:outline-none focus:ring-1 focus:ring-primary dark:border-zinc-800 dark:bg-zinc-950">
                                    <option value="" disabled>Select Template</option>
                                    <option v-for="tpl in props.weeklySchedules" :key="tpl.id" :value="tpl.id">
                                        {{ tpl.name }}
                                    </option>
                                </select>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-1.5">
                                    <label class="text-sm font-medium text-foreground">Start Date</label>
                                    <input v-model="assignForm.start_date" type="date" required class="w-full rounded-2xl border border-border bg-muted/30 px-4 py-2.5 text-sm text-foreground focus:outline-none focus:ring-1 focus:ring-primary dark:border-zinc-800 dark:bg-zinc-950" />
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-sm font-medium text-foreground">End Date</label>
                                    <input v-model="assignForm.end_date" type="date" required class="w-full rounded-2xl border border-border bg-muted/30 px-4 py-2.5 text-sm text-foreground focus:outline-none focus:ring-1 focus:ring-primary dark:border-zinc-800 dark:bg-zinc-950" />
                                </div>
                            </div>
                        </template>

                        <div class="flex justify-end gap-3 pt-4 border-t border-border dark:border-zinc-800">
                            <button type="button" @click="showAssignModal = false" class="rounded-xl border border-border px-4 py-2 text-sm font-semibold hover:bg-muted dark:border-zinc-800">
                                Cancel
                            </button>
                            <button type="submit" :disabled="assignForm.processing" class="rounded-xl bg-primary px-4 py-2 text-sm font-semibold text-primary-foreground hover:bg-primary/95 transition-colors">
                                Apply Schedule
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Transition>

        <!-- MODAL: CREATE/EDIT WEEKLY recurring TEMPLATE -->
        <Transition name="fade">
            <div v-if="showWeeklyTemplateModal" class="fixed inset-0 z-50 flex items-center justify-center bg-zinc-950/65 backdrop-blur-sm p-4 overflow-y-auto">
                <div class="w-full max-w-3xl rounded-3xl border border-border bg-card p-6 shadow-xl my-8 dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex items-center justify-between border-b border-border pb-4 mb-5 dark:border-zinc-800">
                        <h3 class="text-lg font-semibold text-foreground">
                            {{ editingWeeklyTemplate ? 'Edit Weekly Template' : 'Create Weekly Template' }}
                        </h3>
                        <button @click="showWeeklyTemplateModal = false" class="text-muted-foreground hover:text-foreground">
                            <X class="w-5 h-5" />
                        </button>
                    </div>

                    <form @submit.prevent="submitWeeklyTemplate" class="space-y-6">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <label class="text-sm font-medium text-foreground">Template Name</label>
                                <input v-model="weeklyTemplateForm.name" placeholder="e.g. Standard Support Mon-Fri" required class="w-full rounded-2xl border border-border bg-muted/30 px-4 py-2.5 text-sm text-foreground focus:outline-none focus:ring-1 focus:ring-primary dark:border-zinc-800 dark:bg-zinc-950" />
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-sm font-medium text-foreground">Description</label>
                                <input v-model="weeklyTemplateForm.description" placeholder="e.g. Morning shifts Mon-Fri, off weekends" class="w-full rounded-2xl border border-border bg-muted/30 px-4 py-2.5 text-sm text-foreground focus:outline-none focus:ring-1 focus:ring-primary dark:border-zinc-800 dark:bg-zinc-950" />
                            </div>
                        </div>

                        <!-- Days Mon to Sun Grid -->
                        <div class="space-y-3">
                            <h4 class="text-sm font-semibold text-foreground uppercase tracking-wider">Configure Weekly Layout</h4>
                            
                            <div class="grid gap-3">
                                <div 
                                    v-for="day in ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as const" 
                                    :key="day"
                                    class="grid grid-cols-12 gap-3 items-center bg-muted/20 p-3 rounded-2xl border border-border dark:border-zinc-800 dark:bg-zinc-950/20"
                                >
                                    <div class="col-span-3 font-semibold text-sm capitalize text-foreground">
                                        {{ day }}
                                    </div>
                                    
                                    <div class="col-span-5">
                                        <select 
                                            v-model="weeklyTemplateForm[day].shift_type_id" 
                                            class="w-full rounded-xl border border-border bg-muted/40 px-3 py-1.5 text-xs text-foreground focus:outline-none dark:border-zinc-800 dark:bg-zinc-950"
                                        >
                                            <option value="">Day Off / Off Duty</option>
                                            <option v-for="t in props.shiftTypes.filter(st => st.is_active)" :key="t.id" :value="t.id">
                                                {{ t.name }} ({{ t.code }})
                                            </option>
                                        </select>
                                    </div>

                                    <div class="col-span-4 flex items-center gap-2">
                                        <span class="text-xs text-muted-foreground shrink-0">Break:</span>
                                        <select 
                                            v-model="weeklyTemplateForm[day].break_hours" 
                                            :disabled="!weeklyTemplateForm[day].shift_type_id"
                                            class="w-full rounded-xl border border-border bg-muted/40 px-3 py-1.5 text-xs text-foreground focus:outline-none disabled:opacity-40 dark:border-zinc-800 dark:bg-zinc-950"
                                        >
                                            <option :value="0">None</option>
                                            <option :value="0.5">0.5 hr</option>
                                            <option :value="1.0">1.0 hr</option>
                                            <option :value="1.5">1.5 hr</option>
                                            <option :value="2.0">2.0 hr</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 pt-4 border-t border-border dark:border-zinc-800">
                            <button type="button" @click="showWeeklyTemplateModal = false" class="rounded-xl border border-border px-4 py-2 text-sm font-semibold hover:bg-muted dark:border-zinc-800">
                                Cancel
                            </button>
                            <button type="submit" :disabled="weeklyTemplateForm.processing" class="rounded-xl bg-primary px-4 py-2 text-sm font-semibold text-primary-foreground hover:bg-primary/95 transition-colors">
                                Save Template
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Transition>

        <!-- MODAL: CALENDAR SHIFT EVENT DETAILS -->
        <Transition name="fade">
            <div v-if="showEventDetailModal" class="fixed inset-0 z-50 flex items-center justify-center bg-zinc-950/65 backdrop-blur-sm p-4">
                <div class="w-full max-w-md rounded-3xl border border-border bg-card p-6 shadow-xl dark:border-zinc-800 dark:bg-zinc-900 animate-in fade-in duration-200">
                    <div class="flex items-center justify-between border-b border-border pb-4 mb-4 dark:border-zinc-800">
                        <h3 class="text-lg font-semibold text-foreground">
                            Scheduled Shift Details
                        </h3>
                        <button @click="showEventDetailModal = false" class="text-muted-foreground hover:text-foreground">
                            <X class="w-5 h-5" />
                        </button>
                    </div>

                    <div class="space-y-4 text-sm" v-if="selectedEvent">
                        <div class="flex items-center gap-3">
                            <span 
                                class="w-3.5 h-3.5 rounded-full shrink-0" 
                                :style="{ backgroundColor: selectedEvent.backgroundColor }"
                            ></span>
                            <span class="font-bold text-base text-foreground">
                                {{ selectedEvent.extendedProps.employee_name }}
                            </span>
                        </div>

                        <div class="rounded-2xl bg-muted/40 p-4 border border-border space-y-3 dark:border-zinc-800 dark:bg-zinc-950/30">
                            <div class="flex items-center justify-between">
                                <span class="text-muted-foreground text-xs">Date</span>
                                <span class="font-medium text-foreground">{{ selectedEvent.extendedProps.date }}</span>
                            </div>

                            <div class="flex items-center justify-between" v-if="selectedEvent.extendedProps.shift_name">
                                <span class="text-muted-foreground text-xs">Shift Type</span>
                                <span class="font-semibold text-foreground">{{ selectedEvent.extendedProps.shift_name }}</span>
                            </div>

                            <div class="flex items-center justify-between" v-if="selectedEvent.extendedProps.hours">
                                <span class="text-muted-foreground text-xs">Hours</span>
                                <span class="font-medium text-foreground">{{ selectedEvent.extendedProps.hours }}</span>
                            </div>

                            <div class="flex items-center justify-between" v-if="selectedEvent.extendedProps.break_hours !== undefined && selectedEvent.extendedProps.break_hours !== null">
                                <span class="text-muted-foreground text-xs">Break Hours</span>
                                <span class="font-medium text-foreground">{{ selectedEvent.extendedProps.break_hours }} hr</span>
                            </div>

                            <div class="flex items-center justify-between" v-if="selectedEvent.extendedProps.hourly_rate">
                                <span class="text-muted-foreground text-xs">Shift Hourly Rate</span>
                                <span class="font-medium text-foreground text-emerald-600 dark:text-emerald-400">
                                    ₱{{ Number(selectedEvent.extendedProps.hourly_rate).toFixed(2) }} / hr
                                </span>
                            </div>

                            <div class="flex items-center justify-between" v-if="selectedEvent.extendedProps.night_differential_rate">
                                <span class="text-muted-foreground text-xs">Night Differential</span>
                                <span class="font-medium text-foreground">
                                    {{ selectedEvent.extendedProps.night_differential_rate }}x multiplier
                                </span>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="text-muted-foreground text-xs">Schedule Type</span>
                                <span class="font-semibold text-xs uppercase px-2 py-0.5 rounded" 
                                    :class="selectedEvent.extendedProps.type === 'exception' ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/20 dark:text-amber-300' : 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-300'"
                                >
                                    {{ selectedEvent.extendedProps.type === 'exception' ? 'Date Override (' + selectedEvent.extendedProps.exception_type + ')' : 'Standard Assignment' }}
                                </span>
                            </div>

                            <div class="pt-2 border-t border-border dark:border-zinc-800 space-y-1" v-if="selectedEvent.extendedProps.reason || selectedEvent.extendedProps.notes">
                                <span class="text-muted-foreground text-xs block">Notes / Reason</span>
                                <span class="text-xs text-foreground block bg-card/60 p-2 rounded-lg border border-border dark:border-zinc-800">
                                    {{ selectedEvent.extendedProps.reason || selectedEvent.extendedProps.notes }}
                                </span>
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 pt-3 border-t border-border dark:border-zinc-800" v-if="props.isAdminOrHr">
                            <button 
                                type="button" 
                                @click="deleteCurrentCalendarEvent" 
                                class="rounded-xl border border-red-500/25 px-4 py-2 text-sm font-semibold text-red-500 hover:bg-red-500/10 transition-colors"
                            >
                                Remove Schedule
                            </button>
                            <button type="button" @click="showEventDetailModal = false" class="rounded-xl bg-muted px-4 py-2 text-sm font-semibold text-foreground hover:bg-muted/80">
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </AuthenticatedLayout>
</template>

<style>
.fc {
    font-family: inherit;
    --fc-border-color: var(--color-border);
    --fc-button-bg-color: var(--color-muted);
    --fc-button-border-color: var(--color-border);
    --fc-button-text-color: var(--color-foreground);
    --fc-button-hover-bg-color: var(--color-accent);
    --fc-button-hover-border-color: var(--color-border);
    --fc-button-active-bg-color: var(--color-secondary);
    --fc-button-active-border-color: var(--color-border);
    --fc-page-bg-color: transparent;
    --fc-today-bg-color: rgba(var(--color-primary), 0.05);
}

.fc .fc-toolbar-title {
    font-size: 1.15rem;
    font-weight: 700;
}

.fc .fc-button {
    font-size: 0.78rem;
    font-weight: 600;
    padding: 0.45em 0.85em;
    border-radius: 0.75rem;
    text-transform: capitalize;
    box-shadow: none !important;
}

.fc .fc-button-group > .fc-button:first-child {
    border-top-left-radius: 0.75rem;
    border-bottom-left-radius: 0.75rem;
}

.fc .fc-button-group > .fc-button:last-child {
    border-top-right-radius: 0.75rem;
    border-bottom-right-radius: 0.75rem;
}

.fc .fc-col-header-cell {
    font-size: 0.78rem;
    font-weight: 600;
    padding: 8px 0;
    background-color: var(--color-muted);
}

.fc-daygrid-event {
    border-radius: 6px !important;
    padding: 2px 4px !important;
    font-size: 0.72rem !important;
    font-weight: 550 !important;
    cursor: pointer;
}

.fc-h-event {
    border: none !important;
}

.fc-daygrid-day-number {
    font-size: 0.78rem;
    padding: 6px !important;
    color: var(--color-foreground);
}

.fc-theme-standard td, .fc-theme-standard th {
    border-color: var(--border) !important;
}

.dark .fc-theme-standard td, .dark .fc-theme-standard th {
    border-color: #27272a !important;
}

.dark .fc .fc-col-header-cell {
    background-color: #18181b;
}

.dark .fc .fc-today-bg-color {
    background-color: rgba(255, 255, 255, 0.03);
}

/* Transitions */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
