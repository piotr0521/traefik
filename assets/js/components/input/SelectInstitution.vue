<script setup>
import _debounce from "lodash/debounce";
import { computed, onMounted, reactive } from "vue";

import InstitutionAdd from "@/pages/institutions/popup/AddInstitutionForm.vue";
import { usePopupStore } from "@/stores/popup.js";
import { fetchParams } from "@/utils/position";

import SelectLabelBase from "./SelectLabelBase.vue";

const props = defineProps(["label", "value", "disabled", "error"]);
let selected = computed({
  get() {
    return props.value;
  },
  set(newValue) {
    emit("update:value", newValue);
  },
});

const state = reactive({
  institutions: [],
  isFetching: true,
});

const { setPopupState, getPopupState } = usePopupStore();
const emit = defineEmits(["update:value", "update:error"]);

async function fetchInstitutions(name = "") {
  state.isFetching = true;
  searchInstitutions.cancel();
  state.institutions = await fetchParams(`/api/institutions`, {
    page: 1,
    name,
  });
  state.isFetching = false;
}

const searchInstitutions = _debounce(fetchInstitutions, 350);

function updateInstitutions(newValue) {
  selected = newValue["@id"];
  searchInstitutions(newValue["name"]);
}

onMounted(() => {
  state.isFetching = true;
  fetchInstitutions();
});
</script>

<template>
  <SelectLabelBase
    v-model:value="selected"
    v-model:error="error"
    :label="label"
    placeholder="Select institution"
    :options="state.institutions"
    :loading="state.isFetching"
    :disabled="disabled"
    :filterable="false"
    @search="searchInstitutions"
  >
    <li
      class="underline text-blue-700 text-center cursor-pointer"
      @click="setPopupState({ popupName: 'showPopupInstitutionAdd', value: true })"
    >
      Add new institution
    </li>
  </SelectLabelBase>

  <InstitutionAdd v-if="getPopupState('showPopupInstitutionAdd')" @addInstitution="updateInstitutions" />
</template>
