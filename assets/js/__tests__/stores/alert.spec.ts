import { createPinia } from "pinia";

import { AlertType, useAlertStore } from "@/stores/alert";

describe("alert store", () => {
  let alertStore: any;

  beforeAll(() => {
    const pinia = createPinia();
    alertStore = useAlertStore(pinia);
  });

  afterEach(() => {
    alertStore.clear();
  });

  it("add and remove alert", () => {
    alertStore.addAlert("Test alert message", AlertType.Success);
    expect(alertStore.getMessages).toHaveLength(1);
    alertStore.removeAlert(0);
    expect(alertStore.getMessages).toHaveLength(0);
  });

  it("clear all alert messages", () => {
    alertStore.addAlert("Test alert message", AlertType.Warning);
    expect(alertStore.hasAlerts).toBe(true);
    alertStore.clear();
    expect(alertStore.hasAlerts).toBe(false);
  });

  it("throw error when add invalid alert type", () => {
    const consoleSpy = jest.spyOn(console, "error").mockImplementation();
    alertStore.addAlert("Test alert message", "invalid");
    expect(consoleSpy).toHaveBeenCalledWith("Invalid alert type: invalid");
  });

  it("add success, warning, and error messages", () => {
    alertStore.success("Success message");
    alertStore.warning("Warning message");
    alertStore.error("Error message");
    expect(alertStore.getMessages).toHaveLength(3);
  });
});
