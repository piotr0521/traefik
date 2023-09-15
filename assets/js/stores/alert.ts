import { defineStore } from "pinia";

export enum AlertType {
  Success = "success",
  Warning = "warning",
  Error = "error",
}

type AlertMessage = {
  message: string;
  type: AlertType;
};

type AlertState = {
  messages: AlertMessage[];
};

export const useAlertStore = defineStore("alertStore", {
  state: (): AlertState => ({
    messages: [],
  }),
  actions: {
    addAlert(message: string, type: AlertType, autoHide = true, duration = 4000) {
      if (Object.values(AlertType).includes(type)) {
        const alert = { message, type };
        this.messages.push(alert);

        if (autoHide) {
          setTimeout(() => {
            const index = this.messages.indexOf(alert);
            if (index >= 0) {
              this.messages.splice(index, 1);
            }
          }, duration);
        }
      } else {
        console.error(`Invalid alert type: ${type}`);
      }
    },
    removeAlert(index: number) {
      this.messages.splice(index, 1);
    },
    clear() {
      this.messages = [];
    },
    success(message: string, autoHide = true, duration = 4000) {
      this.addAlert(message, AlertType.Success, autoHide, duration);
    },
    warning(message: string, autoHide = true, duration = 4000) {
      this.addAlert(message, AlertType.Warning, autoHide, duration);
    },
    error(message: string, autoHide = true, duration = 4000) {
      this.addAlert(message, AlertType.Error, autoHide, duration);
    },
  },
  getters: {
    getMessages: (state): AlertMessage[] => {
      return state.messages;
    },
    hasAlerts(state): boolean {
      return state.messages.length > 0;
    },
  },
});
