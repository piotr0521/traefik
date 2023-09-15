<script setup>
import { computed } from "vue";
import vSelect from "vue-select";

const props = defineProps({
  clearSearchOnSelect: {
    type: Boolean,
    default: true,
    comment: "Clear search input when an option is selected",
  },
  multiple: {
    default: false,
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  filterable: {
    type: Boolean,
    default: true,
  },
  searchable: {
    type: Boolean,
    default: true,
  },
  label: {
    type: String,
  },
  placeholder: {
    type: String,
    default: "",
  },
  options: {
    type: Array,
    required: true,
  },
  value: {
    type: [String, Array, null],
  },
  selected: {
    type: [String, Array],
    default: null,
  },
  loading: {
    type: Boolean,
    default: false,
  },
  error: {
    type: String,
    default: null,
  },
  hideSelected: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(["update:value", "search"]);

const hideSelectedOption = (option) => {
  if (props.hideSelected) {
    if (props.multiple && selected.value) {
      return !selected.value.includes(option.code);
    }
    return option.code !== selected.value;
  }
  return true;
};
const selected = computed({
  get() {
    return props.value;
  },
  set(newValue) {
    emit("update:value", newValue);
  },
});
</script>
<template>
  <div class="grid gap-1">
    <label class="form-label mb-2">{{ label }}</label>
    <vSelect
      v-model="selected"
      :reduce="(option) => option.code"
      :class="{ error: error }"
      :options="options"
      :placeholder="placeholder"
      :multiple="multiple"
      :searchable="searchable"
      :loading="loading"
      :disabled="disabled"
      :filterable="filterable"
      :selectable="hideSelectedOption"
      :close-on-select="!multiple"
      @search="$emit('search', $event)"
    >
      <template #selected-option-container="{ option, deselect }">
        <slot name="selected-option-container" :option="option" :deselect="deselect" />
      </template>
      <template #list-footer>
        <slot></slot>
      </template>
    </vSelect>

    <span v-if="error" class="form-error text-sm">
      {{ error }}
    </span>
  </div>
</template>
