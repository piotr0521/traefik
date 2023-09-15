export function formatCurrency(value) {
  try {
    return new Intl.NumberFormat("en-us", {
      style: "currency",
      signDisplay: "auto",
      currency: "USD",
      maximumFractionDigits: 0,
    }).format(value);
  } catch (error) {
    console.log(error);
  }
}

export function formatCurrencyStruct(value, maximumFractionDigits = 0) {
  if (!value || !value.base) return "Incorrect format";

  try {
    return new Intl.NumberFormat("en-us", {
      style: "currency",
      signDisplay: "auto",
      currency: value.currency,
      maximumFractionDigits: maximumFractionDigits,
    }).format(value.base);
  } catch (error) {
    console.log(error);
  }
}

export function formatPercent(value) {
  try {
    return new Intl.NumberFormat("en-us", {
      style: "decimal",
      signDisplay: "auto",
      maximumFractionDigits: 2,
    }).format(value);
  } catch (error) {
    console.log(error);
  }
}

export function formatPercentReal(value) {
  try {
    value = new Intl.NumberFormat("en-us", {
      style: "decimal",
      signDisplay: "auto",
      maximumFractionDigits: 2,
    }).format(value * 100);
    if ("-0" === value) {
      value = 0;
    }
    return value + "%";
  } catch (error) {
    console.log(error);
  }
}

export function formatDate(value) {
  if (!value) return;

  try {
    return new Intl.DateTimeFormat("en-US", {
      timeZone: "UTC",
      year: "numeric",
      month: "short",
      day: "numeric",
    }).format(new Date(value));
  } catch (error) {
    console.log(error);
  }
}

export function formatDecimalNumber(value) {
  try {
    return new Intl.NumberFormat("en-us", {
      style: "decimal",
      signDisplay: "auto",
      maximumFractionDigits: 2,
    }).format(value);
  } catch (error) {
    console.log(error);
  }
}

/**
 * It takes a number and a currency code and returns a formatted string
 * @param amount - The amount to format.
 * @param [currency=USD] - The currency to use.
 * @returns A string with the amount formatted as a currency.
 */
export function formatAmount(amount, currency = "USD") {
  try {
    return new Intl.NumberFormat("en-us", {
      style: "currency",
      signDisplay: "auto",
      currency: currency,
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    }).format(amount / 100);
  } catch (error) {
    console.log(error);
  }
}
