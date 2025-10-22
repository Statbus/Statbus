const Encore = require("@symfony/webpack-encore");

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
  .cleanupOutputBeforeBuild()
  .splitEntryChunks()
  .enableSingleRuntimeChunk()
  .enableSourceMaps(!Encore.isProduction())
  .enableVersioning(Encore.isProduction())
  .enableVueLoader(() => {}, { runtimeCompilerBuild: false })
  .configureBabel((config) => {
    config.plugins.push("@babel/plugin-syntax-import-meta");
  })
  /*
   * ENTRY CONFIG
   *
   * Each entry will result in one JavaScript file (e.g. app.js)
   * and one CSS file (e.g. app.css) if your JavaScript imports CSS.
   */
  .addEntry("app", "./assets/app.js")
  .addEntry("globalSearch", "./assets/globalSearch.js")
  .addEntry("playtime", "./assets/playtime.js")
  .addEntry("theme", "./assets/themeSwitcher.js")
  .addEntry("jobs", "./assets/components/jobs.js")
  // .addEntry("pollGraph", "./assets/components/pollGraph.js")
  .addEntry("ballot", "./assets/components/ballot.js")
  // .addEntry("badger-icons","./assets/components/badgerIcons.js")
  .addEntry("badger", "./assets/components/badger.js")
  .addEntry("roundMap", "./assets/components/roundMap.js")
  .addEntry(
    "time_dilation_current-3",
    "./assets/components/time-dilation-current-3.js"
  )
  .addEntry("connectionChart", "./assets/components/connectionChart.js")
  .addEntry("playerServerChart", "./assets/components/playerServerChart.js")
  // .addEntry("logviewer", "./assets/components/logviewer/app.js")
  .addEntry(
    "hourlyConnectionChart",
    "./assets/components/hourlyConnectionChart.js"
  )

  // .addEntry("map", "./assets/components/map.js")

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
  .enableSassLoader();

// uncomment if you use TypeScript
//.enableTypeScriptLoader()

// uncomment if you use React
//.enableReactPreset()

// uncomment to get integrity="..." attributes on your script & link tags
// requires WebpackEncoreBundle 1.4 or higher
//.enableIntegrityHashes(Encore.isProduction())

// uncomment if you're having problems with a jQuery plugin
//.autoProvidejQuery()

module.exports = Encore.getWebpackConfig();
