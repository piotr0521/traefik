describe("account holders module", function () {
  it("should list account holders", function () {
    cy.login();
    cy.visit("/user/dashboard");
    cy.intercept({
      method: "GET",
      url: "/api/account_holders",
    }).as("listAccountHolders");
    cy.get("a").contains("Account Holders").click();
    cy.wait("@listAccountHolders").its("response.statusCode").should("eq", 200);
    cy.get("table").find("tbody").should("have.length.above", 1);
  });
  it("displays error on add account holder(required values)", function () {
    cy.login();
    cy.visit("/user/dashboard");
    cy.intercept({
      method: "POST",
      url: "/api/account_holders",
    }).as("storeAccountHolders");
    cy.on("uncaught:exception", (err, runnable) => {
      if (err?.response?.statusCode === 422) {
        return true;
      }
      return false;
    });
    cy.get("a").contains("Account Holders").click();
    cy.get("button.w-11.h-11").click();
    cy.get("button.flex.items-center.text-indigo-900.text-sm").click();
    cy.get("button").contains("Submit").click();
    cy.wait("@storeAccountHolders").its("response.statusCode").should("eq", 422);
    cy.get('input[name="groshy_account_holder_name"]')
      .siblings("span")
      .should("be.visible")
      .and("contain", "This value should not be blank.");
  });
  it("should add an account holder", function () {
    cy.login();
    cy.visit("/user/dashboard");
    // Mock the response so pinia store doesn't break
    cy.intercept(
      {
        method: "POST",
        url: "/api/account_holders",
      },
      {
        statusCode: 200,
        body: {
          id: 1,
          name: "Test Account Holder",
        },
      }
    ).as("storeAccountHolders");
    cy.get("a").contains("Account Holders").click();
    cy.get("button.w-11.h-11").click();
    cy.get("button.flex.items-center.text-indigo-900.text-sm").click();
    cy.get('input[name="groshy_account_holder_name"]').type("Test Account Holder{enter}");
    cy.wait("@storeAccountHolders").its("response.statusCode").should("eq", 200);
  });
  it("should edit an account holder", function () {
    cy.login();
    cy.visit("/user/dashboard");
    // Mock the response so pinia store doesn't break
    cy.intercept(
      {
        method: "PATCH",
        url: "/api/account_holders/*",
      },
      {
        statusCode: 200,
        body: {
          id: 1,
          name: "Test Account Holder",
        },
      }
    ).as("updateAccountHolders");
    cy.intercept({
      method: "GET",
      url: "/api/account_holders",
    }).as("listAccountHolders");
    cy.get("a").contains("Account Holders").click();
    cy.wait("@listAccountHolders").its("response.statusCode").should("eq", 200);
    cy.get("table").find("tbody").find("tr").first().find("button").click();
    cy.get("button").contains("Edit").click();
    cy.get('input[name="groshy_account_holder_name"]').clear().type("Test Account Holder{enter}");
    cy.wait("@updateAccountHolders").its("response.statusCode").should("eq", 200);
  });
  it("should delete an account holder", function () {
    cy.login();
    cy.visit("/user/dashboard");
    cy.intercept(
      {
        method: "DELETE",
        url: "/api/account_holders/*",
      },
      []
    ).as("deleteAccountHolders");
    cy.intercept({
      method: "GET",
      url: "/api/account_holders",
    }).as("listAccountHolders");
    cy.get("a").contains("Account Holders").click();

    cy.wait("@listAccountHolders").its("response.statusCode").should("eq", 200);
    cy.get("table").find("tbody").find("tr").first().find("button").click();
    cy.get("button").contains("Delete").click();
    cy.wait("@deleteAccountHolders").its("response.statusCode").should("eq", 200);
  });
});
