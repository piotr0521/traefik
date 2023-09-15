<script setup lang="ts">
import { computed } from "vue";

import InputLabelPrefix from "@/components/input/InputLabelPrefix.vue";
import SelectAsset from "@/components/input/SelectAsset.vue";
import type { Asset } from "@/entities/asset";
type Form = {
  asset: Asset | Asset["id"] | null;
  capitalCommitment: number;
};
const emit = defineEmits(["update:form", "update:errors"]);
const props = defineProps(["form", "errors", "meta", "sponsor"]);
const form = computed<Form>(() => props.form);
const setForm = (value: any, key: keyof Form) => {
  emit("update:form", { ...form.value, [key]: value });
};
</script>
<template>
  <div>
    <h1 class="text-xl font-normal text-indigo-900 mb-5">Investment</h1>
    <div class="grid gap-2">
      <SelectAsset
        :value="form.asset"
        :error="meta.asset.touched ? errors.asset : ''"
        :sponsor="sponsor"
        label="Investment"
        hide-add
        @update:value="setForm($event, 'asset')"
      />
      <InputLabelPrefix
        :value="form.capitalCommitment"
        :error="meta.capitalCommitment.touched ? errors.capitalCommitment : ''"
        label="Total Commitment"
        name="groshy_investment_add_commitment"
        @update:value="setForm($event, 'capitalCommitment')"
      />
    </div>
  </div>
</template>
