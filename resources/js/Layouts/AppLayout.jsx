// layouts/AppLayout.jsx
import React from "react";
import Navbar from "@/Components/Navbar";
import Footer from "@/Components/Footer";
import { ThemeProvider, createTheme } from "@mui/material/styles";
import { useThemeStore } from "@/store/themeStore";

export default function AppLayout({ auth, children }) {
    const { darkMode } = useThemeStore();

    const theme = createTheme({
        palette: {
            mode: darkMode ? "dark" : "light",
            text: {
                primary: darkMode ? "#888585" : "#333",
            },
        },
    });

    return (
        <>
            <ThemeProvider theme={theme}>
                <Navbar auth={auth} />
                <main>{children}</main>
                <Footer />
            </ThemeProvider>
        </>
    );
}
