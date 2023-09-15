<script setup lang="ts">
import { computed, ref } from "vue";
import { array, number, object, string } from "yup";

import Preloader from "@/components/Preloader.vue";
import Steps from "@/components/stepper/Steps.vue";
import useForm from "@/composables/useForm";
import useStepperValidation from "@/composables/useStepperValidation";
import type { AnyModel } from "@/entities/base";
import type { PositionCreditCard } from "@/entities/position-credit-card";
import PositionCreditCardService from "@/services/PositionCreditCard";
import { toaster } from "@/utils/toaster";

import Classification from "./CreditCardSteps/Classification.vue";
import Details from "./CreditCardSteps/Details.vue";
import GeneralInfo from "./CreditCardSteps/GeneralInfo.vue";
const emit = defineEmits(["close"]);
const { meta, errors, state, isSubmitting, store } = useForm(
  {
    name: "",
    accountHolder: undefined,
    institution: undefined,
    cardLimit: 0,
    cardBalance: 0,
    tags: [],
    notes: "",
  } as Partial<PositionCreditCard>,
  object<AnyModel<PositionCreditCard>>().shape({
    name: string().required().label("Name"),
    accountHolder: string().required().label("Account holder"),
    institution: string().required().label("Institution"),
    cardLimit: number()
      .transform((value) => (Number.isNaN(value) ? 0 : value))
      .positive()
      .required()
      .label("Card limit"),
    cardBalance: number()
      .transform((value) => (Number.isNaN(value) ? 0 : value))
      .positive()
      .required()
      .label("Card balance"),
    tags: array().label("Tags"),
    notes: string().label("Notes"),
  })
);
const step = ref(1);
const [name, accountHolder, institution, cardLimit, cardBalance, tags, notes] = state;
const generalInfo = computed({
  get: () => ({
    name: name.value as string,
    accountHolder: accountHolder.value as string,
    institution: institution.value as string,
  }),
  set: (value: Partial<PositionCreditCard>) => {
    if (value.name !== undefined) {
      name.value = value.name;
    }
    if (value.accountHolder !== undefined) {
      accountHolder.value = value.accountHolder;
    }
    if (value.institution !== undefined) {
      institution.value = value.institution;
    }
  },
});
const generalInfoErrors = computed(() => ({
  name: errors.value.name,
  accountHolder: errors.value.accountHolder,
  institution: errors.value.institution,
}));
const generalInfoMeta = computed(() => ({
  name: meta.value.name,
  accountHolder: meta.value.accountHolder,
  institution: meta.value.institution,
}));
const details = computed({
  get: () => ({
    cardLimit: cardLimit.value as number,
    cardBalance: cardBalance.value as number,
  }),
  set: (value: Partial<PositionCreditCard>) => {
    if (value.cardLimit !== undefined) {
      cardLimit.value = value.cardLimit;
    }
    if (value.cardBalance !== undefined) {
      cardBalance.value = value.cardBalance;
    }
  },
});
const detailsErrors = computed(() => ({
  cardLimit: errors.value.cardLimit,
  cardBalance: errors.value.cardBalance,
}));
const detailsMeta = computed(() => ({
  cardLimit: meta.value.cardLimit,
  cardBalance: meta.value.cardBalance,
}));
const classification = computed({
  get: () => ({
    tags: tags.value as string[],
    notes: notes.value as string,
  }),
  set: (value: Partial<PositionCreditCard>) => {
    if (value.tags !== undefined) {
      tags.value = value.tags;
    }
    if (value.notes !== undefined) {
      notes.value = value.notes;
    }
  },
});
const classificationErrors = computed(() => ({
  tags: errors.value.tags,
  notes: errors.value.notes,
}));
const classificationMeta = computed(() => ({
  tags: meta.value.tags,
  notes: meta.value.notes,
}));
const { validate, goBack } = useStepperValidation(
  [generalInfoMeta, detailsMeta, classificationMeta],
  [generalInfoErrors, detailsErrors, classificationErrors],
  step
);
const handleBack = () => {
  if (step.value > 1) {
    step.value--;
  }
};
const handleNext = async () => {
  if (!validate()) return;
  if (step.value < 3) {
    step.value++;
  } else if (step.value === 3) {
    try {
      const res = await store(new PositionCreditCardService());
      toaster.success("Position added successfully.");
      emit("close", res.id);
    } catch (e) {
      goBack();
    }
  }
};
</script>

<template>
  <div class="py-5 px-9 h-full relative">
    <form class="grid px-8 max-w-screen-md" @submit.prevent="handleNext">
      <!-- TODO: find proper clases for h1 tags -->
      <GeneralInfo v-show="step === 1" v-model:form="generalInfo" :errors="generalInfoErrors" :meta="generalInfoMeta" />
      <Details v-show="step === 2" v-model:form="details" :errors="detailsErrors" :meta="detailsMeta" />
      <Classification
        v-show="step === 3"
        v-model:form="classification"
        :errors="classificationErrors"
        :meta="classificationMeta"
      />
    </form>
    <div
      class="py-6 px-12 absolute bottom-0 left-0 right-0 border-t border-slate-300 flex justify-between items-center mt-4"
    >
      <Steps v-model="step" :steps="3" />
      <div>
        <button v-show="step > 1" type="button" class="btn btn-cancel mr-3" @click="handleBack">Back</button>
        <button type="button" class="btn btn-primary border-2 border-transparent" @click="handleNext">
          {{ step < 3 ? "Continue" : "Submit" }}
        </button>
      </div>
    </div>
  </div>
  <Preloader v-if="isSubmitting" />
</template>
