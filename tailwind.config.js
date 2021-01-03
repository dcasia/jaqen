const defaultTheme = require('tailwindcss/defaultTheme')

module.exports = {
    purge: [
        './**/*.ts',
        './**/*.vue',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: [ 'Roboto', ...defaultTheme.fontFamily.sans ]
            },
            colors: {
                primary: '#623CEA'
            }
        }
    },
    variants: {},
    plugins: []
}
