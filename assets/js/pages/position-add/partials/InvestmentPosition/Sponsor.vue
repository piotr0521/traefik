<script setup lang="ts">
import { computed } from "vue";

import CheckboxLabelBase from "@/components/input/CheckboxLabelBase.vue";
import SelectSponsor from "@/components/input/SelectSponsor.vue";
import type { Sponsor } from "@/entities/sponsors";
type Form = {
  sponsor: Sponsor | Sponsor["id"] | null;
  isDirect: boolean;
};
const form = computed<Form>(() => props.form);
const props = defineProps(["form", "errors", "meta"]);
const emit = defineEmits(["update:form", "update:errors"]);
const setForm = (value: any, key: keyof Form) => {
  emit("update:form", { ...form.value, [key]: value });
};
</script>
<template>
  <div>
    <h1 class="text-xl font-normal text-indigo-900 mb-5">Sponsor</h1>
    <div class="grid gap-2">
      <CheckboxLabelBase
        :value="form.isDirect"
        label="Invested directly with sponsor"
        name="groshy_investment_add_isDirect"
        @update:value="setForm($event, 'isDirect')"
      />

      <SelectSponsor
        label="Sponsor"
        :value="form.sponsor"
        :error="meta.sponsor.touched ? errors.sponsor : ''"
        hide-add
        @update:value="setForm($event, 'sponsor')"
      />
    </div>
  </div>
</template>
