module.exports = {
  env: {
    node: true,
  },
  plugins: ["unused-imports", "simple-import-sort", "@typescript-eslint", "cypress"],
  extends: [
    "eslint:recommended",
    "plugin:@typescript-eslint/recommended",
    "plugin:vue/vue3-recommended",
    "plugin:prettier-vue/recommended",
    "prettier",
    "plugin:cypress/recommended",
  ],
  settings: {},
  parser: "vue-eslint-parser",
  parserOptions: {
    parser: "@typescript-eslint/parser",
    extraFileExtensions: [".vue"],
  },
  ignorePatterns: ["**/*.d.ts"],
  rules: {
    "prettier-vue/prettier": [
      "error",
      {
        // Override all options of `prettier` here
        // @see https://prettier.io/docs/en/options.html
        printWidth: 120,
        singleQuote: false,
        semi: true,
        trailingComma: "es5",
        tabWidth: 2,
        bracketSpacing: true,
      },
    ],
    "no-unused-vars": "off",
    "no-console": "warn",
    "no-extra-boolean-cast": "off",

    // Vue
    "vue/multi-word-component-names": "warn",
    "vue/no-mutating-props": "warn",

    //  Import
    "import/prefer-default-export": "off",

    // Unused Import
    "unused-imports/no-unused-imports": "error",
    "unused-imports/no-unused-vars": [
      "warn",
      {
        vars: "all",
        varsIgnorePattern: "^_",
        args: "after-used",
        argsIgnorePattern: "^_",
      },
    ],

    // Import Sort
    "simple-import-sort/imports": "error",
    "simple-import-sort/exports": "error",

    // Cypress
    "cypress/no-assigning-return-values": "error",
    "cypress/no-unnecessary-waiting": "warn",
    "cypress/assertion-before-screenshot": "warn",
    "cypress/no-force": "warn",
    "cypress/no-async-tests": "error",
    "cypress/no-pause": "error",
  },
};
