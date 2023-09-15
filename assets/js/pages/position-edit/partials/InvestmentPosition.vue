<script setup>
import { onMounted, reactive } from "vue";
import { useRoute, useRouter } from "vue-router";

import InputLabelPrefix from "@/components/input/InputLabelPrefix.vue";
import SelectTags from "@/components/input/SelectTags.vue";
import TextareaLabelBase from "@/components/input/TextareaLabelBase.vue";
import Preloader from "@/components/Preloader.vue";
import { useAssetTypeConfigStore } from "@/stores/assetTypeConfig";
import useBreadcrumbs from "@/utils/breadcrumbs";
import { getData, goBack, update } from "@/utils/position";

const state = reactive({
  form: {
    capitalCommitment: null,
    tags: [],
    notes: null,
  },
  errors: {},
  isFetching: true,
});

const router = useRouter();
const route = useRoute();

const url = `${useAssetTypeConfigStore().getBySlug(route.params.slug).positionUrl}/${route.params.uuid}`; // url to save data

useBreadcrumbs.forAddPosition(route.params.slug, "Update Investment");

async function getPosition() {
  const data = await getData(url);
  state.form.capitalCommitment = data.capitalCommitment.base;
  state.form.notes = data.notes;
  state.form.tags = data.tags.map((item) => item["@id"]);
  state.isFetching = false;
}

onMounted(() => {
  getPosition();
});
</script>
<template>
  <div class="py-5 px-9 grid">
    <form class="grid gap-5 bg-white p-8 max-w-screen-md" @submit.prevent="update(state, url, router)">
      <InputLabelPrefix
        v-model:value="state.form.capitalCommitment"
        v-model:error="state.errors.capitalCommitment"
        label="Total Commitment"
        name="groshy_investment_edit_commitment"
      />

      <SelectTags v-model:value="state.form.tags" v-model:error="state.errors.tags" />

      <TextareaLabelBase v-model:value="state.form.notes" label="Notes" name="groshy_investment_edit_notes" />

      <div class="flex gap-8 justify-center mt-4">
        <button type="button" class="btn btn-cancel" :disabled="state.isFetching" @click="goBack(router)">
          Cancel
        </button>

        <button type="submit" class="btn btn-primary" :disabled="state.isFetching">Submit</button>
      </div>
    </form>
  </div>
  <Preloader v-if="state.isFetching" />
</template>
