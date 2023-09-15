<script setup>
import axios from "axios";
import { reactive, ref } from "vue";
import { useRoute } from "vue-router";

import ButtonClose from "@/components/icons/ButtonClose.vue";
import DatePickerLabelBase from "@/components/input/DatePickerLabelBase.vue";
import InputLabelPrefix from "@/components/input/InputLabelPrefix.vue";
import TextareaLabelBase from "@/components/input/TextareaLabelBase.vue";
import Preloader from "@/components/Preloader.vue";
import { usePopupStore } from "@/stores/popup";
import { toaster } from "@/utils/toaster";

const emit = defineEmits(["completeInvestmentAdd"]);
const props = defineProps({
  position: {
    type: Object,
  },
});
const route = useRoute();

const { setPopupState, getPopupState } = usePopupStore();
const isFetching = ref(false);

const state = reactive({
  form: {
    date: null,
    transactions: [{ amount: null }],
    notes: null,
  },
  errors: {
    date: null,
  },
});

function closePopup() {
  setPopupState({ popupName: "showPopupCompleteInvestmentAdd", value: false });
}

async function submit() {
  isFetching.value = true;
  state.errors = {
    date: null,
  };
  const positionEventData = {
    type: "COMPLETE",
    position: props.position["@id"],
    date: state.form.date,
    //Map out the transactions if the amount is null
    ...(state.form.transactions[0].amount
      ? {
          transactions: [
            {
              amount: state.form.transactions[0].amount,
            },
          ],
        }
      : {}),
    notes: state.form.notes,
  };
  try {
    let { data } = await axios.post(`/api/position_events`, positionEventData);
    state.form = {
      date: null,
      transactions: [{ amount: null }],
      notes: null,
    };
    emit("completeInvestmentAdd");
    closePopup();

    toaster.success("New completed event added");
  } catch (errors) {
    errors.response.data.violations.forEach((error) => {
      state.errors[error.propertyPath] = error.message;
    });
  } finally {
    isFetching.value = false;
  }
}
</script>

<template>
  <div
    v-if="getPopupState('showPopupCompleteInvestmentAdd')"
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

      <h2 class="text-indigo-900 font-normal text-xl mb-5">Add Completed Event</h2>

      <!-- start content -->
      <form class="grid gap-5" @submit.prevent="submit">
        <DatePickerLabelBase
          v-model:value="state.form.date"
          v-model:error="state.errors.date"
          label="Event date"
          name="groshy_complete_event_add_date"
        />

        <InputLabelPrefix
          v-model:value="state.form.transactions[0].amount"
          label="Final distribution"
          name="groshy_complete_event_add_transaction"
        />

        <TextareaLabelBase v-model:value="state.form.notes" label="Notes" name="groshy_complete_event_add_notes" />

        <div class="flex gap-8 justify-center mt-4">
          <button type="button" class="btn btn-cancel" @click="closePopup">Cancel</button>

          <button type="submit" class="btn btn-primary">Submit</button>
        </div>
      </form>
      <!-- end content -->
      <Preloader v-if="isFetching" />
    </div>
    <!-- end popup -->
  </div>
</template>
