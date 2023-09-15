<script setup lang="ts">
import { computed } from "vue";

import InputLabelPrefix from "@/components/input/InputLabelPrefix.vue";

const props = defineProps(["form", "errors", "meta"]);
const emit = defineEmits(["update:form", "update:errors"]);
type Form = {
  cardLimit: number;
  cardBalance: number;
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
        :value="form.cardLimit"
        :error="meta.cardLimit.touched ? errors.cardLimit : ''"
        label="Card Limit"
        name="groshy_credit_card_add_limit"
        type="number"
        @update:value="setForm($event, 'cardLimit')"
      />
      <InputLabelPrefix
        :value="form.cardBalance"
        :error="meta.cardBalance.touched ? errors.cardBalance : ''"
        label="Card balance"
        name="groshy_credit_card_add_balance"
        type="number"
        @update:value="setForm($event, 'cardBalance')"
      />
    </div>
  </div>
</template>
<style scoped></style>
