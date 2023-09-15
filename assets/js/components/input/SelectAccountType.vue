<script setup>
import { computed, reactive, watch } from "vue";

import { useAccountTypeStore } from "@/stores/accountType.js";

import SelectLabelBase from "./SelectLabelBase.vue";

const props = defineProps({
  root: {
    type: String,
    required: true,
    comment:
      "Root type. Account types represented as 2 level trees, we should not have any use cases when root is not required",
  },
  label: {
    type: String,
    default: "Select Account Type",
  },
  placeholder: {
    type: String,
    default: "Select account type",
  },
  value: {
    type: String,
  },
  error: {
    type: String,
  },
});

const state = reactive({
  value: props.value,
});

const emit = defineEmits(["update:value", "update:error"]);
const accountTypeStore = useAccountTypeStore();
const error = computed({
  get() {
    return props.error;
  },
  set(value) {
    emit("update:error", value);
  },
});
const accountTypeOptions = computed(() => {
  return accountTypeStore.byParent(props.root).map((item) => {
    return {
      code: item["@id"],
      label: item.name,
    };
  });
});

watch(
  () => state.value,
  (newValue) => {
    emit("update:value", newValue);
  }
);
</script>

<template>
  <div>
    <SelectLabelBase
      v-model:value="state.value"
      v-model:error="error"
      :label="label"
      :placeholder="placeholder"
      :options="accountTypeOptions"
    />
  </div>
</template>
