/* eslint-disable @typescript-eslint/no-var-requires */
const Encore = require("@symfony/webpack-encore");
const path = require("path");
require("dotenv").config();
const BrowserSyncPlugin = require("browser-sync-webpack-plugin");

// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
  Encore.configureRuntimeEnvironment(process.env.NODE_ENV || "dev");
}

Encore
  // directory where compiled assets will be stored
  .setOutputPath("public/build/")
  // public path used by the web server to access the output path
  .setPublicPath("/build")
  // only needed for CDN's or sub-directory deploy
  //.setManifestKeyPrefix('build/')

  /*
   * ENTRY CONFIG
   *
   * Each entry will result in one JavaScript file (e.g. app.js)
   * and one CSS file (e.g. app.css) if your JavaScript imports CSS.
   */
  .addEntry("app", "./assets/js/app.ts")
  .addEntry("checkout-app", "./assets/js/checkout.ts")
  .addEntry("client", "./assets/client/script.js") // frontend

  // When enabled, Webpack "splits" your files into smaller pieces for greater optimization.
  .splitEntryChunks()

  // will require an extra script tag for runtime.js
  // but, you probably want this, unless you're building a single-page app
  .enableSingleRuntimeChunk()

  .addStyleEntry("tailwind", "./assets/css/tailwind.css")

  // https://symfony.com/doc/current/frontend/encore/postcss.html
  // the directory where the postcss.config.js file is stored
  // config: path.resolve(__dirname, 'sub-dir', 'custom.config.js'),

  // enable post css loader
  .enablePostCssLoader((options) => {
    options.postcssOptions = {
      // directory where the postcss.config.js file is stored
      path: "./postcss.config.js",
    };
  })

  /*
   * FEATURE CONFIG
   *
   * Enable & configure other features below. For a full
   * list of features, see:
   * https://symfony.com/doc/current/frontend.html#adding-more-features
   */
  .cleanupOutputBeforeBuild()
  .enableBuildNotifications()
  .enableSourceMaps(!Encore.isProduction())
  // enables hashed filenames (e.g. app.abc123.css)
  .enableVersioning(Encore.isProduction())

  .configureBabel((config) => {
    config.plugins.push("@babel/plugin-proposal-class-properties");

    /* It removes all console.logs from the production build. */
    if (Encore.isProduction()) {
      config.plugins.push("transform-remove-console");
    }
  })

  // enables @babel/preset-env polyfills
  .configureBabelPresetEnv((config) => {
    config.useBuiltIns = "usage";
    config.corejs = 3;
  })

  // enables Sass/SCSS support
  //.enableSassLoader()

  // uncomment if you use TypeScript
  .enableTypeScriptLoader()

  // uncomment if you use React
  //.enableReactPreset()

  // uncomment to get integrity="..." attributes on your script & link tags
  // requires WebpackEncoreBundle 1.4 or higher
  //.enableIntegrityHashes(Encore.isProduction())

  // uncomment if you're having problems with a jQuery plugin
  //.autoProvidejQuery()

  .enableVueLoader()
  .addPlugin(
    new BrowserSyncPlugin(
      {
        host: "127.0.0.1",
        port: 3000,
        proxy: process.env.PROXY,
        files: [
          {
            match: ["src/*.php"],
          },
          {
            match: ["templates/*.twig"],
          },
          {
            match: ["assets/*.js"],
          },
          {
            match: ["assets/*.css"],
          },
          {
            match: ["assets/*.ts"],
          },
        ],
        notify: false,
      },

      {
        reload: true,
      }
    )
  )
  .enableStylusLoader();
const config = Encore.getWebpackConfig();
/**
 * Add custom configuration to webpack itself.
 */
config.resolve.alias = {
  ...config.resolve.alias,
  "@": path.resolve(__dirname, "assets/js"),
};
module.exports = config;
