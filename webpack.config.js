const Path = require('path');
const { JavascriptWebpackConfig, CssWebpackConfig } = require('@silverstripe/webpack-config');

const PATHS = {
    ROOT: Path.resolve(),
    SRC: Path.resolve('client/src'),
};

module.exports = [
    new JavascriptWebpackConfig('js', PATHS)
        .setEntry({
            bundle: `${PATHS.SRC}/js/bundle.js`,
        })
        .getConfig(),
    new CssWebpackConfig('css', PATHS)
        .setEntry({
            bundle: `${PATHS.SRC}/styles/bundle.scss`,
        })
        .getConfig(),
];
