<script setup>
import axios from "axios";
import { computed, onMounted, reactive, ref, watch } from "vue";
import { useRoute } from "vue-router";

import ButtonClose from "@/components/icons/ButtonClose.vue";
import DatePickerLabelBase from "@/components/input/DatePickerLabelBase.vue";
import InputLabelPrefix from "@/components/input/InputLabelPrefix.vue";
import SelectLabelBase from "@/components/input/SelectLabelBase.vue";
import TextareaLabelBase from "@/components/input/TextareaLabelBase.vue";
import Preloader from "@/components/Preloader.vue";
import { usePopupStore } from "@/stores/popup";
import { fetch, fetchPosition } from "@/utils/position";
import { toaster } from "@/utils/toaster";

const props = defineProps({
  transactionTypesConfig: {
    type: Array,
  },
  transactionEdit: {
    type: Object,
  },
});
const route = useRoute();
const { setPopupState, getPopupState } = usePopupStore();

const state = reactive({
  form: {
    transactionDate: null,
    amount: null,
    type: null,
    // TODO: check this input
    // isCompleted: props.transactionEdit?.,
    // notes: null,
  },
  errors: {
    transactionDate: null,
    amount: null,
    quantity: null,
    type: null,
  },
  transactionTypes: [],
});

const isFetching = ref(true);
const emit = defineEmits(["transactionEdit"]);

// props.transactionEdit.transactionDate
// amount: props.transactionEdit?.amount.base,
// type: null,

watch(
  () => props.transactionEdit,
  () => {
    state.form.amount = props.transactionEdit.amount.base;
    state.form.transactionDate = props.transactionEdit.transactionDate;
    state.form.type = props.transactionEdit.type["@id"];
  }
);

function closePopup() {
  setPopupState({ popupName: "showPopupTransactionEdit", value: false });
}

async function submit() {
  isFetching.value = true;

  state.errors = {};

  try {
    let { data } = await axios.patch(
      `/api/transactions/${props.transactionEdit.id}`,
      {
        transactionDate: state.form.transactionDate,
        amount: state.form.amount,
        type: state.form.type,
        // isCompleted: state.form.isCompleted,
        // notes: state.form.notes,
      },
      { headers: { "Content-Type": "application/merge-patch+json" } }
    );

    state.form = {
      transactionDate: null,
      amount: null,
      type: null,
      // isCompleted: false,
      // notes: null,
    };
    emit("transactionEdit");
    closePopup();
    // TODO: check text
    toaster.success("New transaction updated");
  } catch (errors) {
    console.log(errors);
    errors.response.data.violations.forEach((error) => {
      state.errors[error.propertyPath] = error.message;
    });
  } finally {
    isFetching.value = false;
  }
}

async function fetchTransactionTypes() {
  state.transactionTypes = await fetch(`/api/transaction_types`);
  isFetching.value = false;
}

const transactionTypes = computed(() => {
  return state.transactionTypes.filter((item) => props.transactionTypesConfig.includes(item.label.toUpperCase()));
});

onMounted(async () => {
  fetchTransactionTypes();
  state.form.position = await fetchPosition(route);
});
</script>

<template>
  <div
    v-if="getPopupState('showPopupTransactionEdit')"
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

      <h2 class="text-indigo-900 font-normal text-xl mb-5">Edit Transaction</h2>

      <!-- start content -->
      <form class="grid gap-5" @submit.prevent="submit">
        <DatePickerLabelBase
          v-model:value="state.form.transactionDate"
          v-model:error="state.errors.transactionDate"
          label="Event date"
          name="groshy_transaction_add_transactionDate"
        />

        <InputLabelPrefix
          v-model:value="state.form.amount"
          v-model:error="state.errors.amount"
          label="Amount"
          name="groshy_sponsor_add_quantity"
        />

        <!-- <CheckboxLabelBase
          label="Reinvested"
          name="groshy_sponsor_add_isReinvested"
          v-model:value="state.form.isReinvested"
        /> -->

        <SelectLabelBase
          v-model:value="state.form.type"
          v-model:error="state.errors.type"
          label="Event type"
          placeholder="Select transaction type"
          :options="transactionTypes"
        />

        <TextareaLabelBase v-model:value="state.form.notes" label="Notes" name="groshy_cash_add_notes" />

        <!-- <CheckboxLabelBase
          label="Investment completed"
          name="groshy_sponsor_add_isCompleted"
          v-model:value="state.form.isCompleted"
        /> -->

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
