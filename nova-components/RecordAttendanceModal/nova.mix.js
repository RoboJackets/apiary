const mix = require('laravel-mix')
const path = require('path')
const webpack = require('webpack')

class NovaExtension {
  name() {
    return 'nova-extension'
  }

  register(name) {
    this.name = name
  }

  webpackConfig(webpackConfig) {
    webpackConfig.externals = {
      vue: 'Vue',
      'laravel-nova-ui': 'LaravelNovaUi',
    }

    webpackConfig.output = {
      uniqueName: this.name,
    }

    webpackConfig.resolve = webpackConfig.resolve || {}
    webpackConfig.resolve.modules = [
      path.resolve(__dirname, 'node_modules'),
      'node_modules',
    ]
  }
}

mix.extend('nova', new NovaExtension())
