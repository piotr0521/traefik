export function negateAmount(value) {
  try {
    return {
      minor: -value.minor,
      base: -value.base,
      currency: value.currency,
    };
  } catch (error) {
    console.log(error);
  }
}
