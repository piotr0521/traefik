const test_sponsor = "A Test Sponsor";
describe("Sponsors module", function () {
  beforeEach(function () {
    cy.intercept({
      method: "GET",
      url: "/api/sponsors**",
    }).as("getSponsors");
  });
  it("should list sponsors", function () {
    cy.login();
    cy.visit("/user/dashboard");
    cy.get("a").contains("Sponsors").click();
    cy.wait("@getSponsors").its("response.statusCode").should("eq", 200);
    cy.get("table").contains("td", "10 Federal");
  });
  it("should add a sponsor", function () {
    cy.login();
    cy.visit("/user/dashboard");
    cy.intercept({
      method: "POST",
      url: "/api/sponsors",
    }).as("storeSponsor");
    cy.get("a").contains("Sponsors").click();
    cy.wait(5000);
    cy.get("button.w-11.h-11").click();
    cy.get("button.flex.items-center.text-indigo-900.text-sm").contains("Add").click();
    cy.get('input[name="groshy_sponsor_add[name]"]').type(test_sponsor);
    cy.get('input[name="groshy_sponsor_add[website]"]').type("https://testing.dev");
    cy.get("button").contains("Submit").click();
    cy.wait("@storeSponsor").its("response.statusCode").should("eq", 201);
    cy.wait("@getSponsors").its("response.statusCode").should("eq", 200);
    cy.get("table").contains("tr", test_sponsor);
  });
  context("Sponsor edit form", function () {
    beforeEach(function () {
      cy.login();
      cy.visit("/user/dashboard");
      cy.get("a").contains("Sponsors").click();
      cy.wait("@getSponsors").its("response.statusCode").should("eq", 200);
      cy.get("table").contains("tr", test_sponsor).find("button.block.relative").click();
      cy.get("button.flex.items-center.text-indigo-900.text-sm").contains("Edit").click();
    });
    // it("Displays error on required fields", function () {
    // cy.intercept({
    // method: "PATCH",
    // url: "/api/sponsors/**",
    // }).as("updateSponsor");
    // cy.on("uncaught:exception", (err, runnable) => {
    // if (err?.response?.statusCode === 422) {
    // return true;
    // }
    // return false;
    // });
    // cy.get('input[name="groshy_sponsor_add[name]"]').clear();
    // cy.get('input[name="groshy_sponsor_add[website]"]').clear();
    // cy.get("button").contains("Submit").click();
    // cy.wait("@updateSponsor").its("response.statusCode").should("eq", 422);
    // cy.get("span.form-error.text-sm").should("be.visible").and("contain", "This value should not be blank.");
    // });
    it("Displays error on website field due to incorrect format", function () {
      cy.intercept({
        method: "PATCH",
        url: "/api/sponsors/**",
      }).as("updateSponsor");
      cy.on("uncaught:exception", (err, runnable) => {
        if (err?.response?.statusCode === 422) {
          return true;
        }
        return false;
      });
      cy.get('input[name="groshy_sponsor_add[website]"]')
        .clear()
        .type("invalidwebsite" + "{enter}");
      cy.wait("@updateSponsor").its("response.statusCode").should("eq", 422);
      cy.get('input[name="groshy_sponsor_add[website]"]')
        .siblings("span.form-error.text-sm")
        .first()
        .should("be.visible")
        .and("contain", "This value is not a valid URL.");
    });
    it("should edit a sponsor", function () {
      cy.intercept({
        method: "PATCH",
        url: "/api/sponsors/**",
      }).as("updateSponsor");
      cy.get('input[name="groshy_sponsor_add[name]"]').clear().type(`${test_sponsor} Updated`);
      cy.get('input[name="groshy_sponsor_add[website]"]').clear().type("https://testing.dev");
      cy.get("button").contains("Submit").click();
      cy.wait("@updateSponsor").its("response.statusCode").should("eq", 200);
      cy.wait("@getSponsors").its("response.statusCode").should("eq", 200);
      cy.get("table").contains("tr", `${test_sponsor} Updated`);
    });
  });

  context("Sponsor delete flow", function () {
    beforeEach(function () {
      cy.login();
      cy.visit("/user/dashboard");
      cy.get("a").contains("Sponsors").click();
      cy.wait(3000);
    });
    it("should delete a sponsor", function () {
      cy.intercept({
        method: "DELETE",
        url: "/api/sponsors/**",
      }).as("deleteSponsor");
      cy.get("table").contains("tr", `${test_sponsor} Updated`).find("button.block.relative").click();
      cy.get("button.flex.items-center.text-indigo-900.text-sm").contains("Delete").click();
      cy.wait("@deleteSponsor").its("response.statusCode").should("eq", 204);
      cy.wait("@getSponsors").its("response.statusCode").should("eq", 200);
      cy.get("table").contains("tr", `${test_sponsor} Updated`).should("not.exist");
    });
  });
});
