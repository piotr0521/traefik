<script setup lang="ts">
import { computed } from "vue";

import InputLabelPrefix from "@/components/input/InputLabelPrefix.vue";

const props = defineProps(["form", "errors", "meta"]);
const emit = defineEmits(["update:form", "update:errors"]);
type Form = {
  yield: number;
  currentValue: number;
};
const form = computed<Form>(() => props.form);
const setForm = (value: number, key: keyof Form) => {
  emit("update:form", { ...form.value, [key]: value });
};
</script>
<template>
  <div>
    <h1 class="text-xl font-normal text-indigo-900 mb-5">Details</h1>
    <div class="grid gap-2">
      <InputLabelPrefix
        :value="form.yield"
        :error="meta.yield.touched ? errors.yield : ''"
        label="Yield"
        name="groshy_cash_add[yield]"
        type="number"
        unit="%"
        number-format="decimal"
        @update:value="setForm($event, 'yield')"
      />
      <InputLabelPrefix
        :value="form.currentValue"
        :error="meta.currentValue.touched ? errors.currentValue : ''"
        label="Current balance"
        name="groshy_cash_add[currentValue]"
        type="number"
        @update:value="setForm($event, 'currentValue')"
      />
    </div>
  </div>
</template>
<style scoped></style>
