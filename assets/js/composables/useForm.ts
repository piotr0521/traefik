import { useForm as validateForm } from "vee-validate";
import { ref, watch } from "vue";
import type { ObjectSchema } from "yup";

import type { AbstractModel, AnyModel } from "@/entities/base";
import type ModelService from "@/services/ModelService";
const clean = <T extends Record<string, any>>(obj: T) => {
  const result = { ...obj };
  Object.keys(result).forEach((key) => {
    if (result[key] === undefined || result[key] === null || result[key] === "") {
      delete result[key];
    }
  });
  return result;
};
export default function useForm<M extends AbstractModel>(model: Partial<M>, schema?: ObjectSchema<any, AnyModel<M>>) {
  const { errors, useFieldModel, handleSubmit, isSubmitting, resetForm } = validateForm({
    initialValues: model,
    validateOnMount: false,
    validationSchema: schema,
  });
  const meta = ref(
    Object.keys(model).reduce((acc, key) => ({ ...acc, [key]: { touched: false } }), {}) as Record<
      keyof typeof model,
      { touched: boolean }
    >
  );
  const keys = [...Object.keys(model)] as (keyof typeof model)[];
  const state = useFieldModel(keys);
  keys.forEach((key, index) => {
    watch(
      () => state[index].value,
      (value) => {
        if (value !== model[key]) {
          meta.value[key].touched = true;
        }
      }
    );
  });
  const actions = {
    setTouched(value = true) {
      keys.forEach((key) => {
        meta.value[key].touched = value;
      });
    },
    store: async (service: ModelService<M>, resetForm = true) => {
      actions.setTouched();
      const res = handleSubmit(async (values, { resetForm: reset, setFieldError }) => {
        try {
          const { id, ...rest } = values;
          const response = await service.store(clean(rest as Partial<M>));
          resetForm && reset();
          return response;
        } catch (error: any) {
          error.response.data.violations.forEach(
            (error: { message: string; propertyPath: keyof typeof errors.value }) => {
              setFieldError(error.propertyPath, error.message);
            }
          );
          return Promise.reject(error);
        }
      })();
      return (await res) || Promise.reject();
    },
    update: async (service: ModelService<M>, resetForm = true) => {
      actions.setTouched();
      const res = handleSubmit.withControlled(async (values, { resetForm: reset, setFieldError }) => {
        try {
          const response = await service.update(clean(values));
          resetForm && reset();
          return response;
        } catch (error: any) {
          error.response.data.violations.forEach(
            (error: { message: string; propertyPath: keyof typeof errors.value }) => {
              setFieldError(error.propertyPath, error.message);
            }
          );
          return Promise.reject(error);
        }
      })();
      return (await res) || Promise.reject();
    },
  };
  return {
    state,
    meta,
    errors,
    isSubmitting,
    resetForm,
    handleSubmit,
    ...actions,
  };
}
