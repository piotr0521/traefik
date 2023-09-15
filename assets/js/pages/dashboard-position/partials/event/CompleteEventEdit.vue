<script setup>
import axios from "axios";
import _isEmpty from "lodash/isEmpty";
import { onMounted, reactive, ref, watch } from "vue";
import { useRoute } from "vue-router";

import ButtonClose from "@/components/icons/ButtonClose.vue";
import DatePickerLabelBase from "@/components/input/DatePickerLabelBase.vue";
import InputLabelPrefix from "@/components/input/InputLabelPrefix.vue";
import TextareaLabelBase from "@/components/input/TextareaLabelBase.vue";
import Preloader from "@/components/Preloader.vue";
import { usePopupStore } from "@/stores/popup";
import { fetchPosition } from "@/utils/position";
import { toaster } from "@/utils/toaster";

const emit = defineEmits(["event:edit", "closedPopup"]);
const props = defineProps({
  event: {
    type: Object,
    default: null,
  },
});
const route = useRoute();

const { setPopupState, getPopupState } = usePopupStore();
const isFetching = ref(false);
watch(
  () => props.event,
  (event) => {
    if (_isEmpty(event)) return;
    isFetching.value = true;
    axios
      .get(event["@id"])
      .then(({ data }) => {
        state.form = {
          date: data.date,
          position: data.position["@id"],
          transactions: [
            {
              amount: data.transactions[0]?.amount?.base ?? null,
              transaction: data.transactions[0]?.["@id"] ?? null,
            },
          ],
          notes: data.notes,
        };
      })
      .catch((error) => {
        toaster.error("Error fetching position event");
        closePopup();
      })
      .finally(() => {
        isFetching.value = false;
      });
  }
);
const state = reactive({
  form: {
    date: null,
    position: null,
    transactions: [{ amount: null }],
    notes: null,
  },
  errors: {
    date: null,
    "value.amount": null,
  },
});

function closePopup() {
  setPopupState({ popupName: "showPopupCompleteInvestmentEdit", value: false });
}

async function submit() {
  if (_isEmpty(props.event)) return;
  isFetching.value = true;
  state.errors = {
    date: null,
    "value.amount": null,
  };

  const positionEventData = {
    type: "COMPLETE",
    position: state.form.position,
    date: state.form.date,
    transactions: state.form.transactions,
    notes: state.form.notes,
  };

  try {
    let { data } = await axios.patch(`/api/position_events/${props.event.id}`, positionEventData, {
      headers: {
        "Content-Type": "application/merge-patch+json",
      },
    });
    state.form = {
      date: null,
      type: null,
      transactions: [{ amount: null }],
      notes: null,
    };
    emit("event:edit");
    closePopup();
    toaster.success("Complete event edited successfully!");
  } catch (errors) {
    errors.response.data.violations.forEach((error) => {
      state.errors[error.propertyPath] = error.message;
    });
  } finally {
    isFetching.value = false;
  }
}

onMounted(async () => {
  state.form.position = await fetchPosition(route);
});
</script>

<template>
  <div
    v-if="getPopupState('showPopupCompleteInvestmentEdit')"
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
          name="groshy_complete_event_edit_date"
        />

        <InputLabelPrefix
          v-model:value="state.form.transactions[0].amount"
          label="Final distribution"
          name="groshy_complete_event_edit_transaction"
        />

        <TextareaLabelBase v-model:value="state.form.notes" label="Notes" name="groshy_complete_event_edit_notes" />

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
