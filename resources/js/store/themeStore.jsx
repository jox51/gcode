import { create } from "zustand";
import { persist } from "zustand/middleware";

export const useThemeStore = create(
    persist(
        (set) => ({
            darkMode: false,
            toggleDarkMode: () =>
                set((state) => ({ darkMode: !state.darkMode })),
        }),
        {
            name: "theme-preference", // unique name for localStorage key
            getStorage: () => localStorage, // specifies localStorage as the storage option
        }
    )
);
