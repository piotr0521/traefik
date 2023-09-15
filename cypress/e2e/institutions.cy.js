describe("Institutions module", function () {
  it("should list institutions", function () {
    cy.login();
    cy.visit("/user/dashboard");
    cy.intercept({
      method: "GET",
      url: "/api/institutions?**",
    }).as("listInstitutions");
    cy.get("a").contains("Institutions").click();
    cy.wait("@listInstitutions").its("response.statusCode").should("eq", 200);
    cy.get("table").find("tbody").should("have.length.above", 1);
  });
  it("displays error on add institution(required values)", function () {
    cy.login();
    cy.visit("/user/dashboard");
    cy.intercept({
      method: "POST",
      url: "/api/institutions",
    }).as("storeInstitution");
    cy.on("uncaught:exception", (err, runnable) => {
      if (err?.response?.statusCode === 422) {
        return true;
      }
      return false;
    });
    cy.get("a").contains("Institutions").click();
    cy.get("button.w-11.h-11").click();
    cy.get("button.flex.items-center.text-indigo-900.text-sm").click();
    cy.get("button").contains("Submit").click();
    cy.wait("@storeInstitution").its("response.statusCode").should("eq", 422);
    cy.get('input[name="groshy_institution_add_name"]')
      .siblings("span")
      .should("be.visible")
      .and("contain", "This value should not be blank.");
    cy.get('input[name="groshy_institution_add_website"]')
      .siblings("span")
      .should("be.visible")
      .and("contain", "This value should not be blank.");
  });
  it("displays error on add institution(invalid website)", function () {
    cy.login();
    cy.visit("/user/dashboard");
    cy.intercept({
      method: "POST",
      url: "/api/institutions",
    }).as("storeInstitution");
    cy.on("uncaught:exception", (err, runnable) => {
      if (err?.response?.statusCode === 422) {
        return true;
      }
      return false;
    });
    cy.get("a").contains("Institutions").click();
    cy.get("button.w-11.h-11").click();
    cy.get("button.flex.items-center.text-indigo-900.text-sm").click();
    cy.get('input[name="groshy_institution_add_name"]').type("Test Institution");
    cy.get('input[name="groshy_institution_add_website"]').type("wrongformat{enter}");
    cy.wait("@storeInstitution").its("response.statusCode").should("eq", 422);
    cy.get('input[name="groshy_institution_add_website"]')
      .siblings("span")
      .should("be.visible")
      .and("contain", "This value is not a valid URL.");
  });
  it("should add an institution", function () {
    cy.login();
    cy.visit("/user/dashboard");
    cy.intercept(
      {
        method: "POST",
        url: "/api/institutions",
      },
      []
    ).as("storeInstitution");
    cy.get("a").contains("Institutions").click();
    cy.get("button.w-11.h-11").click();
    cy.get("button.flex.items-center.text-indigo-900.text-sm").click();
    cy.get('input[name="groshy_institution_add_name"]').type("Test Institution");
    cy.get('input[name="groshy_institution_add_website"]').type("https://testing.dev{enter}");
    cy.wait("@storeInstitution").its("response.statusCode").should("eq", 200);
  });
});
