<script setup lang="ts">
import { computed } from "vue";

import InputLabelBase from "@/components/input/InputLabelBase.vue";
import SelectAccountHolder from "@/components/input/SelectAccountHolder.vue";
import SelectInstitution from "@/components/input/SelectInstitution.vue";

const props = defineProps(["form", "errors", "meta"]);
const emit = defineEmits(["update:form"]);
type Form = {
  name: string;
  institution: string;
  accountHolder: string;
};
const form = computed<Form>(() => props.form);
const setForm = (value: string, key: keyof Form) => {
  emit("update:form", { ...form.value, [key]: value });
};
</script>
<template>
  <div>
    <h1 class="text-xl font-normal text-indigo-900 mb-5">General Info</h1>
    <div class="grid gap-2">
      <InputLabelBase
        :value="form.name"
        :error="meta.name.touched ? errors.name : ''"
        label="Name *"
        name="groshy_credit_card_add_name"
        @update:value="setForm($event, 'name')"
      />
      <SelectInstitution
        :value="form.institution"
        :error="meta.institution.touched ? errors.institution : ''"
        label="Issued by *"
        @update:value="setForm($event, 'institution')"
      />
      <SelectAccountHolder
        :value="form.accountHolder"
        :error="meta.accountHolder.touched ? errors.accountHolder : ''"
        label="Card owner *"
        @update:value="setForm($event, 'accountHolder')"
      />
    </div>
  </div>
</template>
<style scoped></style>
