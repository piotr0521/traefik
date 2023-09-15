<script setup lang="ts">
import { AxiosError } from "axios";
import { storeToRefs } from "pinia";
import { computed, ref, watch } from "vue";
import { array, boolean, number, object, string } from "yup";

import Preloader from "@/components/Preloader.vue";
import Steps from "@/components/stepper/Steps.vue";
import useForm from "@/composables/useForm";
import useStepperValidation from "@/composables/useStepperValidation";
import type { AnyModel } from "@/entities/base";
import type { PositionInvestment } from "@/entities/position-investment";
import PositionInvestmentService from "@/services/PositionInvestmentService";
import { useAssetTypeStore } from "@/stores/assetType";
import { useAssetTypeConfigStore } from "@/stores/assetTypeConfig";
import { toaster } from "@/utils/toaster";

import Classification from "./InvestmentPosition/Classification.vue";
import GeneralInfo from "./InvestmentPosition/GeneralInfo.vue";
import Investment from "./InvestmentPosition/Investment.vue";
import Sponsor from "./InvestmentPosition/Sponsor.vue";
const emit = defineEmits(["close"]);
const { selected } = storeToRefs(useAssetTypeStore());
const { meta, errors, state, isSubmitting, store } = useForm(
  {
    isDirect: false,
    sponsor: null,
    asset: null,
    capitalCommitment: 0,
    name: "",
    accountHolder: null,
    institution: null,
    tags: [],
    notes: "",
  } as Partial<PositionInvestment>,
  object<AnyModel<PositionInvestment>>().shape({
    isDirect: boolean().required().label("Invested directly with sponsor"),
    sponsor: string().required().label("Sponsor"),
    asset: string().required().label("Investment"),
    capitalCommitment: number()
      .transform((value) => (Number.isNaN(value) ? 0 : value))
      .positive()
      .required()
      .label("Caoital Commitment"),
    name: string().required().label("Name"),
    accountHolder: string().required().label("Account holder"),
    institution: string()
      .required()
      .when("isDirect", {
        is: true,
        then: (schema) => schema.notRequired(),
      })
      .label("Institution"),
    tags: array().label("Tags"),
    notes: string().label("Notes"),
  })
);
const [isDirect, sponsor, asset, capitalCommitment, name, accountHolder, institution, tags, notes] = state;
const step = ref(1);
const sponsorInfo = computed({
  get: () => ({
    isDirect: isDirect.value as boolean,
    sponsor: sponsor.value as string | null,
  }),
  set: (value: Partial<PositionInvestment>) => {
    if (value.isDirect !== undefined) {
      isDirect.value = value.isDirect;
    }
    if (value.sponsor !== undefined) {
      sponsor.value = value.sponsor;
    }
  },
});
const sponsorInfoErrors = computed(() => ({
  isDirect: errors.value.isDirect,
  sponsor: errors.value.sponsor,
}));
const sponsorInfoMeta = computed(() => ({
  isDirect: meta.value.isDirect,
  sponsor: meta.value.sponsor,
}));
const investmentInfo = computed({
  get: () => ({
    asset: asset.value as string | null,
    capitalCommitment: capitalCommitment.value as number,
  }),
  set: (value: Partial<PositionInvestment>) => {
    if (value.asset !== undefined) {
      asset.value = value.asset;
    }
    if (value.capitalCommitment !== undefined) {
      capitalCommitment.value = value.capitalCommitment;
    }
  },
});
const investmentInfoErrors = computed(() => ({
  asset: errors.value.asset,
  capitalCommitment: errors.value.capitalCommitment,
}));
const investmentInfoMeta = computed(() => ({
  asset: meta.value.asset,
  capitalCommitment: meta.value.capitalCommitment,
}));
const generalInfo = computed({
  get: () => ({
    name: name.value as string,
    accountHolder: accountHolder.value as string | null,
    institution: institution.value as string | null,
  }),
  set: (value: Partial<PositionInvestment>) => {
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
const classification = computed({
  get: () => ({
    tags: tags.value as string[],
    notes: notes.value as string,
  }),
  set: (value: Partial<PositionInvestment>) => {
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
  [sponsorInfoMeta, investmentInfoMeta, generalInfoMeta, classificationMeta],
  [sponsorInfoErrors, investmentInfoErrors, generalInfoErrors, classificationErrors],
  step
);
watch(
  () => capitalCommitment,
  () => {
    if (capitalCommitment.value == "") {
      capitalCommitment.value = 0;
    }
  }
);
watch(
  () => isDirect.value,
  (isDirect) => {
    if (isDirect) {
      institution.value = "";
    }
  }
);
const handleBack = () => {
  if (step.value > 1) {
    step.value--;
  }
};
const handleNext = async () => {
  if (!validate()) return;
  if (step.value < 4) {
    step.value++;
  } else if (step.value === 4) {
    try {
      const url = useAssetTypeConfigStore().getBySlug(selected.value)?.positionUrl || "/";
      const [_, path] = url.split("/api");
      const res = await store(new PositionInvestmentService(path));
      toaster.success("Position added successfully.");
      emit("close", res.id);
    } catch (e) {
      if (e instanceof AxiosError) {
        if (e.response?.status === 422) {
          goBack();
        }
      }
    }
  }
};
</script>
<template>
  <div class="py-5 px-9 h-full relative">
    <form class="grid px-8 max-w-screen-md">
      <Sponsor v-show="step === 1" v-model:form="sponsorInfo" :errors="sponsorInfoErrors" :meta="sponsorInfoMeta" />
      <Investment
        v-show="step === 2"
        v-model:form="investmentInfo"
        :sponsor="sponsor"
        :errors="investmentInfoErrors"
        :meta="investmentInfoMeta"
      />
      <GeneralInfo
        v-show="step === 3"
        v-model:form="generalInfo"
        :errors="generalInfoErrors"
        :meta="generalInfoMeta"
        :is-direct="isDirect"
      />
      <Classification
        v-show="step === 4"
        v-model:form="classification"
        :errors="classificationErrors"
        :meta="classificationMeta"
      />
    </form>
    <div
      class="py-6 px-12 absolute bottom-0 left-0 right-0 border-t border-slate-300 flex justify-between items-center mt-4"
    >
      <Steps v-model="step" :steps="4" />
      <div>
        <button v-show="step > 1" type="button" class="btn btn-cancel mr-3" @click="handleBack">Back</button>
        <button type="button" class="btn btn-primary border-2 border-transparent" @click="handleNext">
          {{ step < 4 ? "Continue" : "Submit" }}
        </button>
      </div>
    </div>
    <Preloader v-if="isSubmitting" />
    <!-- <AddSponsorPopup v-if="getPopupState('showPopupSponsorAdd')" @update:sponsors="fetchSponsors" /> -->
    <!-- <InvestmentAdd v-if="getPopupState('showPopupInvestmentAdd')" :sponsor="sponsor" @update:investments="fetchAssets" /> -->
  </div>
</template>
