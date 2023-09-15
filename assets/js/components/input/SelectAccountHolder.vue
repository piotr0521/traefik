<script setup>
import { computed } from "vue";

import { useAccountHolderStore } from "@/stores/accountHolder";

import SelectLabelBase from "./SelectLabelBase.vue";

const props = defineProps({
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
const emit = defineEmits(["update:value", "update:error"]);
const accountHolderStore = useAccountHolderStore();

const accountHolderOptions = computed(() => {
  return accountHolderStore.items.map((item) => {
    return {
      code: item["@id"],
      label: item.name,
    };
  });
});
const error = computed({
  get() {
    return props.error;
  },
  set(value) {
    emit("update:error", value);
  },
});
const selected = computed({
  get() {
    return props.value;
  },
  set(value) {
    emit("update:value", value);
  },
});

// If there is only one account holder, then select it.
if (accountHolderOptions.value.length === 1) {
  selected.value = accountHolderOptions.value[0].code;
}
</script>

<template>
  <div>
    <SelectLabelBase
      v-model:value="selected"
      v-model:error="error"
      :label="label"
      :placeholder="placeholder"
      :options="accountHolderOptions"
    />
  </div>
</template>
