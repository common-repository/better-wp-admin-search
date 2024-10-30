const path = require("path");

// css extraction and minification
// const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const CssMinimizerPlugin = require("css-minimizer-webpack-plugin");

// clean out build dir in-between builds
const { CleanWebpackPlugin } = require("clean-webpack-plugin");

const configAdmin = {
  entry: ["./assets/admin/js/src/main.js"],
  output: {
    path: path.resolve(__dirname, "dist"),
    filename: "admin/admin.bundle.js",
  },
  module: {
    rules: [
      // js babelization
      {
        test: /\.(js|jsx)$/,
        exclude: /node_modules/,
        loader: "babel-loader",
      },
      // sass compilation
      {
        test: /\.scss$/,
        use: [
          {
            loader: "style-loader",
          },
          {
            loader: "css-loader",
          },
          {
            loader: "sass-loader",
            options: {
              implementation: require("sass"),
            },
          },
        ],
      },
    ],
  },
  resolve: {
    extensions: [".js"],
  },
  plugins: [
    // clear out build directories on each build
    new CleanWebpackPlugin({
      cleanOnceBeforeBuildPatterns: ["./admin/*"],
    }),
    // // css extraction into dedicated file
    // new MiniCssExtractPlugin({
    //   filename: "./admin/main.min.css",
    // }),
    // new MiniCssExtractPlugin(),
  ],
  optimization: {
    // minification - only performed when mode = production
    minimizer: [
      // js minification - special syntax enabling webpack 5 default terser-webpack-plugin
      `...`,
      // css minification
      new CssMinimizerPlugin(),
    ],
  },
};
module.exports = [configAdmin];
