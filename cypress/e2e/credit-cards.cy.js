describe("Credit cards module", function () {
  cy.intercept("GET", "/api/position/credit_cards").as("creditCards");
  it("should list credit cards", function () {
    cy.login();
    cy.visit("/user/dashboard");
    cy.get("a").contains("Credit Card").click();
    cy.wait("@creditCards").its("response.statusCode").should("eq", 200);
  });
  it("should add a sponsor", function () {
    cy.login();
    cy.visit("/user/dashboard");
    cy.intercept(
      {
        method: "POST",
        url: "/api/position/credit_cards",
      },
      []
    ).as("storeCreditCard");
    cy.get("a").contains("Credit Card").click();
    cy.wait("@creditCards").its("response.statusCode").should("eq", 200);
    cy.get("button.btn-dashboard").contains("Add Account").click();
    cy.get('input[name="groshy_credit_card_add_name"]').type("Test Sponsor");
    cy.get('input[placeholder="Select institution"]').click();
    cy.get("#vs1__listbox").find("#vs1__option-0").click();
    cy.get("button").contains("Continue").click();
    cy.get('input[name="groshy_credit_card_add_limit"]').clear().clear().type("50");
    cy.get('input[name="groshy_credit_card_add_balance"]').clear().clear().type("5");
    cy.get("button").contains("Continue").click();
    cy.get("button").contains("Submit").click();
    cy.wait("@storeCreditCard").its("response.statusCode").should("eq", 200);
  });
});
