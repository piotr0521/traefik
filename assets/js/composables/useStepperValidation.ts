import type { ComputedRef, Ref } from "vue";
type Meta = {
  [key: string]: {
    touched: boolean;
  };
};
type Error = {
  [key: string]: string | undefined;
};
export default function useStepperValidation(
  meta: ComputedRef<Meta>[],
  errors: ComputedRef<Error>[],
  step: Ref<number>
) {
  /**
   * Validate the current step
   * @returns
   * true if the current step has no errors
   * false if the current step has errors
   * @description
   * This function sets the touched property of all the fields in the current step to true
   * and returns true if the current step has no errors
   * and returns false if the current step has errors
   * @example
   * const { validate } = useStepperValidation(meta, errors, step);
   * const isValid = validate();
   * if (isValid) {
   *  // do something
   * }
   *  */
  const validate = () => {
    const metaStep = meta[step.value - 1].value;
    const keys = Object.keys(metaStep);
    for (const key of keys) {
      metaStep[key].touched = true;
    }
    return !hasErrors(step.value);
  };
  /**
   * Has errors in the current step
   * @param step
   * @returns
   * true if the current step has errors
   * false if the current step has no errors
   * @description
   * This function returns true if the current step has errors
   * and returns false if the current step has no errors
   * @example
   * const { hasErrors } = useStepperValidation(meta, errors, step);
   * const hasErrors = hasErrors(step.value);
   * if (hasErrors) {
   * // do something
   * }
   */
  const hasErrors = (step: number) => {
    return Object.values(errors[step - 1].value).some((error) => error);
  };
  /**
   * Go back to the first step with errors
   * @description
   * This function goes back to the first step with errors
   * @example
   * const { goBack } = useStepperValidation(meta, errors, step);
   * goBack();
   */
  const goBack = () => {
    if (
      !Object.values(errors)
        .flat()
        .some((error) => error.value)
    )
      return;
    for (let i = 1; i <= errors.length; i++) {
      if (hasErrors(i)) {
        step.value = i;
        return;
      }
    }
  };

  return {
    validate,
    hasErrors,
    goBack,
  };
}
