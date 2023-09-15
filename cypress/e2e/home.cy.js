describe("Home page", () => {
  it("has footer links", () => {
    cy.visit("");
    cy.contains("a", "Privacy Policy").should("be.visible");
    cy.contains("a", "Terms of Use").should("be.visible");
  });
  it("has header links", () => {
    cy.visit("");
    cy.get("div.btn-menubar.nav-menubar").first().click();
    // It should look for the elements on the mobile menu since cypress works on a mobile viewport
    cy.get(".menu-mobile").within(() => {
      cy.contains("a", "Features").should("be.visible");
      cy.contains("a", "Security").should("be.visible");
      cy.contains("a", "Pricing").should("be.visible");
      cy.contains("a", "Sign in").should("be.visible");
      cy.contains("a", "Try it free").should("be.visible");
    });
  });
  it("has sign up button links", () => {
    cy.visit("");
    cy.contains("a", "Sign up now").should("be.visible");
  });
});
