<script setup>
import axios from "axios";
import { storeToRefs } from "pinia";
import { computed, reactive, ref, watch } from "vue";

import ButtonClose from "@/components/icons/ButtonClose.vue";
import DatePickerLabelBase from "@/components/input/DatePickerLabelBase.vue";
import InputLabelPrefix from "@/components/input/InputLabelPrefix.vue";
import SelectLabelBase from "@/components/input/SelectLabelBase.vue";
import TextareaLabelBase from "@/components/input/TextareaLabelBase.vue";
import Preloader from "@/components/Preloader.vue";
import { usePopupStore } from "@/stores/popup";
import { usePositionEventTypeStore } from "@/stores/positionEventType";
import { toaster } from "@/utils/toaster";

const props = defineProps({
  event: {
    type: Object,
    value: null,
  },
  position: {
    type: Object,
    value: null,
  },
  positionEventTypesConfig: {
    type: Array,
  },
});

const emit = defineEmits(["event:edit"]);
const { setPopupState, getPopupState } = usePopupStore();
const { config } = storeToRefs(usePositionEventTypeStore());
const isFetching = ref(false);

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
  },
});
const label = ref("");
watch(
  () => props.event,
  (value) => {
    if (value === null) return;
    isFetching.value = true;
    axios(value["@id"])
      .then(({ data }) => {
        state.form = {
          date: data.date,
          type: data.type,
          position: data.position["@id"],
          value: {
            amount: data.value?.amount?.base ?? null,
          },
          transactions: data.transactions?.map((transaction) => ({
            amount: transaction.amount?.base ?? null,
            transaction: transaction["@id"],
          })),
          notes: data.notes,
        };
        switch (data.type) {
          case "DISTRIBUTION":
            label.value = "Distribution amount";
            inputAmount.amout = true;
            inputAmount.reinvest = false;
            break;
          case "CONTRIBUTION":
            label.value = "Contribution amount";
            inputAmount.amout = true;
            inputAmount.reinvest = false;
            break;
          case "REINVEST":
            label.value = "Distribution amount";
            inputAmount.amout = true;
            inputAmount.reinvest = true;
            break;
          case ("VALUE_UPDATE", "BALANCE_UPDATE"):
            label.value = "";
            inputAmount.amout = false;
            inputAmount.reinvest = false;
            break;
        }
      })
      .catch(() => {
        toaster.error("An error has occurred while fetching the position event.");
        closePopup();
      })
      .finally(() => {
        isFetching.value = false;
      });
  }
);
const inputAmount = reactive({
  amout: false,
  reinvest: false,
});

function closePopup() {
  setPopupState({ popupName: "showPopupEventEdit", value: false });
}

const positionEventTypes = computed(() => {
  return props.positionEventTypesConfig.map((item) => {
    if (config.value[item]) {
      return { label: config.value[item], code: item };
    }
  });
});

async function update() {
  isFetching.value = true;
  state.errors = {
    date: null,
    "value.amount": null,
  };

  const positionEventData = {
    date: state.form.date,
    type: state.form.type,
    position: state.form.position,
    ...(state?.form?.value?.amount
      ? {
          value: {
            amount: state.form.value.amount,
          },
        }
      : {}),
    transactions: state.form.transactions,
    notes: state.form.notes,
  };
  try {
    let { data } = await axios.patch(`/api/position_events/${props.event.id}`, positionEventData, {
      headers: { "Content-Type": "application/merge-patch+json" },
    });

    state.form = {
      date: null,
      type: null,
      position: null,
      transactions: [{ amount: null }, { amount: null }],
      value: { amount: null },
      notes: null,
    };

    emit("event:edit");
    closePopup();

    toaster.success("Event edited successfully");
  } catch (errors) {
    console.log(errors);
    errors.response.data.violations.forEach((error) => {
      state.errors[error.propertyPath] = error.message;
    });
  } finally {
    isFetching.value = false;
  }
}
const parseAmount = (amount) => {
  return Math.abs(amount).toLocaleString("en-US", {
    minimumFractionDigits: 2,
  });
};
const firstTransaction = computed(() => {
  return parseAmount(state.form.transactions[0]?.amount);
});
const secondTransaction = computed(() => {
  return parseAmount(state.form.transactions[1]?.amount);
});
</script>

<template>
  <div
    v-if="getPopupState('showPopupEventEdit')"
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

      <h2 class="text-indigo-900 font-normal text-xl mb-5">Edit Event</h2>

      <!-- start content -->
      <form class="grid gap-5" @submit.prevent="update">
        <DatePickerLabelBase
          v-model:value="state.form.date"
          v-model:error="state.errors.date"
          label="Event date"
          name="groshy_event_edit_date"
        />

        <SelectLabelBase
          v-if="positionEventTypes.length > 1"
          v-model:value="state.form.type"
          v-model:error="state.errors.type"
          label="Event type"
          placeholder="Select event type"
          :options="positionEventTypes"
          disabled
        />

        <!-- if diferent types -->
        <InputLabelPrefix
          v-if="inputAmount.amout && state.form.type?.length"
          v-model:value="firstTransaction"
          :label="label"
          name="groshy_event_edit_value"
          readonly
        />
        <!-- end if diferent type -->

        <!-- if diferent types and reinvest -->
        <InputLabelPrefix
          v-if="inputAmount.reinvest && state.form.type?.length"
          v-model:value="secondTransaction"
          label="Contribution amount (reinvestment)"
          name="groshy_event_edit_value"
          readonly
        />
        <!-- end if diferent type and reinvest -->

        <InputLabelPrefix
          v-if="state.form.type?.length"
          v-model:value="state.form.value.amount"
          v-model:error="state.errors['value.amount']"
          label="Update asset value"
          name="groshy_event_edit_value"
        />

        <TextareaLabelBase
          v-if="state.form.type?.length"
          v-model:value="state.form.notes"
          label="Notes"
          name="groshy_event_edit_notes"
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
