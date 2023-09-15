describe("Logging In", function () {
  // we can use these values to log in
  const username = "user2";
  const password = "user2";

  context("Unauthorized", function () {
    it("it redirects on visit to /user/dashboard when no session", function () {
      // we must have a valid session cookie to be logged
      // in else we are redirected to /unauthorized
      cy.visit("/user/dashboard");
      cy.get("h1").should("contain", "Sign In");

      cy.url().should("include", "login");
    });
  });

  context("HTML form submission", function () {
    beforeEach(function () {
      cy.visit("/login");
    });

    it("displays errors on login", function () {
      // incorrect username on purpose
      cy.get("input[id=username]").type("jane.lae");
      cy.get("input[id=password]").type("password123{enter}");

      // we should have visible errors now
      cy.get("div").should("be.visible").and("contain", "Invalid credentials.");

      // and still be on the same URL
      cy.url().should("include", "/login");
    });

    it("redirects to /user/dashboard on success", function () {
      cy.get("input[id=username]").type(username);
      cy.get("input[id=password]").type(password + "{enter}");

      // we should be redirected to /dashboard
      cy.url().should("include", "/user/dashboard");
    });
  });
});
