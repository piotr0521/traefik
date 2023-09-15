<script setup lang="ts">
import { computed, ref } from "vue";
import { array, number, object, string } from "yup";

import Preloader from "@/components/Preloader.vue";
import Steps from "@/components/stepper/Steps.vue";
import useForm from "@/composables/useForm";
import useStepperValidation from "@/composables/useStepperValidation";
import type { AnyModel } from "@/entities/base";
import type { PositionCash } from "@/entities/position-cash";
import PositionCashService from "@/services/PositionCash";
import { toaster } from "@/utils/toaster";

import Classification from "./CashSteps/Classification.vue";
import Details from "./CashSteps/Details.vue";
import GeneralInfo from "./CashSteps/GeneralInfo.vue";
const emit = defineEmits(["close"]);

const { meta, errors, state, isSubmitting, store } = useForm(
  {
    name: "",
    accountHolder: undefined,
    accountType: undefined,
    institution: undefined,
    yield: 0,
    currentValue: 0,
    tags: [],
    notes: "",
  } as Partial<PositionCash>,
  object<AnyModel<PositionCash>>().shape({
    name: string().required().label("Name"),
    accountHolder: string().required().label("Account holder"),
    accountType: string().required().label("Account type"),
    institution: string().required().label("Institution"),
    yield: number()
      .transform((value) => (Number.isNaN(value) ? 0 : value))
      .min(0)
      .required()
      .label("Yield"),
    currentValue: number()
      .transform((value) => (Number.isNaN(value) ? 0 : value))
      .positive()
      .required()
      .label("Current balance"),
    tags: array().label("Tags"),
    notes: string().label("Notes"),
  })
);
const step = ref(1);
const [name, accountHolder, accountType, institution, yieldV, currentValue, tags, notes] = state;
const generalInfo = computed({
  get: () => ({
    name: name.value as string,
    accountHolder: accountHolder.value as string,
    accountType: accountType.value as string,
    institution: institution.value as string,
  }),
  set: (value: Partial<PositionCash>) => {
    if (value.name !== undefined) {
      name.value = value.name;
    }
    if (value.accountHolder !== undefined) {
      accountHolder.value = value.accountHolder;
    }
    if (value.accountType !== undefined) {
      accountType.value = value.accountType;
    }
    if (value.institution !== undefined) {
      institution.value = value.institution;
    }
  },
});
const generalInfoErrors = computed(() => ({
  name: errors.value.name,
  accountHolder: errors.value.accountHolder,
  accountType: errors.value.accountType,
  institution: errors.value.institution,
}));
const generalInfoMeta = computed(() => ({
  name: meta.value.name,
  accountHolder: meta.value.accountHolder,
  accountType: meta.value.accountType,
  institution: meta.value.institution,
}));
const details = computed({
  get: () => ({
    yield: yieldV.value as number,
    currentValue: currentValue.value as number,
  }),
  set: (value: Partial<PositionCash>) => {
    if (value.yield !== undefined) {
      yieldV.value = value.yield;
    }
    if (value.currentValue !== undefined) {
      currentValue.value = value.currentValue;
    }
  },
});
const detailsErrors = computed(() => ({
  yield: errors.value.yield,
  currentValue: errors.value.currentValue,
}));
const detailsMeta = computed(() => ({
  yield: meta.value.yield,
  currentValue: meta.value.currentValue,
}));
const classification = computed({
  get: () => ({
    tags: tags.value as string[],
    notes: notes.value as string,
  }),
  set: (value: Partial<PositionCash>) => {
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
      const res = await store(new PositionCashService());
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
