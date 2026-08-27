const path = require('path');
const fs = require('fs');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const CssMinimizerPlugin = require('css-minimizer-webpack-plugin');
const IgnoreEmitPlugin = require('ignore-emit-webpack-plugin');

// Gera entradas dinâmicas com base nos arquivos .css da pasta Admin/css
function generateEntries(sourceDir) {
    const entries = {};
    if (fs.existsSync(sourceDir)) {
        const files = fs.readdirSync(sourceDir);
        files.forEach(file => {
            if (file.endsWith('.css')) {
                const name = path.basename(file, '.css');
                entries[name] = path.join(sourceDir, file);
            }
        });
    }
    return entries;
}

const adminCssSourceDir = path.resolve(__dirname, 'Admin/css');

module.exports = {
    mode: 'production',
    entry: generateEntries(adminCssSourceDir),
    output: {
        path: path.resolve(__dirname, 'Admin/cssCompiled'),
    },
    module: {
        rules: [
            {
                test: /\.css$/,
                use: [
                    MiniCssExtractPlugin.loader,
                    'css-loader',
                ],
            },
        ],
    },
    plugins: [
        new MiniCssExtractPlugin({
            filename: '[name].COMPILED.css',
            experimentalUseImportModule: false,
        }),
        new IgnoreEmitPlugin(/^.*\.js$/),
    ],
    optimization: {
        minimize: true,
        minimizer: [
            `...`,
            new CssMinimizerPlugin(),
        ],
    },
};
