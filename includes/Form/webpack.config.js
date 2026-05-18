// Require processing plugins.
import MiniCssExtractPlugin from 'mini-css-extract-plugin';
import ESLintPlugin from 'eslint-webpack-plugin';
import CssMinimizerPlugin from 'css-minimizer-webpack-plugin';
import TerserPlugin from 'terser-webpack-plugin';
import RemoveEmptyScriptsPlugin from 'webpack-remove-empty-scripts';

import { sync } from 'glob';
import { basename } from 'path';

export default [
  {
    entry: sync(['./assets/source/css/*.scss']).reduce(
      (files, filePath) => {
        files[basename(filePath, '.scss')] = import.meta.dirname + '/' + filePath;
        return files;
      },
      {}
    ),
    output: {
      // filename: '[name].min.css',
      path: import.meta.dirname + '/assets/dist/css',
      clean: true,
    },
    module: {
      rules: [
        {
          test: /\.s?css$/,
          use: [
            MiniCssExtractPlugin.loader,
            {
              loader: 'css-loader',
              options: { url: false, modules: false },
            },
            'postcss-loader',
            'sass-loader',
          ],
        }
      ],
    },
    plugins: [
      new RemoveEmptyScriptsPlugin(),
      new MiniCssExtractPlugin(
        {
          filename: "[name].min.css",
        }
      ),
    ],
    optimization: {
      minimizer: [
        new CssMinimizerPlugin(
          {
            minimizerOptions: {
              preset: [
                'default',
                {
                  discardComments: { removeAll: true },
                  minifyFontValues: { removeQuotes: false },
                },
              ],
            },
          },
        ),
      ],
      minimize: true,
    },
  },
  {
    entry: sync(['./assets/source/js/*.js']).reduce(
      (files, filePath) => {
        files[basename(filePath, '.js')] = import.meta.dirname + '/' + filePath;
        return files;
      },
      {}
    ),
    output: {
      filename: '[name].min.js',
      path: import.meta.dirname + '/assets/dist/js',
      clean: true,
    },
    module: {
      rules: [
        {
          test: /\.js$/,
          use: {
            loader: 'babel-loader',
          },
        },
      ],
    },
    plugins: [
      new ESLintPlugin(
        {
          emitError: true,
          emitWarning: true,
          failOnError: false,
          failOnWarning: false,
          quiet: false,
        }
      ),
    ],
    optimization: {
      minimize: true,
      minimizer: [
        new TerserPlugin(
          {
            terserOptions: {
              keep_classnames: false,
              keep_fnames: false,
            },
          }
        ),
      ],
    },
  },
];
