<script setup lang="ts">
import _debounce from "lodash/debounce";
import { computed, type PropType, ref, watch } from "vue";

import { usePopupStore } from "@/stores/popup";
import { fetchParams } from "@/utils/position";

import SelectLabelBase from "./SelectLabelBase.vue";
async function fetchAssets(name = "") {
  search.cancel();
  assets.value = await fetchParams(`/api/asset/investments`, {
    sponsor: props.value,
    name,
  });
  isFetching.value = false;
}

const search = _debounce(fetchAssets, 350);
const props = defineProps({
  label: {
    type: String,
    required: true,
  },
  value: {
    type: null as unknown as PropType<string | null>,
  },
  disabled: {
    type: Boolean,
  },
  error: {
    type: String,
  },
  sponsor: {
    type: null as unknown as PropType<string | null>,
  },
  hideAdd: {
    type: Boolean,
  },
});
const emit = defineEmits(["update:value", "update:error"]);
const { setPopupState } = usePopupStore();
watch(
  () => props.sponsor,
  (sponsor) => {
    if (sponsor) {
      selected.value = null;
      isFetching.value = true;
      fetchAssets();
    }
  }
);
const assets = ref([]);
const isFetching = ref(false);
const selected = computed({
  get() {
    return props.value;
  },
  set(newValue) {
    emit("update:value", newValue);
  },
});
</script>
<template>
  <SelectLabelBase
    v-model:value="selected"
    :error="error"
    :label="label"
    placeholder="Select investment"
    :disabled="!sponsor || disabled"
    :options="assets"
    :loading="isFetching"
    :filterable="false"
    @search="search"
  >
    <li
      v-if="!hideAdd"
      class="underline text-blue-700 text-center cursor-pointer"
      @click="setPopupState({ popupName: 'showPopupInvestmentAdd', value: true })"
    >
      Add new investment
    </li>
  </SelectLabelBase>
</template>
