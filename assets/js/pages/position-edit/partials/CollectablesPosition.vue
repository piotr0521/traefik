<script setup>
import { onMounted, reactive, ref } from "vue";
import { useRoute, useRouter } from "vue-router";

import InputLabelBase from "@/components/input/InputLabelBase.vue";
import SelectTags from "@/components/input/SelectTags.vue";
import TextareaLabelBase from "@/components/input/TextareaLabelBase.vue";
import Preloader from "@/components/Preloader.vue";
import { useAssetTypeConfigStore } from "@/stores/assetTypeConfig";
import useBreadcrumbs from "@/utils/breadcrumbs";
import { getData, goBack, update } from "@/utils/position";

const state = reactive({
  form: {
    name: null,
    tags: [],
    notes: null,
  },
  errors: {},
  isFetching: true,
});
const selected = ref(null);

const router = useRouter();
const route = useRoute();
const url = `${useAssetTypeConfigStore().getBySlug(route.params.slug).positionUrl}/${route.params.uuid}`; // url to save data

useBreadcrumbs.forAddPosition(route.params.slug, "Update Collectable");

async function getPosition() {
  const data = await getData(url);
  state.form.name = data.name;
  state.form.notes = data.notes;

  selected.value = data.tags.map((item) => item["@id"]);
}

onMounted(() => {
  getPosition();
  state.isFetching = false;
});
</script>
<template>
  <div class="py-5 px-9 grid">
    <h1 class="text-indigo-900 text-3xl mb-4">Update Collectable</h1>
    <form class="card grid gap-5 p-8 max-w-screen-md" @submit.prevent="update(state, url, router)">
      <InputLabelBase
        v-model:value="state.form.name"
        v-model:error="state.errors.name"
        label="Name *"
        name="groshy_cash_add_name"
      />

      <SelectTags v-model:value="state.form.tags" v-model:error="state.errors.tags" :selected="selected" />

      <TextareaLabelBase v-model:value="state.form.notes" label="Notes" name="groshy_cash_add_notes" />

      <div class="flex gap-8 justify-center mt-4">
        <button type="button" class="btn btn-cancel" @click="goBack(router)">Cancel</button>

        <button type="submit" class="btn btn-primary">Submit</button>
      </div>
    </form>
  </div>
  <Preloader v-if="state.isFetching" />
</template>
