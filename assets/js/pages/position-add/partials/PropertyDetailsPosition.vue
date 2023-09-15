<script setup>
import { onMounted, reactive } from "vue";
import { useRoute, useRouter } from "vue-router";

import DatePickerLabelBase from "@/components/input/DatePickerLabelBase.vue";
import InputLabelBase from "@/components/input/InputLabelBase.vue";
import InputLabelPrefix from "@/components/input/InputLabelPrefix.vue";
import SelectLabelBase from "@/components/input/SelectLabelBase.vue";
import SelectTags from "@/components/input/SelectTags.vue";
import TextareaLabelBase from "@/components/input/TextareaLabelBase.vue";
import Preloader from "@/components/Preloader.vue";
import { useAssetTypeConfigStore } from "@/stores/assetTypeConfig";
import useBreadcrumbs from "@/utils/breadcrumbs";
import { goBack, submit } from "@/utils/position";

const state = reactive({
  form: {
    name: null,
    address: null,
    propertyType: null,
    units: null,
    website: null,
    purchaseDate: null,
    purchaseValue: null,
    currentValue: null,
    tags: [],
    notes: null,
  },
  errors: {},

  propertyTypes: [
    { label: "Single Family", code: "Single Family" },
    { label: "Multifamily", code: "Multifamily" },
    { label: "Office", code: "Office" },
    { label: "Industrial", code: "Industrial" },
    { label: "Retail", code: "Retail" },
    { label: "Hospitality", code: "Hospitality" },
    { label: "Land", code: "Land" },
    { label: "Storage", code: "Storage" },
    { label: "Medical", code: "Medical" },
    { label: "Car Wash", code: "Car Wash" },
    { label: "ATM", code: "ATM" },
    { label: "Mobile Home Park", code: "Mobile Home Park" },
    { label: "Mixed Use", code: "Mixed Use" },
    { label: "Senior Housing", code: "Senior Housing" },
    { label: "Student Housing", code: "Student Housing" },
    { label: "Data Center", code: "Data Center" },
    { label: "Parking", code: "Parking" },
    { label: "Other", code: "Other" },
  ],
  isFetching: true,
});

const router = useRouter();
const route = useRoute();
const url = useAssetTypeConfigStore().getBySlug(route.params.slug).positionUrl; // url to save data

useBreadcrumbs.forAddPosition(route.params.slug, "Add Cash Account");

onMounted(() => {
  state.isFetching = false;
});
</script>
<template>
  <div class="py-5 px-9 grid">
    <form class="grid gap-5 bg-white p-8 max-w-screen-md" @submit.prevent="submit(state, url, router)">
      <h2 class="text-indigo-900 text-3xl mb-3">Property Details</h2>

      <InputLabelBase
        v-model:value="state.form.name"
        v-model:error="state.errors.name"
        label="Name *"
        name="groshy_property_details_add_name"
      />

      <InputLabelBase
        v-model:value="state.form.address"
        v-model:error="state.errors.address"
        label="Address"
        name="groshy_property_details_add_address"
      />

      <SelectLabelBase
        v-model:value="state.form.propertyType"
        v-model:error="state.errors.propertyType"
        label="Property Type *"
        placeholder="Select Property Type"
        :options="state.propertyTypes"
      />

      <InputLabelBase
        v-model:value="state.form.units"
        v-model:error="state.errors.units"
        label="Numbe of units"
        name="groshy_property_details_add_units"
      />

      <InputLabelBase
        v-model:value="state.form.website"
        v-model:error="state.errors.website"
        label="Website"
        name="groshy_property_details_add_website"
      />

      <h2 class="text-indigo-900 text-3xl my-3">Investment Details</h2>

      <DatePickerLabelBase
        v-model:value="state.form.purchaseDate"
        v-model:error="state.errors.purchaseDate"
        label="Purchase date *"
        name="groshy_property_details_add_purchaseDate"
      />

      <InputLabelPrefix
        v-model:value="state.form.purchaseValue"
        v-model:error="state.errors.purchaseValue"
        label="Purchase Price *"
        name="groshy_property_details_add_purchaseValue"
      />

      <InputLabelPrefix
        v-model:value="state.form.currentValue"
        v-model:error="state.errors.currentValue"
        label="Current Price *"
        name="groshy_property_details_add_currentValue"
      />

      <SelectTags v-model:value="state.form.tags" v-model:error="state.errors.tags" />

      <TextareaLabelBase v-model:value="state.form.notes" label="Notes" name="groshy_cash_add_notes" />

      <div class="flex gap-8 justify-center mt-4">
        <button type="button" class="btn btn-cancel" @click="goBack(router)">Cancel</button>

        <button type="submit" class="btn btn-primary">Submit</button>
      </div>
    </form>
  </div>
  <Preloader v-if="state.isFetching" />
</template>
