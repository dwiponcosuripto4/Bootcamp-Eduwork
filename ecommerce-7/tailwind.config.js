import defaultTheme from "tailwindcss/defaultTheme";
import forms from "@tailwindcss/forms";

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
        "./app/**/*.php",
    ],

    safelist: ["bg-blue-500", "bg-green-500", "bg-yellow-500", "bg-red-500"],

    theme: {
        extend: {
            fontFamily: {
                sans: ["Inter", ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms],
};
