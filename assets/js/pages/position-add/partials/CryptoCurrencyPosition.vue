<script setup>
import { onMounted, reactive } from "vue";
import { useRoute, useRouter } from "vue-router";

import DatePickerLabelBase from "@/components/input/DatePickerLabelBase.vue";
import InputLabelBase from "@/components/input/InputLabelBase.vue";
import InputLabelPrefix from "@/components/input/InputLabelPrefix.vue";
import SelectInstitution from "@/components/input/SelectInstitution.vue";
import SelectLabelBase from "@/components/input/SelectLabelBase.vue";
import SelectTags from "@/components/input/SelectTags.vue";
import TextareaLabelBase from "@/components/input/TextareaLabelBase.vue";
import Preloader from "@/components/Preloader.vue";
import { useAssetTypeConfigStore } from "@/stores/assetTypeConfig";
import useBreadcrumbs from "@/utils/breadcrumbs";
import { fetchParamsSymbol, fetchSymbol, goBack, submit } from "@/utils/position";

const state = reactive({
  form: {
    asset: null,
    quantity: null,
    averagePrice: null,
    purchaseDate: null,
    institution: null,
    tags: [],
    notes: null,
  },
  errors: {},
  isFetching: true,
  assets: [],
});

const router = useRouter();
const route = useRoute();
const url = useAssetTypeConfigStore().getBySlug(route.params.slug).positionUrl; // url to save data

useBreadcrumbs.forAddPosition(route.params.slug, "Add Crypto Position");

async function fetchOptions() {
  state.assets = await fetchSymbol(`/api/asset/crypto`);
}

async function searchOptions(value) {
  state.assets = await fetchParamsSymbol(`/api/asset/crypto`, {
    page: 1,
    symbol: value,
  });
}

onMounted(() => {
  fetchOptions();
  state.isFetching = false;
});
</script>
<template>
  <div class="py-5 px-9 grid">
    <h1 class="text-indigo-900 text-3xl mb-4">Add Crypto Position</h1>

    <form class="grid gap-5 bg-white p-8 max-w-screen-md" @submit.prevent="submit(state, url, router)">
      <SelectLabelBase
        v-model:value="state.form.asset"
        v-model:error="state.errors.asset"
        label="Symbol *"
        placeholder="Select Symbol"
        :options="state.assets"
        @search="searchOptions"
      />

      <InputLabelBase
        v-model:value.number="state.form.quantity"
        v-model:error="state.errors.quantity"
        label="Quantity *"
        name="groshy_certificate_deposit_add_quantity"
      />

      <InputLabelPrefix
        v-model:value="state.form.averagePrice"
        v-model:error="state.errors.averagePrice"
        label="Average Price *"
        name="groshy_certificate_deposit_add_averagePrice"
      />

      <DatePickerLabelBase
        v-model:value="state.form.purchaseDate"
        v-model:error="state.errors.purchaseDate"
        label="Purchase Date *"
        name="groshy_certificate_deposit_add_purchaseDate"
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
