const Encore = require("@symfony/webpack-encore");
const { PurgeCSSPlugin } = require('purgecss-webpack-plugin');
const CssMinimizerPlugin = require("css-minimizer-webpack-plugin");
const glob = require("glob-all");
const path = require("path");
// const purgeCSSPlugin = require('@fullhuman/postcss-purgecss')

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
  // only needed for CDN's or subdirectory deploy
  //.setManifestKeyPrefix('build/')

  /*
   * ENTRY CONFIG
   *
   * Each entry will result in one JavaScript file (e.g. app.js)
   * and one CSS file (e.g. app.css) if your JavaScript imports CSS.
   */
  .addEntry("app", "./assets/app.js")
  .addEntry("discordUser", "./assets/vue/UserDiscord.js")
  .addEntry("ticketFeed", "./assets/vue/TicketFeed/Feed.js")
  .addEntry("timelineViewer", "./assets/js/timelineViewer.js")
  // .addEntry("logViewerTS", "./assets/ts/logViewer.ts")
  // .addEntry("dashboard", "./assets/styles/dashboard.scss")
  
  // When enabled, Webpack "splits" your files into smaller pieces for greater optimization.
  .splitEntryChunks()

  // enables the Symfony UX Stimulus bridge (used in assets/bootstrap.js)
  .enableStimulusBridge("./assets/controllers.json")

  // will require an extra script tag for runtime.js
  // but, you probably want this, unless you're building a single-page app
  .enableSingleRuntimeChunk()

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
  .enableIntegrityHashes(Encore.isProduction())

  // configure Babel
  // .configureBabel((config) => {
  //     config.plugins.push('@babel/a-babel-plugin');
  // })

  // enables and configure @babel/preset-env polyfills
  .configureBabelPresetEnv((config) => {
    config.useBuiltIns = "usage";
    config.corejs = "3.23";
  })

  // enables Sass/SCSS support
  .enableSassLoader()

  // uncomment if you use TypeScript
  // .enableTypeScriptLoader()

  // uncomment if you use React
  //.enableReactPreset()

  // uncomment to get integrity="..." attributes on your script & link tags
  // requires WebpackEncoreBundle 1.4 or higher
  //.enableIntegrityHashes(Encore.isProduction())

  // uncomment if you're having problems with a jQuery plugin
  // .autoProvidejQuery()
  .enableVueLoader()

if (Encore.isProduction()) {
Encore.addPlugin(
    new PurgeCSSPlugin({
      paths: glob.sync([
        path.join(__dirname, "templates/**/*.html.twig"),
        path.join(__dirname, "templates/**/*.svg"),
        path.join(__dirname, "app/**/*.php"),
        path.join(__dirname, "src/**/*.php"),
        path.join(__dirname, "assets/**/*.*"),
    ]),
    safelist: {
      standard: ['text-bg-perma'],
      deep: [
        /table-/,
        /autoComplete/,
        /alert-/,
        /bg-/,
        /tippy-/
      ]
    }
    }),
  );
Encore.addPlugin(
    new CssMinimizerPlugin({
        minimizerOptions: {
          preset: [
            "default",
            {
              discardComments: { removeAll: true },
            },
          ],
        },
      })
)
}
module.exports = Encore.getWebpackConfig();
