<script setup lang="ts">
import { computed, ref, watch } from 'vue';

const props = defineProps<{
  modelValue: string[];
  otherSelectedValue?: string;
  otherDescription?: string;
  noneSelected?: boolean;
}>();

const emit = defineEmits<{
  (e: 'update:modelValue', v: string[]): void;
  (e: 'update:otherDescription', v: string): void;
}>();

const selected = ref<string[]>(props.modelValue ?? []);
const otherDescription = ref<string>(props.otherDescription ?? '');

const ministryOptions = [
  'conduct_cg',
  'cg_collaboration',
  'join_cg',
  'campus_missions',
  'morning_devotion',
  'kids_devotion',
  'family_devotion',
  'evangelism',
  'prayer_soaking',
  'prayer_empowerment',
  'empowerment_with_others',
  'simpaaralan',
  'preacher',
  'remedials_lcsol',
  'visiting_sick',
  'week_of_fire',
  'boc',
  'mentoring',
  'ho_mentoring_meeting',
  'nightlife',
  'convention',
  'other',
  'none',
];

const normalized = computed(() => selected.value);

watch(
  () => props.modelValue,
  (v) => {
    selected.value = v ?? [];
  }
);

watch(
  () => props.otherDescription,
  (v) => {
    otherDescription.value = v ?? '';
  }
);

watch(selected, (v) => {
  // If none is selected, clear all others
  if (v.includes('none')) {
    emit('update:modelValue', ['none']);
    return;
  }

  // If other selected, keep other_description editable.
  // If other not selected, clear other_description
  if (!v.includes('other')) {
    otherDescription.value = '';
    emit('update:otherDescription', '');
  }

  emit('update:modelValue', v);
});

const setToggle = (value: string) => {
  if (value === 'none') {
    selected.value = ['none'];
    return;
  }
  if (selected.value.includes('none')) {
    selected.value = selected.value.filter((x) => x !== 'none');
  }

  if (selected.value.includes(value)) {
    selected.value = selected.value.filter((x) => x !== value);
  } else {
    selected.value = [...selected.value, value];
  }

  // if toggling on 'other', keep it; if toggling off it, watcher will clear description.
  if (!selected.value.includes('other') && value === 'other') {
    otherDescription.value = '';
    emit('update:otherDescription', '');
  }
};

const isOtherSelected = computed(() => selected.value.includes('other'));
</script>

<template>
  <div class="space-y-4">
    <h3 class="text-lg font-semibold text-foreground">Ministry Involvement</h3>
    <p class="text-sm text-muted-foreground">Select all that apply. If you choose "None", it will clear other choices.</p>

    <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
      <label
        v-for="opt in ministryOptions"
        :key="opt"
        class="flex items-center gap-2 rounded-xl border border-border bg-muted/20 px-3 py-2 text-xs"
      >
        <input
          type="checkbox"
          :checked="normalized.includes(opt)"
          @change="setToggle(opt)"
        />
        <span class="capitalize">{{ opt.replaceAll('_', ' ') }}</span>
      </label>
    </div>

    <div v-if="isOtherSelected" class="space-y-2">
      <label class="text-sm font-medium text-foreground">Custom description (Other)</label>
      <textarea
        v-model="otherDescription"
        @input="emit('update:otherDescription', otherDescription)"
        class="w-full rounded-2xl border border-border bg-muted px-4 py-3 text-sm text-foreground"
        placeholder="Describe your other ministry involvement"
      />
    </div>
  </div>
</template>

