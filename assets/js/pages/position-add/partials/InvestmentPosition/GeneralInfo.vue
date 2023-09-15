<script setup lang="ts">
import { computed } from "vue";

import InputLabelBase from "@/components/input/InputLabelBase.vue";
import SelectAccountHolder from "@/components/input/SelectAccountHolder.vue";
import SelectInstitution from "@/components/input/SelectInstitution.vue";
import type { AccountHolder } from "@/entities/account-holder";
import type { Institution } from "@/entities/institutions";
type Form = {
  name: string;
  accountHolder: AccountHolder | AccountHolder["id"] | null;
  institution: Institution | Institution["id"] | null;
};
const emit = defineEmits(["update:form", "update:errors"]);
const props = defineProps(["form", "errors", "meta", "isDirect"]);
const form = computed<Form>(() => props.form);
const setForm = (value: any, key: keyof Form) => {
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
        name="groshy_investment_add_name"
        @update:value="setForm($event, 'name')"
      />
      <SelectAccountHolder
        :value="form.accountHolder"
        :error="meta.accountHolder.touched ? errors.accountHolder : ''"
        label="Account Holder"
        @update:value="setForm($event, 'accountHolder')"
      />
      <SelectInstitution
        :value="form.institution"
        :error="meta.institution.touched ? errors.institution : ''"
        label="Invested on"
        :disabled="isDirect"
        @update:value="setForm($event, 'institution')"
      />
    </div>
  </div>
</template>
