/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ["templates/**/*.{twig,html}", "./assets/js/**/*.{vue,js}"],
  theme: {
    container: {
      screens: {
        "2xl": "1300px",
      },
    },
    variants: {
      border: ({ after }) => after(["disabled"]),
    },
    extend: {
      colors: {
        bodyBg: "#FBFCFE",
      },
      boxShadow: {
        header: "0px 4px 19px rgba(239, 246, 255, 0.35);",
        cardReview: "0px 0px 45px rgba(239, 246, 255, 1);",
      },
      fontSize: {
        "10xl": "17rem",
      },
      minHeight: {
        400: "25rem",
      },
    },
  },
  plugins: [require("@tailwindcss/forms")],
};
