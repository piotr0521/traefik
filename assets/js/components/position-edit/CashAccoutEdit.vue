<script setup>
import { onMounted, reactive, watch } from "vue";
import { useRoute } from "vue-router";

import ButtonClose from "@/components/icons/ButtonClose.vue";
import InputLabelBase from "@/components/input/InputLabelBase.vue";
import SelectTags from "@/components/input/SelectTags.vue";
import TextareaLabelBase from "@/components/input/TextareaLabelBase.vue";
import Preloader from "@/components/Preloader.vue";
import { usePopupStore } from "@/stores/popup";
import { update } from "@/utils/position";

const props = defineProps({
  cashAccountEdit: {
    type: Object,
  },
});
const route = useRoute();
const { getPopupState, setPopupState } = usePopupStore();

const state = reactive({
  form: {
    name: null,
    tags: [],
    notes: null,
  },
  errors: {},
  isFetching: true,
});

const emit = defineEmits(["cashAccountEdit"]);

watch(
  () => props.cashAccountEdit,
  (cashAccountEdit) => {
    if (cashAccountEdit) {
      state.form.name = cashAccountEdit.name;
      state.form.tags = cashAccountEdit?.tags.map((item) => item["@id"]);
      state.form.notes = cashAccountEdit.notes;
    }
  }
);

function closePopup() {
  setPopupState({ popupName: "showPopupCashAccountEdit", value: false });
}

async function submit() {
  update(state, props.cashAccountEdit["@id"], null, "Cash account updated").then(() => {
    if (Object.keys(state.errors).length > 0) {
      return;
    }
    emit("cashAccountEdit");
    closePopup();
  });
}

onMounted(() => {
  state.isFetching = false;
});
</script>

<template>
  <div
    v-if="getPopupState('showPopupCashAccountEdit')"
    class="fixed top-0 left-0 right-0 bottom-0 min-h-full min-w-full z-10 flex items-center justify-center"
  >
    <!-- start overlay -->
    <div
      class="absolute top-0 left-0 right-0 bottom-0 min-h-full min-w-full z-0"
      style="background: rgba(49, 46, 129, 0.7)"
      @click="closePopup"
    ></div>
    <!-- end overlay -->

    <!-- start popup -->
    <div class="bg-white p-12 relative z-10 max-w-screen-md w-full rounded-md">
      <!-- start button close -->
      <ButtonClose @click="closePopup" />
      <!-- end butotn close -->

      <h2 class="text-indigo-900 font-normal text-xl mb-5">Edit Account</h2>

      <!-- start content -->
      <form class="grid gap-5" @submit.prevent="submit">
        <InputLabelBase
          v-model:value="state.form.name"
          v-model:error="state.errors.name"
          label="Name *"
          name="groshy_cash_add_name"
        />

        <SelectTags v-model:value="state.form.tags" v-model:error="state.errors.tags" />

        <TextareaLabelBase v-model:value="state.form.notes" label="Notes" name="groshy_cash_add_notes" />

        <div class="flex gap-8 justify-center mt-4">
          <button type="button" class="btn btn-cancel" @click="closePopup">Cancel</button>

          <button type="submit" class="btn btn-primary">Submit</button>
        </div>
      </form>
      <!-- end content -->
      <Preloader v-if="state.isFetching" />
    </div>
    <!-- end popup -->
  </div>
</template>
