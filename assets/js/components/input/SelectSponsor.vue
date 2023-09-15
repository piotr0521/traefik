<script setup lang="ts">
import _debounce from "lodash/debounce";
import { computed, onMounted, type PropType, reactive } from "vue";

import { usePopupStore } from "@/stores/popup";
import { fetchParams } from "@/utils/position";

import SelectLabelBase from "./SelectLabelBase.vue";
async function fetchSponsors(name = "") {
  search.cancel();
  state.sponsors = await fetchParams(`/api/sponsors`, {
    name,
  });
  state.isFetching = false;
}

const search = _debounce(fetchSponsors, 350);
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
  hideAdd: {
    type: Boolean,
  },
});
const emit = defineEmits(["update:value", "update:error"]);
const { setPopupState } = usePopupStore();
const state = reactive({
  sponsors: [],
  isFetching: true,
});
const selected = computed({
  get() {
    return props.value;
  },
  set(newValue) {
    emit("update:value", newValue);
  },
});

onMounted(() => {
  state.isFetching = true;
  fetchSponsors();
});
</script>
<template>
  <SelectLabelBase
    v-model:value="selected"
    :error="error"
    :label="label"
    placeholder="Select sponsor"
    :options="state.sponsors"
    :loading="state.isFetching"
    :disabled="disabled"
    :filterable="false"
    @search="search"
  >
    <li
      v-if="!hideAdd"
      class="underline text-blue-700 text-center cursor-pointer"
      @click="setPopupState({ popupName: 'showPopupSponsorAdd', value: true })"
    >
      Add new sponsor
    </li>
  </SelectLabelBase>
</template>
<style scoped></style>
