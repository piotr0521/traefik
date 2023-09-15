<script setup>
import { onMounted, reactive } from "vue";
import { useRoute, useRouter } from "vue-router";

import DatePickerLabelBase from "@/components/input/DatePickerLabelBase.vue";
import InputLabelBase from "@/components/input/InputLabelBase.vue";
import InputLabelPrefix from "@/components/input/InputLabelPrefix.vue";
import SelectTags from "@/components/input/SelectTags.vue";
import TextareaLabelBase from "@/components/input/TextareaLabelBase.vue";
import Preloader from "@/components/Preloader.vue";
import { useAssetTypeConfigStore } from "@/stores/assetTypeConfig";
import useBreadcrumbs from "@/utils/breadcrumbs";
import { goBack, submit } from "@/utils/position";

const state = reactive({
  form: {
    name: null,
    description: null,
    website: null,
    ownership: 0,
    originalValue: null,
    originalDate: null,
    currentValue: null,
    valueDate: null,
    tags: [],
    notes: null,
  },
  errors: {},
  isFetching: true,
});

const router = useRouter();
const route = useRoute();
const url = useAssetTypeConfigStore().getBySlug(route.params.slug).positionUrl; // url to save data

useBreadcrumbs.forAddPosition(route.params.slug, "Add Business");

onMounted(() => {
  state.isFetching = false;
});
</script>
<template>
  <div class="py-5 px-9 grid">
    <h1 class="text-indigo-900 text-3xl mb-4">Add Business</h1>

    <form class="grid gap-5 bg-white p-8 max-w-screen-md" @submit.prevent="submit(state, url, router)">
      <InputLabelBase
        v-model:value="state.form.name"
        v-model:error="state.errors.name"
        label="Name *"
        name="groshy_business_add_name"
      />

      <InputLabelBase
        v-model:value="state.form.description"
        v-model:error="state.errors.description"
        label="Description"
        name="groshy_business_add_description"
      />

      <InputLabelBase
        v-model:value="state.form.website"
        v-model:error="state.errors.website"
        label="Website"
        name="groshy_business_add_website"
      />

      <InputLabelBase
        v-model:value="state.form.ownership"
        v-model:error="state.errors.ownership"
        label="Ownership, % *"
        name="groshy_business_add_ownership"
      />

      <InputLabelPrefix
        v-model:value="state.form.originalValue"
        v-model:error="state.errors.originalValue"
        label="Original Investment / Purchase Cost *"
        name="groshy_business_add_originalValue"
      />

      <DatePickerLabelBase
        v-model:value="state.form.originalDate"
        v-model:error="state.errors.originalDate"
        label="Founded/Purchase Date *"
        name="groshy_business_originalDate"
      />

      <InputLabelPrefix
        v-model:value="state.form.currentValue"
        v-model:error="state.errors.currentValue"
        label="Current Valuation *"
        name="groshy_business_add_currentValue"
      />

      <DatePickerLabelBase
        v-model:value="state.form.valueDate"
        v-model:error="state.errors.valueDate"
        label="Valuation Date *"
        name="groshy_business_valueDate"
      />

      <SelectTags v-model:value="state.form.tags" v-model:error="state.errors.tags" />

      <TextareaLabelBase v-model:value="state.form.notes" label="Notes" name="groshy_business_add_notes" />

      <div class="flex gap-8 justify-center mt-4">
        <button type="button" class="btn btn-cancel" @click="goBack(router)">Cancel</button>

        <button type="submit" class="btn btn-primary">Submit</button>
      </div>
    </form>
  </div>
  <Preloader v-if="state.isFetching" />
</template>
