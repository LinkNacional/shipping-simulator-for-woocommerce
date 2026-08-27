const path = require('path');
const fs = require('fs');
const { CleanWebpackPlugin } = require('clean-webpack-plugin');

// Gera entradas dinâmicas com base nos arquivos .js da pasta Admin/js
function generateEntries(sourceDir) {
    const entries = {};
    if (fs.existsSync(sourceDir)) {
        const files = fs.readdirSync(sourceDir);
        files.forEach(file => {
            if (file.endsWith('.js')) {
                const name = path.basename(file, '.js');
                entries[name] = path.join(sourceDir, file);
            }
        });
    }
    return entries;
}

const adminJsSourceDir = path.resolve(__dirname, 'Admin/js');

module.exports = {
    mode: 'production',
    entry: generateEntries(adminJsSourceDir),
    output: {
        path: path.resolve(__dirname, 'Admin/jsCompiled'),
        filename: '[name].COMPILED.js',
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
        new CleanWebpackPlugin({
            cleanOnceBeforeBuildPatterns: [
                path.resolve(__dirname, 'Admin/jsCompiled/*'),
            ],
        }),
    ],
};
