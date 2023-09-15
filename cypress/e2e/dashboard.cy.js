import { format } from "date-fns";

describe("Dashboard", () => {
  beforeEach(function () {
    cy.login();
    cy.visit("/user/dashboard");

    cy.get(".loader").should("not.exist");
    cy.wait(5000);
  });

  it("dashboard URL always has 'from' and 'to' parameters for date", () => {
    cy.url().should("include", "from").and("include", "to");
  });

  it("dashboard has 7 calendar buttons", () => {
    cy.get(".btn-dashboard").its("length").should("eq", 7);
  });

  it("click 1 month button on calendar changes URL updates button styles and refreshes URL", () => {
    cy.get("button.btn-dashboard[data-range=1]").as("btn").click();
    cy.get("@btn").should("have.class", "active");
    cy.get("@btn").then(($btn) => {
      let start = new Date();
      let end = new Date();
      start.setMonth(start.getMonth() - 1);

      cy.url()
        .should("contain", "from=" + format(start, "yyyy-MM-dd"))
        .and("contain", "to=" + format(end, "yyyy-MM-dd"));
    });
  });

  it("click 3 month button on calendar button changes URL updates button styles and refreshes URL", () => {
    cy.get("button.btn-dashboard[data-range=3]").as("btn").click();
    cy.get("@btn").should("have.class", "active");
    cy.get("@btn").then(($btn) => {
      let start = new Date();
      let end = new Date();
      start.setMonth(start.getMonth() - 3);

      cy.url()
        .should("contain", "from=" + format(start, "yyyy-MM-dd"))
        .and("contain", "to=" + format(end, "yyyy-MM-dd"));
    });
  });

  it("click 6 month button on calendar button changes URL updates button styles and refreshes URL", () => {
    cy.get("button.btn-dashboard[data-range=6]").as("btn").click();
    cy.get("@btn").should("have.class", "active");
    cy.get("@btn").then(($btn) => {
      let start = new Date();
      let end = new Date();
      start.setMonth(start.getMonth() - 6);

      cy.url()
        .should("contain", "from=" + format(start, "yyyy-MM-dd"))
        .and("contain", "to=" + format(end, "yyyy-MM-dd"));
    });
  });

  it("click YTD button on calendar button changes URL updates button styles and refreshes URL", () => {
    cy.get("button.btn-dashboard[data-range=ytd]").as("btn").click();
    cy.get("@btn").should("have.class", "active");
    cy.get("@btn").then(($btn) => {
      let start = new Date();
      let end = new Date();
      start = new Date(start.getFullYear(), 0, 1);

      cy.url()
        .should("contain", "from=" + format(start, "yyyy-MM-dd"))
        .and("contain", "to=" + format(end, "yyyy-MM-dd"));
    });
  });

  it("click MAX button on calendar button changes URL updates button styles and refreshes URL", () => {
    cy.get("button.btn-dashboard[data-range=max]").as("btn").click();
    cy.get("@btn").should("have.class", "active");
    cy.get("@btn").then(($btn) => {
      const startAttr = $btn.attr("data-range-start");
      const endAttr = $btn.attr("data-range-end");
      let start = startAttr ? Date.parse(startAttr) : new Date();
      let end = Date.parse(endAttr);

      cy.url()
        .should("contain", "from=" + format(start, "yyyy-MM-dd"))
        .should("contain", "to=" + format(end, "yyyy-MM-dd"));
    });
  });
});
