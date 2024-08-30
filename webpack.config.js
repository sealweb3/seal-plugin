const path = require('path');
const webpack = require('webpack');
const dotenv = require('dotenv');

// Load environment variables from .env file
dotenv.config();

module.exports = {
  mode: 'development', // Set the mode to 'development' or 'production'
  entry: {
    //attestation: './src/attestation.js',
    //metamask: './src/metamask.js', // Add your second entry file here
    web3manager: './js/web3manager.js', //add file certified by manager
  },
  output: {
    filename: '[name].bundle.js', // This will generate attestation.bundle.js and siweTest.bundle.js
    path: path.resolve(__dirname, 'dist'),
  },
  module: {
    rules: [
      {
        test: /\.js$/,
        exclude: /node_modules/,
        use: {
          loader: 'babel-loader',
          options: {
            presets: ['@babel/preset-env'],
          },
        },
      },
    ],
  },
  plugins: [
    new webpack.DefinePlugin({
      'process.env.PRIVATE_KEY': JSON.stringify(process.env.PRIVATE_KEY),
      'process.env.JWT_TOKEN': JSON.stringify(process.env.JWT_TOKEN),
    }),
  ],
  resolve: {
    fallback: {
      "path": false,
      "os": false,
      "crypto": false
    }
  }
};