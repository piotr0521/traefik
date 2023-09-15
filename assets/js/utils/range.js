import { format, isValid, parse } from "date-fns";

export default class DateIntervalProvider {
  static #format = "yyyy-MM-dd";

  getInterval() {
    if (this.canCreateFromQuery()) {
      return this.#createFromQuery();
    }

    if (localStorage.getItem("interval")) {
      return this.#createFromLocalStorage();
    }

    return this.#createDefault();
  }

  canCreateFromQuery() {
    return (
      this.#getQuery().from &&
      this.#isValidDate(this.#getQuery().from) &&
      this.#getQuery().to &&
      this.#isValidDate(this.#getQuery().to)
    );
  }

  #isValidDate(date) {
    return isValid(this.#parseDate(date));
  }

  #parseDate(date) {
    return parse(date, DateIntervalProvider.#format, new Date());
  }

  #createFromQuery() {
    return { start: this.#getQuery().from, end: this.#getQuery().to };
  }

  #createFromLocalStorage() {
    return JSON.parse(localStorage.getItem("interval"));
  }

  #createDefault() {
    let end = new Date();
    let start = new Date();
    start = start.setMonth(start.getMonth() - 1);
    return {
      start: format(start, DateIntervalProvider.#format),
      end: format(end, DateIntervalProvider.#format),
    };
  }
  #getQuery() {
    // Extract params directly from the URL instead of the router object to avoid having to wait for the router to be ready before we can use it.
    return new Proxy(new URLSearchParams(window.location.search), {
      get: (searchParams, prop) => searchParams.get(prop),
    });
  }
}
