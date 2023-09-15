<script setup>
import axios from "axios";
import { storeToRefs } from "pinia";
import { computed, onMounted, reactive, ref, watch } from "vue";
import { useRoute } from "vue-router";

import ButtonClose from "@/components/icons/ButtonClose.vue";
import DatePickerLabelBase from "@/components/input/DatePickerLabelBase.vue";
import InputLabelPrefix from "@/components/input/InputLabelPrefix.vue";
import SelectLabelBase from "@/components/input/SelectLabelBase.vue";
import TextareaLabelBase from "@/components/input/TextareaLabelBase.vue";
import Preloader from "@/components/Preloader.vue";
import { usePopupStore } from "@/stores/popup";
import { usePositionEventTypeStore } from "@/stores/positionEventType";
import { fetchPosition } from "@/utils/position";
import { toaster } from "@/utils/toaster";

const props = defineProps({
  positionEventTypesConfig: {
    type: Array,
  },
});
const emit = defineEmits(["eventAdd"]);

const route = useRoute();

const { setPopupState, getPopupState } = usePopupStore();
const { config } = storeToRefs(usePositionEventTypeStore());
const isFetching = ref(false);

// cash distribution : transaction amoun +
//cash contribution: transaction amount -
// reinvest : transaction amount + -

const state = reactive({
  form: {
    date: null,
    type: null,
    position: null,
    transactions: [{ amount: null }, { amount: null }],
    value: {
      amount: null,
    },
    notes: null,
  },
  errors: {
    date: null,
    "value.amount": null,
    value: null,
  },
});
const label = ref("");
const inputAmount = reactive({
  amout: true,
  reinvest: false,
});

function closePopup() {
  setPopupState({ popupName: "showPopupEventAdd", value: false });
}
watch(
  () => state.form.type,
  (val) => {
    switch (state.form.type) {
      case "DISTRIBUTION":
        inputAmount.amout = true;
        inputAmount.reinvest = false;
        label.value = "Distribution amount";
        break;
      case "CONTRIBUTION":
        inputAmount.amout = true;
        inputAmount.reinvest = false;
        label.value = "Contribution amount";
        break;
      case "REINVEST":
        inputAmount.amout = true;
        inputAmount.reinvest = true;
        label.value = "Distribution amount";
        break;
      case ("VALUE_UPDATE", "BALANCE_UPDATE"):
        inputAmount.amout = false;
        inputAmount.reinvest = false;
        label.value = "";
        break;
    }
  }
);
watch(
  () => getPopupState("showPopupEventAdd"),
  (val) => {
    if (val) {
      state.form.type = positionEventTypes.value[0].code;
    }
  }
);
const positionEventTypes = computed(() => {
  return props.positionEventTypesConfig.map((item) => {
    if (config.value[item]) {
      return { label: config.value[item], code: item };
    }
  });
});

let positionEventData;

async function submit() {
  isFetching.value = true;
  state.errors = {
    date: null,
    "value.amount": null,
    value: null,
  };

  positionEventData = {
    date: state.form.date,
    type: state.form.type,
    position: state.form.position,
    //Map out the value if the amount is null
    ...(state.form.value.amount
      ? {
          value: { amount: state.form.value.amount },
        }
      : {}),
    notes: state.form.notes,
  };

  switch (state.form.type) {
    case "DISTRIBUTION":
      positionEventData.transactions = [{ amount: state.form.transactions[0].amount }];
      break;
    case "CONTRIBUTION":
      positionEventData.transactions = [{ amount: -state.form.transactions[0].amount }];
      break;
    case "REINVEST":
      positionEventData.transactions = [
        { amount: state.form.transactions[0].amount },
        { amount: -state.form.transactions[1].amount },
      ];
      break;
  }

  try {
    let { data } = await axios.post(`/api/position_events`, positionEventData);

    state.form = {
      date: null,
      type: null,
      position: state.form.position,
      transactions: [{ amount: null }, { amount: null }],
      value: { amount: null },
      notes: null,
    };

    emit("eventAdd");
    closePopup();

    toaster.success("New event added");
  } catch (errors) {
    console.log(errors);
    errors.response.data.violations.forEach((error) => {
      state.errors[error.propertyPath] = error.message;
    });
  } finally {
    isFetching.value = false;
  }
}
//Calculate the error for the value amount
const valueErrors = computed(() => {
  return state.errors["value.amount"] || state.errors.value;
});
onMounted(async () => {
  state.form.position = await fetchPosition(route);
});
</script>

<template>
  <div
    v-if="getPopupState('showPopupEventAdd')"
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

      <h2 class="text-indigo-900 font-normal text-xl mb-5">Add Event</h2>

      <!-- start content -->
      <form class="grid gap-5" @submit.prevent="submit">
        <DatePickerLabelBase
          v-model:value="state.form.date"
          v-model:error="state.errors.date"
          label="Event date"
          name="groshy_event_add_date"
        />

        <SelectLabelBase
          v-if="positionEventTypes.length > 1"
          v-model:value="state.form.type"
          v-model:error="state.errors.type"
          label="Event type"
          placeholder="Select event type"
          :options="positionEventTypes"
        />

        <!-- if diferent types -->
        <InputLabelPrefix
          v-if="inputAmount.amout && state.form.type?.length"
          v-model:value="state.form.transactions[0].amount"
          :label="label"
          name="groshy_event_add_value"
        />
        <!-- end if diferent type -->

        <!-- if diferent types and reinvest -->
        <InputLabelPrefix
          v-if="inputAmount.reinvest && state.form.type?.length"
          v-model:value="state.form.transactions[1].amount"
          label="Contribution amount (reinvestment)"
          name="groshy_event_add_value"
        />
        <!-- end if diferent type and reinvest -->

        <InputLabelPrefix
          v-if="state.form.type?.length"
          v-model:value="state.form.value.amount"
          v-model:error="valueErrors"
          label="Update asset value"
          name="groshy_event_add_value"
        />

        <TextareaLabelBase
          v-if="state.form.type?.length"
          v-model:value="state.form.notes"
          label="Notes"
          name="groshy_event_add_notes"
        />

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
