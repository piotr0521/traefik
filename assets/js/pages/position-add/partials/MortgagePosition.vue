<script setup>
import { onMounted, reactive } from "vue";
import { useRoute, useRouter } from "vue-router";

import DatePickerLabelBase from "@/components/input/DatePickerLabelBase.vue";
import InputLabelBase from "@/components/input/InputLabelBase.vue";
import InputLabelPrefix from "@/components/input/InputLabelPrefix.vue";
import SelectInstitution from "@/components/input/SelectInstitution.vue";
import SelectTags from "@/components/input/SelectTags.vue";
import TextareaLabelBase from "@/components/input/TextareaLabelBase.vue";
import Preloader from "@/components/Preloader.vue";
import { useAssetTypeConfigStore } from "@/stores/assetTypeConfig";
import useBreadcrumbs from "@/utils/breadcrumbs";
import { goBack, submit } from "@/utils/position";

const state = reactive({
  form: {
    name: null,
    mortgageAmount: null,
    mortgageDate: null,
    terms: null,
    interest: null,
    institution: null,
    tags: [],
    notes: null,
  },
  errors: {},

  isFetching: true,
});

const router = useRouter();
const route = useRoute();
const url = useAssetTypeConfigStore().getBySlug(route.params.slug).positionUrl; // url to save data

useBreadcrumbs.forAddPosition(route.params.slug, "Add Mortgage");

onMounted(() => {
  state.isFetching = false;
});
</script>

<template>
  <div class="py-5 px-9 grid">
    <h1 class="text-indigo-900 text-3xl mb-4">Add Mortgage</h1>

    <form class="grid gap-5 bg-white p-8 max-w-screen-md" @submit.prevent="submit(state, url, router)">
      <InputLabelBase
        v-model:value="state.form.name"
        v-model:error="state.errors.name"
        label="Name *"
        name="groshy_mortgage_add_name"
      />

      <InputLabelPrefix
        v-model:value="state.form.mortgageAmount"
        v-model:error="state.errors.mortgageAmount"
        label="Mortgage Amount *"
        name="groshy_mortgage_add_mortgageAmount"
      />

      <DatePickerLabelBase
        v-model:value="state.form.mortgageDate"
        v-model:error="state.errors.mortgageDate"
        label="Deposit Date *"
        name="groshy_mortgage_date"
      />

      <InputLabelBase
        v-model:value.number="state.form.terms"
        v-model:error="state.errors.terms"
        label="Terms (years) *"
        name="groshy_mortgage_add_terms"
      />

      <InputLabelBase
        v-model:value.number="state.form.interest"
        v-model:error="state.errors.interest"
        label="Mortgage Rate *"
        name="groshy_mortgage_add_iterest"
      />

      <SelectInstitution
        v-model:value="state.form.institution"
        v-model:error="state.errors.institution"
        label="Invested on"
      />

      <SelectTags v-model:value="state.form.tags" v-model:error="state.errors.tags" />

      <TextareaLabelBase v-model:value="state.form.notes" label="Notes" name="groshy_certificate_deposit_add_notes" />

      <div class="flex gap-8 justify-center mt-4">
        <button type="button" class="btn btn-cancel" @click="goBack(router)">Cancel</button>

        <button type="submit" class="btn btn-primary">Submit</button>
      </div>
    </form>
  </div>
  <Preloader v-if="state.isFetching" />
</template>
