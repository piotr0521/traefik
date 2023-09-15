<script setup>
import axios from "axios";
import { reactive, ref } from "vue";
import { useRoute } from "vue-router";

import ButtonClose from "@/components/icons/ButtonClose.vue";
import CheckboxLabelBase from "@/components/input/CheckboxLabelBase.vue";
import DatePickerLabelBase from "@/components/input/DatePickerLabelBase.vue";
import InputLabelPrefix from "@/components/input/InputLabelPrefix.vue";
import TextareaLabelBase from "@/components/input/TextareaLabelBase.vue";
import Preloader from "@/components/Preloader.vue";
import { usePopupStore } from "@/stores/popup";
import { toaster } from "@/utils/toaster";

const route = useRoute();
const { setPopupState, getPopupState } = usePopupStore();

const state = reactive({
  form: {
    transactionDate: null,
    amount: null,
    quantity: null,
    isReinvested: true,
    isCompleted: false,
    notes: null,
  },
  errors: {
    transactionDate: null,
    amount: null,
    quantity: null,
  },
});

const isFetching = ref(false);
const emit = defineEmits(["transactionAdd"]);

function closePopup() {
  setPopupState({ popupName: "showPopupTransactionAdd", value: false });
}

async function submit() {
  isFetching.value = true;

  state.errors = {};

  try {
    let { data } = await axios.post(`/api/transactions`, {
      transactionDate: state.form.transactionDate,
      amount: state.form.amount,
      quantity: state.form.quantity,
      isReinvested: state.form.isReinvested,
      isCompleted: state.form.isCompleted,
      notes: state.form.notes,
    });

    state.form = {
      transactionDate: null,
      quantity: null,
      isReinvested: true,
      isCompleted: false,
      notes: null,
    };
    emit("transactionAdd");
    closePopup();
    toaster.success("New transaction added");
  } catch (errors) {
    console.log(errors);
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
    v-if="getPopupState('showPopupTransactionAdd')"
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

      <h2 class="text-indigo-900 font-normal text-xl mb-5">Add Transaction</h2>

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

        <CheckboxLabelBase
          v-model:value="state.form.isReinvested"
          label="Reinvested"
          name="groshy_sponsor_add_isReinvested"
        />

        <TextareaLabelBase v-model:value="state.form.notes" label="Notes" name="groshy_cash_add_notes" />

        <CheckboxLabelBase
          v-model:value="state.form.isCompleted"
          label="Investment completed"
          name="groshy_sponsor_add_isCompleted"
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
