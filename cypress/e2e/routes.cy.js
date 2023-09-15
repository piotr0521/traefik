function transformSlug(route) {
  const slug = route.split("/").pop().replace(/[-_]/g, " ");
  let title = slug
    .split(/\s+/)
    .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
    .join(" ");

  switch (title) {
    case "Profile":
      title = "Update Profile";
      break;
    case "Password":
      title = "Change password";
      break;
    case "Public Non Traded Reit":
      title = "Public Non Traded REIT";
      break;
    case "Real Estate Gp Fund":
      title = "Real Estate GP Fund";
      break;
    case "Real Estate Lp Fund":
      title = "Real Estate LP Fund";
      break;
    case "Private Equity Gp Fund":
      title = "Private Equity GP Fund";
      break;
    case "Private Equity Lp Fund":
      title = "Private Equity LP Fund";
      break;
    case "Security":
      title = "Modern way to manage your assets and investments";
      break;
    case "Features":
      title = "Modern way to manage your assets and investments";
      break;
    case "Pricing":
      title = "Membership Pricing";
      break;
    case "Terms":
      title = "Terms of Use – Groshy";
      break;
    case "Privacy Policy":
      title = "Groshy Privacy Policy";
      break;
  }

  return title;
}

describe("Check all routes", () => {
  const routesWithoutAuth = ["/security", "/features", "/pricing", "/terms", "/privacy-policy"];

  context("Check route without auth", () => {
    routesWithoutAuth.forEach((route) => {
      it(`should navigate to ${route}`, () => {
        cy.visit(route);
        const title = transformSlug(route);
        cy.get("h1").should("contain", title);
      });
    });
  });

  const routes = [
    "/user/dashboard",
    "/user/profile",
    "/user/account-holders",
    "/user/sponsors",
    "/user/institutions",
    "/user/transactions",
    "/user/tags",
    "/user/password",
    "/user/account-security",
    "/user/billing",
  ];

  context("Check non-asset page", () => {
    beforeEach(() => {
      cy.login();
      cy.visit("/user/dashboard");
    });

    routes.forEach((route) => {
      it(`should navigate to ${route}`, () => {
        cy.visit(route);
        const title = transformSlug(route);
        cy.log(title);
        cy.get(".loader", { timeout: 5000 }).should("not.exist");
        cy.get(".breadcrumbs").should("contain", title);
      });
    });
  });
  // Should change this to popup
  const routesAssetsAdd = "/user/asset/:slug/add";
  context(`Check ${routesAssetsAdd}`, () => {
    let children, assets;

    beforeEach(() => {
      cy.login();
      cy.intercept("GET", "/api/asset_types").as("assetType");
      cy.visit("/user/dashboard");
      cy.wait("@assetType").then((response) => {
        const { "hydra:member": assetType } = response.response.body;
        assets = assetType
          .filter((item) => !item.parent)
          .map((asset) => ({
            ...asset,
            children: assetType.filter(
              (item) => item.slug === "cash" && item.parent && item.parent.slug === asset.slug
            ),
          }));
        cy.log(assets);
        children = assetType.filter(
          (item) => item.parent && item.parent.slug !== "alternative-investment" && item.slug !== "cash"
        );
      });
    });

    it("should navigate to /user/asset/:slug/add", () => {
      [...children].forEach((asset) => {
        const getSlug = routesAssetsAdd.replace(":slug", asset.slug);
        cy.visit(getSlug);
        // remove "/add" on route
        const slug = getSlug.replace("/add", "");
        const title = transformSlug(slug);

        cy.get(".loader", { timeout: 5000 }).should("not.exist");

        cy.log("title", title);
        cy.get(".breadcrumbs").should("contain", title).and("contain", "Add Investment");
      });
    });
    it("should navigate to modal options", () => {
      cy.contains("button", "Add Asset").click();
      cy.get("div#asset-modal").within(() => {
        assets.forEach((asset) => {
          cy.contains("button", asset.name).click();
          if (asset.children && asset.children.length > 0) {
            asset.children.forEach((child) => {
              cy.get(".col-span-2.overflow-y-auto.h-full").within(() => {
                cy.contains("button", child.name).click();
              });
              cy.contains("h2", `Create new ${child.name} account`).should("exist");

              cy.get("#button-back").click();
            });
          }
        });
      });
    });
  });

  const routesAssets = "/user/asset/:slug";
  context(`Check ${routesAssets}`, () => {
    let assets, children;
    beforeEach(() => {
      cy.login();
      cy.intercept("GET", "/api/asset_types").as("assetType");
      cy.visit("/user/dashboard");
      cy.wait("@assetType").then((response) => {
        const { "hydra:member": assetType } = response.response.body;
        assets = assetType.filter((item) => !item.parent && item.isAsset);
        children = assetType.filter((item) => item.parent);
      });
    });

    it("should navigate to /user/asset/:slug", () => {
      [...assets, ...children].forEach((asset) => {
        const getSlug = routesAssets.replace(":slug", asset.slug);
        cy.log("getSlug", getSlug);
        cy.visit(getSlug);
        cy.wait(5000);

        const title = transformSlug(getSlug);
        cy.log("title", title);

        cy.get(".loader", { timeout: 5000 }).should("not.exist");
        cy.get(".breadcrumbs").should("contain", title);
      });
    });

    it("should navigate to /user/position/:uuid", () => {
      [...assets, ...children].forEach((asset) => {
        const getSlug = routesAssets.replace(":slug", asset.slug);
        cy.log("getSlug", getSlug);
        cy.visit(getSlug);
        cy.get(".loader", { timeout: 5000 }).should("not.exist");
        cy.get(".grid.p-10.gap-7").then(($table) => {
          const $dashboardTable = $table.find("table.table-dashboard");

          // check assets has data
          if ($dashboardTable.length > 0) {
            cy.get("table.table-dashboard a")
              .first()
              .then(($link) => {
                const linkText = $link.text();

                // redirect to user/position/:uuid
                cy.wrap($link).click();

                // check the breadcrumb user/position/:uuid
                cy.get(".loader", { timeout: 5000 }).should("not.exist");
                cy.get(".breadcrumbs").should("contain", linkText);
              });
          } else {
            // no investment
            cy.contains("You do not have any investments");
          }
        });
      });
    });
  });
});
