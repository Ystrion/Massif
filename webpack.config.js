const path = require('path')

const { WebpackManifestPlugin } = require('webpack-manifest-plugin')
const CssMinimizerPlugin = require('css-minimizer-webpack-plugin')
const ESLintPlugin = require('eslint-webpack-plugin')
const ImageMinimizerPlugin = require('image-minimizer-webpack-plugin')
const MiniCssExtractPlugin = require('mini-css-extract-plugin')
const StylelintPlugin = require('stylelint-webpack-plugin')

require('dotenv').config()

module.exports = (_, argv) => ({
  mode: 'production',
  entry: './resources/js/main.js',
  output: {
    path: path.resolve(__dirname, 'public', 'assets'),
    filename: 'js/main.js',
    publicPath: process.env.PUBLIC_ASSETS_PATH,
    clean: true
  },
  module: {
    rules: [
      {
        test: /\.(avif|gif|jpeg|jpg|png|svg|webp)$/,
        type: 'asset/resource',
        generator: {
          filename: 'img/[name][ext]'
        }
      },
      {
        test: /\.(eot|otf|ttf|woff|woff2)$/,
        type: 'asset/resource',
        generator: {
          filename: 'fonts/[name][ext]'
        }
      },
      {
        test: /\.(c|s[ac])ss$/,
        use: [
          MiniCssExtractPlugin.loader,
          'css-loader',
          {
            loader: 'postcss-loader',
            options: {
              postcssOptions: {
                plugins: ['postcss-preset-env']
              }
            }
          },
          'sass-loader'
        ]
      },
      {
        test: /\.(?:js|mjs|cjs)$/,
        exclude: /node_modules/,
        use: {
          loader: 'babel-loader',
          options: {
            presets: [['@babel/preset-env', { targets: 'defaults' }]]
          }
        }
      }
    ]
  },
  plugins: [
    new ESLintPlugin({
      files: 'resources/**/*.js'
    }),
    new MiniCssExtractPlugin({
      filename: 'css/[name].css'
    }),
    new StylelintPlugin({
      files: 'resources/**/*.scss'
    }),
    new WebpackManifestPlugin()
  ],
  optimization: {
    minimizer: [
      '...',
      new CssMinimizerPlugin(),
      new ImageMinimizerPlugin({
        minimizer: {
          implementation: ImageMinimizerPlugin.sharpMinify
        }
      })
    ]
  },
  devtool: argv.mode === 'production' ? false : 'eval-source-map'
})
