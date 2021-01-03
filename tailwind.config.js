const defaultTheme = require('tailwindcss/defaultTheme')

module.exports = {
    purge: [],
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
